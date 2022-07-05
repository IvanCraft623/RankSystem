<?php

#Plugin By:

/*
	8888888                            .d8888b.                   .d888 888     .d8888b.   .d8888b.   .d8888b.  
	  888                             d88P  Y88b                 d88P"  888    d88P  Y88b d88P  Y88b d88P  Y88b 
	  888                             888    888                 888    888    888               888      .d88P 
	  888  888  888  8888b.  88888b.  888        888d888 8888b.  888888 888888 888d888b.       .d88P     8888"  
	  888  888  888     "88b 888 "88b 888        888P"      "88b 888    888    888P "Y88b  .od888P"       "Y8b. 
	  888  Y88  88P .d888888 888  888 888    888 888    .d888888 888    888    888    888 d88P"      888    888 
	  888   Y8bd8P  888  888 888  888 Y88b  d88P 888    888  888 888    Y88b.  Y88b  d88P 888"       Y88b  d88P 
	8888888  Y88P   "Y888888 888  888  "Y8888P"  888    "Y888888 888     "Y888  "Y8888P"  888888888   "Y8888P"  
*/

declare(strict_types=1);

namespace IvanCraft623\RankSystem\session;

use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\provider\UserData;
use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\Utils;

use IvanCraft623\RankSystem\event\UserRankSetEvent;
use IvanCraft623\RankSystem\event\UserRankRemoveEvent;
use IvanCraft623\RankSystem\event\UserPermissionSetEvent;
use IvanCraft623\RankSystem\event\UserPermissionRemoveEvent;

use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;

final class Session {

	private RankSystem $plugin;

	private string $name;

	private ?Player $player = null;

	private bool $initialized = false;

	/** @var \Closure[] */
	private array $onInits = [];

	/** @var Rank[] */
	private array $ranks = [];

	/** @var Rank[] */
	private array $tempRanks = [];

	private array $tempRanksDuration = [];

	/** @var String[] */
	private array $permissions = [];

	/** @var array<string, ?int> */
	private array $userPermissions = [];

	private array $attachments = [];
	
	public function __construct(string $name) {
		$this->plugin = RankSystem::getInstance();
		$this->name = $name;
		$this->loadUserData();
	}

	public function isInitialized() : bool {
		return $this->initialized;
	}

	public function onInitialize(\Closure $onInit) : void {
		if ($this->initialized) {
			$onInit();
		} else {
			$this->onInits[spl_object_id($onInit)] = $onInit;
		}
	}

	private function loadUserData() : void {
		$this->plugin->getProvider()->getUserData($this->name)->onCompletion(
			function (?UserData $userData) {
				$permissions = [];
				if ($userData !== null) {
					# Ranks
					$this->syncRanks($userData->getRanks());

					# Permissions
					$permissions = $userData->getPermissions();

					$this->updateRanks();
				}
				$this->syncPermissions($permissions);

				$this->initialized = true;
				foreach ($this->onInits as $onInit) {
					$onInit();
				}
				$this->onInits = [];
			}, fn() => throw new \Error("Failed to load ".$this->name."' session")
		);
	}

	/**
	 * Only get called when ranks were loaded or updated
	 * on database, don't call it directly.
	 *
	 * @param array<string, ?int> $ranksdata
	 *
	 * @internal
	 */
	public function syncRanks(array $ranksdata) : void {
		$this->ranks = [];
		$manager = $this->plugin->getRankManager();
		foreach ($ranksdata as $name => $expTime) {
			$rank = $manager->getRank($name);
			if ($rank !== null) {
				$id = spl_object_id($rank);
				if (is_numeric($expTime)) {
					$this->tempRanks[$id] = $rank;
					$this->tempRanksDuration[$id] = $expTime;
				}
				$this->ranks[$id] = $rank;
			}
		}
		$this->updateRanks();
	}

	/**
	 * Only get called when permissions were loaded or updated
	 * on database, don't call it directly.
	 *
	 * @param array<string, ?int> $userPermissions
	 *
	 * @internal
	 */
	public function syncPermissions(array $userPermissions) : void {
		$this->permissions = [];
		$this->userPermissions = $userPermissions;
		foreach ($this->getRanks() as $rank) {
			$this->permissions = array_merge($this->permissions, $rank->getPermissions());
		}
		foreach ($userPermissions as $perm => $expTime) {
			$this->permissions[] = $perm;
		}
		$this->updatePermissions();
	}

	public function getName() : string {
		return $this->name;
	}

	public function getPlayer() : ?Player {
		return $this->player ?? ($this->player = $this->plugin->getServer()->getPlayerExact($this->name));
	}

	public function getNameTagPrefix() : string {
		$prefix = "";
		foreach ($this->plugin->getRankManager()->getHierarchical($this->getRanks()) as $rank) {
			$prefix .= $rank->getNameTagFormat()["prefix"];
		}
		return $prefix;
	}

	public function getChatPrefix() : string {
		$prefix = "";
		foreach ($this->plugin->getRankManager()->getHierarchical($this->getRanks()) as $rank) {
			$prefix .= $rank->getChatFormat()["prefix"];
		}
		return $prefix;
	}

	public function getNameTagFormat() : string {
		$highestFormat = $this->getHighestRank()->getNameTagFormat();
		return $this->getNameTagPrefix().$highestFormat["nameColor"].$this->name;
	}

