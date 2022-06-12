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

use IvanCraft623\RankSystem\event\UserRankSetEvent;
use IvanCraft623\RankSystem\event\UserRankRemoveEvent;
use IvanCraft623\RankSystem\event\UserPermissionSetEvent;
use IvanCraft623\RankSystem\event\UserPermissionRemoveEvent;

use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;

final class Session {

	private RankSystem $plugin;

	private string $name;

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
				if ($userData !== null) {
					# Ranks
					$this->syncRanks($userData->getRanks());

					# Permissions
					$this->syncPermissions($userData->getPermissions());

					$this->updateRanks();

					$this->initialized = true;
					foreach ($this->onInits as $onInit) {
						$onInit();
					}
					$this->onInits = [];
				}
			}, fn() => throw new \Error("Failed to load ".$this->name."' session")
		);
	}

	/**
	 * @param array<string, ?int> $ranks
	 */
	public function syncRanks(array $ranksdata) : void {
		$manager = $this->plugin->getRankManager();
		$ranks = [];
		foreach ($ranksdata as $name => $expTime) {
			$rank = $manager->getByName($name);
			if (is_numeric($expTime)) {
				$this->tempRanks[] = $rank;
				$this->tempRanksDuration[$rank->getName()] = $expTime;
			}
			$ranks[] = $rank;
		}
		$this->ranks = $manager->getHierarchical($ranks);
		$this->updateRanks();
	}

	/**
	 * @param string[] $userPermssions
	 */
	public function syncPermissions(array $userPermissions) : void {
		$this->permissions = array_merge($this->plugin->getGlobalPerms(), $userPermissions);
		foreach ($this->ranks as $rank) {
			$this->permissions = array_merge($this->permissions, $rank->getPermissions());
		}
		$this->updatePermissions();
	}

	public function getName() : string {
		return $this->name;
	}

	public function getNameTagPrefix() : string {
		$prefixes = [];
		$ranks = $this->plugin->getRankManager()->getHierarchical($this->ranks);
		foreach ($ranks as $rank) {
			$prefixes[] = $rank->getNameTagFormat()["prefix"];
		}
		return implode("", $prefixes);
	}

	public function getChatPrefix() : string {
		$prefixes = [];
		$ranks = $this->plugin->getRankManager()->getHierarchical($this->ranks);
		foreach ($ranks as $rank) {
			$prefixes[] = $rank->getChatFormat()["prefix"];
		}
		return implode("", $prefixes);
	}

	public function getNameTagFormat() : string {
		$highestFormat = $this->plugin->getRankManager()->getHierarchical($this->ranks)[0]->getNameTagFormat();
		return $this->getNameTagPrefix().$highestFormat["nameColor"].$this->name;
	}

	public function getChatFormat() : string {
		$highestFormat = $this->plugin->getRankManager()->getHierarchical($this->ranks)[0]->getChatFormat();
		return $this->getChatPrefix().$highestFormat["nameColor"].$this->name.$highestFormat["chatFormat"];
	}

	/**
	 * @return Rank[]
	 */
	public function getRanks() : array {
		return $this->ranks;
	}

	public function getTempRanks() : array {
		return $this->tempRanks;
	}

	public function isTempRank(Rank|string $rank) : bool {
		$rank = ($rank instanceof Rank) ? $rank : $this->plugin->getRankManager()->getByName($rank);
		return in_array($rank, $this->tempRanks, true);
	}

	public function hasRank(Rank|string $rank) : bool {
		$rank = ($rank instanceof Rank) ? $rank : $this->plugin->getRankManager()->getByName($rank);
		return in_array($rank, $this->ranks, true);
	}

	public function getRankExpTime(Rank|string $rank) : ?int {
		$rank = ($rank instanceof Rank) ? $rank : $this->plugin->getRankManager()->getByName($rank);
		if ($this->isTempRank($rank)) {
			return $this->tempRanksDuration[$rank->getName()];
		}
		return null;
	}

	public function setRank(Rank $rank, ?int $expTime = null) : bool {
		# Call Event
		$ev = new UserRankSetEvent(
			$this,
			$rank,
			(is_numeric($expTime) ? (int)$expTime : null)
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
		if ($this->ranks[0] === $default) {
			$this->ranks = [$rank];
		} else {
			$this->ranks[] = $rank;
			if (is_numeric($expTime)) {
				$this->tempRanks[] = $rank;
				$this->tempRanksDuration[$rank->getName()] = (int)$expTime;
			}
		}

		$this->plugin->getProvider()->setRank($this->name, $rank->getName(), $expTime)->onCompletion(
			function (array $ranks) {
				$this->syncRanks($ranks);
				$this->syncPermissions($this->permissions);
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
				$this->syncPermissions($this->permissions);
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

	public function hasPermission(string $perm) : bool {
		return in_array($perm, $this->permissions, true);
	}

	public function setPermission(string $perm) : bool {
		# Call Event
		$ev = new UserPermissionSetEvent(
			$this,
			$perm
		);
		$ev->call();

		if ($ev->isCancelled()) {
			return false;
		}

		$this->plugin->getProvider()->setPermission($this->name, $perm)->onCompletion(
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
		if ($this->ranks === []) {
			$this->ranks = [$this->plugin->getRankManager()->getDefault()];
		}
		$player = $this->plugin->getServer()->getPlayerExact($this->name);
		if ($player !== null) {
			$this->updatePermissions();
			$player->setNameTag($this->getNameTagFormat());
		}
	}

	public function updatePermissions() {
		$player = $this->plugin->getServer()->getPlayerExact($this->name);
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