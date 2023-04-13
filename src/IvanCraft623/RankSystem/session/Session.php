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
use IvanCraft623\RankSystem\utils\Utils;

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

	/** @var RankWrapper[] */
	private array $ranks = [];

	/** @var String[] */
	private array $permissions = [];

	/** @var array<string, ?int> */
	private array $userPermissions = [];

	private array $attachments = [];

	/** @var \Closure[] */
	private array $syncQueue = [];

	private bool $synchronized = false;
	
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
	/**
	 * @internal
	 */
	public function loadUserData() : void {
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
				$this->synchronized = true;
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
				$this->ranks[spl_object_id($rank)] = new RankWrapper($rank, $expTime);
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
		return $this->player;
	}

	/**
	 * Called when the player joins the server
	 *
	 * @internal
	 */
	public function setPlayer(Player $player) : void {
		$this->player = $player;
	}

	public function getNameTagFormat() : string {
		$format = $this->plugin->getConfig()->getNested("nametag.format", "{nametag_ranks_prefix}{nametag_name-color}{name}");
		foreach ($this->plugin->getTagManager()->getTags() as $tag) {
			$format = str_replace($tag->getId(), $tag->getValue($this), $format);
		}
		return $format;
	}

	public function getChatFormat() : string {
		$format = $this->plugin->getConfig()->getNested("chat.format", "{chat_ranks_prefix}{chat_name-color}{name}{chat_format}{message}");
		foreach ($this->plugin->getTagManager()->getTags() as $tag) {
			$format = str_replace($tag->getId(), $tag->getValue($this), $format);
		}
		return $format;
	}

	/**
	 * They will always be ordered hierarchically
	 *
	 * @return Rank[]
	 */
	public function getRanks() : array {
		$ranks = array_map(function(RankWrapper $wrapper) {
			return $wrapper->getRank();
		}, $this->ranks);
		if (count($ranks) !== 0) {
			return $ranks;
		}
		return [$this->plugin->getRankManager()->getDefault()];
	}

	public function getHighestRank() : Rank {
		$ranks = $this->getRanks();
		return $ranks[array_key_first($ranks)];
	}

	public function getTempRanks() : array {
		return array_map(function(RankWrapper $wrapper) {
			return $wrapper->getRank();
		}, array_filter($this->ranks, function(RankWrapper $wrapper) {
			return $wrapper->isTemporary();
		}));
	}

	public function isTempRank(Rank|string $rank) : bool {
		$rank = ($rank instanceof Rank) ? $rank : $this->plugin->getRankManager()->getRank($rank);
		if ($rank !== null && isset($this->ranks[spl_object_id($rank)])) {
			return $this->ranks[spl_object_id($rank)]->isTemporary();
		}
		return false;
	}

	public function hasRank(Rank|string $rank) : bool {
		$rank = ($rank instanceof Rank) ? $rank : $this->plugin->getRankManager()->getRank($rank);
		return $rank !== null && isset($this->ranks[spl_object_id($rank)]);
	}

	public function getRankExpTime(Rank|string $rank) : ?int {
		$rank = ($rank instanceof Rank) ? $rank : $this->plugin->getRankManager()->getRank($rank);
		if ($rank !== null && isset($this->ranks[spl_object_id($rank)])) {
			return $this->ranks[spl_object_id($rank)]->getExpTime();
		}
		return null;
	}

	private function addToSyncQueue(\Closure $closure) : void {
		$this->syncQueue[] = $closure;
		if ($this->synchronized) {
			$this->synchronized = false;
			$this->loadSyncTask();
		}
	}

	private function loadSyncTask() : void {
		if (!$this->synchronized) {
			if (count($this->syncQueue) === 0) {
				$this->synchronized = true;
				return;
			}
			$key = array_key_first($this->syncQueue);
			$this->syncQueue[$key]()->onCompletion(function () use ($key) {
				unset($this->syncQueue[$key]);
				$this->loadSyncTask();
			},
			function () {
				// Do something...
			});
		}
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

		$this->addToSyncQueue(function () use ($rank, $expTime) : Promise {
			$resolver = new PromiseResolver;
			$this->plugin->getProvider()->setRank($this->name, $rank->getName(), $expTime)->onCompletion(
				function (array $ranks) use ($resolver) {
					$this->syncRanks($ranks);
					$this->syncPermissions($this->userPermissions);
					$resolver->resolve(true);
				},
				fn() => $resolver->resolve(false)
			);
			return $resolver->getPromise();
		});

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

		$this->addToSyncQueue(function () use ($rank) : Promise {
			$resolver = new PromiseResolver;
				$this->plugin->getProvider()->removeRank($this->name, $rank->getName())->onCompletion(
				function (array $ranks) use ($resolver) {
					$this->syncRanks($ranks);
					$this->syncPermissions($this->userPermissions);
					$resolver->resolve(true);
				},
				fn() => $resolver->resolve(false)
			);
			return $resolver->getPromise();
		});
		return true;
	}

	public function getPermissions() : array {
		return $this->permissions;
	}

	/**
	 * @return string[]
	 */
	public function getUserPermissions() : array {
		return array_keys($this->userPermissions);
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

	public function hasUserPermission(string $perm) : bool {
		return isset($this->userPermissions[$perm]);
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

		$this->addToSyncQueue(function () use ($perm, $expTime) : Promise {
			$resolver = new PromiseResolver;
				$this->plugin->getProvider()->setPermission($this->name, $perm, $expTime)->onCompletion(
				function (array $permissions) use ($resolver) {
					$this->syncPermissions($permissions);
					$resolver->resolve(true);
				},
				fn() => $resolver->resolve(false)
			);
			return $resolver->getPromise();
		});
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

		$this->addToSyncQueue(function () use ($perm) : Promise {
			$resolver = new PromiseResolver;
				$this->plugin->getProvider()->removePermission($this->name, $perm)->onCompletion(
				function (array $permissions) use ($resolver) {
					$this->syncPermissions($permissions);
					$resolver->resolve(true);
				},
				fn() => $resolver->resolve(false)
			);
			return $resolver->getPromise();
		});
		return true;
	}

	public function updateRanks() {
		$this->ranks = array_map(function(Rank $rank) {
			return new RankWrapper($rank, $this->getRankExpTime($rank));
		}, $this->plugin->getRankManager()->getHierarchical($this->getRanks()));

		$player = $this->getPlayer();
		if ($player !== null) {
			$this->updatePermissions();
			$this->updateNameTag();
			Utils::updateScoreTags($this);
		}
	}

	public function updatePermissions() {
		$player = $this->getPlayer();
		if ($player !== null) {
			foreach ($this->attachments as $attachment) {
				$player->removeAttachment($attachment);
			}
			$this->attachments = [];
			foreach ($this->permissions as $permission) {
				$this->attachments[] = $player->addAttachment($this->plugin, $permission, true);
			}
		}
	}

	public function updateNameTag() : void {
		$player = $this->getPlayer();
		if ($player !== null && $this->plugin->getConfig()->getNested("nametag.enabled", true)) {
			$player->setNameTag($this->getNameTagFormat());
		}
	}
}