	public function getChatFormat() : string {
		$highestFormat = $this->getHighestRank()->getChatFormat();
		return $this->getChatPrefix().$highestFormat["nameColor"].$this->name.$highestFormat["chatFormat"];
	}

	/**
	 * @return Rank[]
	 */
	public function getRanks() : array {
		$default = $this->plugin->getRankManager()->getDefault();
		$ranks = (count($this->ranks) === 0 ? [spl_object_id($default) => $default] : $this->ranks);
		return $ranks;
	}

	public function getHighestRank() : Rank {
		$ranks = $this->plugin->getRankManager()->getHierarchical($this->getRanks());
		return $ranks[array_key_first($ranks)];
	}

	public function getTempRanks() : array {
		return $this->tempRanks;
	}

	public function isTempRank(Rank|string $rank) : bool {
		$rank = ($rank instanceof Rank) ? $rank : $this->plugin->getRankManager()->getRank($rank);
		return $rank !== null && isset($this->tempRanks[spl_object_id($rank)]);
	}

	public function hasRank(Rank|string $rank) : bool {
		$rank = ($rank instanceof Rank) ? $rank : $this->plugin->getRankManager()->getRank($rank);
		return $rank !== null && isset($this->getRanks()[spl_object_id($rank)]);
	}

	public function getRankExpTime(Rank|string $rank) : ?int {
		$rank = ($rank instanceof Rank) ? $rank : $this->plugin->getRankManager()->getRank($rank);
		if ($rank !== null) {
			return $this->tempRanksDuration[spl_object_id($rank)] ?? null;
		}
		return null;
	}

	public function setRank(Rank $rank, ?int $expTime = null) : bool {
		# Call Event
		$ev = new UserRankSetEvent(
			$this,
			$rank,
			$expTime
		);
		$ev->call();

		if ($ev->isCancelled()) {
			return false;
		}

		$default = $this->plugin->getRankManager()->getDefault();
		if ($rank === $default || $this->hasRank($rank)) {
			$ev->cancel();
			return false;
		}

		$this->plugin->getProvider()->setRank($this->name, $rank->getName(), $expTime)->onCompletion(
			function (array $ranks) {
				$this->syncRanks($ranks);
				$this->syncPermissions($this->userPermissions);
			},
			function () {
				// Do something...
			}
		);
		return true;
	}

	public function removeRank(Rank $rank) : bool {
		# Call Event
		$ev = new UserRankRemoveEvent(
			$this,
			$rank
		);
		$ev->call();

		if ($ev->isCancelled()) {
			return false;
		}

		$default = $this->plugin->getRankManager()->getDefault();
		if ($rank === $default || !$this->hasRank($rank)) {
			$ev->cancel();
			return false;
		}

		$this->plugin->getProvider()->removeRank($this->name, $rank->getName())->onCompletion(
			function (array $ranks) {
				$this->syncRanks($ranks);
				$this->syncPermissions($this->userPermissions);
			},
			function () {
				// Do something...
			}
		);
		return true;
	}

	public function getPermissions() : array {
		return $this->permissions;
	}

	/**
	 * @return array<string, ?int>
	 */
	public function getUserPermissions() : array {
		return $this->userPermissions;
	}

	public function isTempPermission(string $permission) : bool {
		return $this->getPermissionExpTime($permission) !== null;
	}

	public function getPermissionExpTime(string $permission) : ?int {
		return $this->userPermissions[$permission] ?? null;
	}

	public function hasPermission(string $perm) : bool {
		return in_array($perm, $this->permissions, true);
	}

	public function setPermission(string $perm, ?int $expTime = null) : bool {
		# Call Event
		$ev = new UserPermissionSetEvent(
			$this,
			$perm,
			$expTime
		);
		$ev->call();

		if ($ev->isCancelled()) {
			return false;
		}

		$this->plugin->getProvider()->setPermission($this->name, $perm, $expTime)->onCompletion(
			function (array $permissions) {
				$this->syncPermissions($permissions);
			},
			function () {
				// Do something...
			}
		);
		return true;
	}

	public function removePermission(string $perm) : bool {
		# Call Event
		$ev = new UserPermissionRemoveEvent(
			$this,
			$perm
		);
		$ev->call();

		if ($ev->isCancelled()) {
			return false;
		}

		$this->plugin->getProvider()->removePermission($this->name, $perm)->onCompletion(
			function (array $permissions) {
				$this->syncPermissions($permissions);
			},
			function () {
				// Do something...
			}
		);
		return true;
	}

	public function updateRanks() {
		$this->ranks = $this->plugin->getRankManager()->getHierarchical($this->ranks);
		$player = $this->getPlayer();
		if ($player !== null) {
			$this->updatePermissions();
			$player->setNameTag($this->getNameTagFormat());
			Utils::updateScoreTags($this);
		}
	}

	public function updatePermissions() {
		$player = $this->getPlayer();
		if ($player !== null) {
			foreach ($this->attachments as $attachment) {
				$player->removeAttachment($attachment);
			}
			$attachment = [];
			foreach ($this->permissions as $permission) {
				$this->attachments[] = $player->addAttachment($this->plugin, $permission, true);
			}
		}
	}
}