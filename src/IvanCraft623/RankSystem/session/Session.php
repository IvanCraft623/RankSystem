<?php

/*
 *   ____             _     ____
 *  |  _ \ __ _ _ __ | | __/ ___| _   _ ___| |_ ___ _ __ ___
 *  | |_) / _` | '_ \| |/ /\___ \| | | / __| __/ _ \ '_ ` _ \
 *  |  _ < (_| | | | |   <  ___) | |_| \__ \ ||  __/ | | | | |
 *  |_| \_\__,_|_| |_|_|\_\|____/ \__, |___/\__\___|_| |_| |_|
 *                                |___/
 *
 * An amazing rank and permissions manager for PocketMine-MP.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author IvanCraft623
 */

declare(strict_types=1);

namespace IvanCraft623\RankSystem\session;

use IvanCraft623\RankSystem\event\UserPermissionRemoveEvent;
use IvanCraft623\RankSystem\event\UserPermissionSetEvent;
use IvanCraft623\RankSystem\event\UserRankRemoveEvent;
use IvanCraft623\RankSystem\event\UserRankSetEvent;

use IvanCraft623\RankSystem\provider\UserData;
use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\permission\PermissionAttachment;
use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\utils\AssumptionFailedError;
use function array_fill_keys;
use function array_filter;
use function array_key_exists;
use function array_key_first;
use function array_keys;
use function array_map;
use function array_merge;
use function count;
use function in_array;
use function is_string;
use function spl_object_id;
use function str_replace;

final class Session {

	private RankSystem $plugin;

	private string $name;

	private ?Player $player = null;

	private SessionChatFormatter $chatFormatter;

	private bool $initialized = false;

	/** @var array<int, \Closure(): void> */
	private array $onInits = [];

	/** @var RankWrapper[] */
	private array $ranks = [];

	/** @var string[] */
	private array $permissions = [];

	/** @var array<string, ?int> */
	private array $userPermissions = [];

	private ?PermissionAttachment $attachment = null;

	/** @var array<int, \Closure(): Promise<bool>> */
	private array $syncQueue = [];

	private bool $synchronized = false;

	public function __construct(string $name) {
		$this->plugin = RankSystem::getInstance();
		$this->name = $name;
		$this->chatFormatter = new SessionChatFormatter($this);

		$this->loadUserData();
	}

	public function isInitialized() : bool {
		return $this->initialized;
	}

	/**
	 * @param \Closure(): void $onInit
	 */
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
				$this->synchronized = true;
				foreach ($this->onInits as $onInit) {
					$onInit();
				}
				$this->onInits = [];
			}, fn() => throw new \Error("Failed to load " . $this->name . "' session")
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
		$this->permissions = array_merge($this->permissions, array_keys($userPermissions));

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
		$this->attachment = $player->addAttachment($this->plugin);
	}

	public function getNameTagFormat() : string {
		$format = $this->plugin->getConfig()->getNested("nametag.format", "{nametag_ranks_prefix}{nametag_name-color}{name}");
		if (!is_string($format)) {
			throw new AssumptionFailedError("Expected string for \"nametag.format\" config");
		}
		foreach ($this->plugin->getTagManager()->getTags() as $tag) {
			$format = str_replace($tag->getId(), $tag->getValue($this), $format);
		}
		return $format;
	}

	public function getChatFormatter() : SessionChatFormatter {
		return $this->chatFormatter;
	}

	public function getChatFormat() : string {
		$format = $this->plugin->getConfig()->getNested("chat.format", "{chat_ranks_prefix}{chat_name-color}{name}{chat_format}{message}");
		if (!is_string($format)) {
			throw new AssumptionFailedError("Expected string for \"chat.format\" config");
		}
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

	/**
	 * @return Rank[]
	 */
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

	/**
	 * @param \Closure(): Promise<bool> $closure
	 */
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
			/** @var PromiseResolver<bool> $resolver */
			$resolver = new PromiseResolver();
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
			/** @var PromiseResolver<bool> $resolver */
			$resolver = new PromiseResolver();
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

	/**
	 * @return string[]
	 */
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
		return array_key_exists($perm, $this->userPermissions);
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
			/** @var PromiseResolver<bool> $resolver */
			$resolver = new PromiseResolver();
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
			/** @var PromiseResolver<bool> $resolver */
			$resolver = new PromiseResolver();
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

	public function updateRanks() : void {
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

	public function updatePermissions() : void {
		$this->attachment?->setPermissions(array_fill_keys($this->permissions, true));
	}

	public function updateNameTag() : void {
		$player = $this->getPlayer();
		if ($player !== null && $this->plugin->getConfig()->getNested("nametag.enabled", true)) {
			$player->setNameTag($this->getNameTagFormat());
		}
	}
}
