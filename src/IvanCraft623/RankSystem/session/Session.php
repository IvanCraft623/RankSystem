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

use IvanCraft623\RankSystem\{RankSystem as Ranks, rank\Rank, event\UserRankSetEvent, event\UserRankRemoveEvent, event\UserPermissionSetEvent, event\UserPermissionRemoveEvent};

final class Session {

	/** @var Ranks */
	private $plugin;

	/** @var String */
	private $name;

	/** @var Rank[] */
	private $ranks = [];

	/** @var Rank[] */
	private $tempRanks = [];

	/** @var Array */
	private $tempRanksDuration = [];

	/** @var String[] */
	private $permissions = [];

	/** @var Array */
	private $attachments = [];
	
	public function __construct(string $name) {
		$this->plugin = Ranks::getInstance();
		$this->name = $name;
		$this->loadRanks();
		$this->loadPermissions(); 
	}

	private function unsetRank(Rank $rank) : void {
		foreach ($this->ranks as $key => $rk) {
			if ($rk == $rank) {
				unset($this->ranks[$key]);
			}
		}
		foreach ($this->tempRanks as $key => $rk) {
			if ($rk == $rank) {
				unset($this->tempRanks[$key]);
				unset($this->tempRanksDuration[$rank->getName()]);
			}
		}
	}

	private function unsetPermssion(string $permission) : bool {
		foreach ($this->permissions as $key => $perm) {
			if ($perm === $permission) {
				unset($this->permissions[$key]);
				return true;
			}
		}
		return false;
	}

	private function loadRanks() : void {
		$ranks = [];
		$manager = $this->plugin->getRankManager();
		foreach ($this->plugin->getProvider()->getRanks($this->name) as $name => $expTime) {
			$rank = $manager->getByName($name);
			if (is_numeric($expTime)) {
				$this->tempRanks[] = $rank;
				$this->tempRanksDuration[$rank->getName()] = $expTime;
			}
			$ranks[] = $rank;
		}
		if ($ranks === []) {
			$ranks = [$this->plugin->getRankManager()->getDefault()];
		}
		$this->ranks = $manager->getHierarchical($ranks);
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
		return implode(" ", $prefixes);
	}

	public function getChatPrefix() : string {
		$prefixes = [];
		$ranks = $this->plugin->getRankManager()->getHierarchical($this->ranks);
		foreach ($ranks as $rank) {
			$prefixes[] = $rank->getChatFormat()["prefix"];
		}
		return implode(" ", $prefixes);
	}

	public function getNameTagFormat() : string {
		$highestFormat = $this->plugin->getRankManager()->getHierarchical($this->ranks)[0]->getNameTagFormat();
		return $this->getNameTagPrefix()." ".$highestFormat["nameColor"].$this->name;
	}

	public function getChatFormat() : string {
		$highestFormat = $this->plugin->getRankManager()->getHierarchical($this->ranks)[0]->getChatFormat();
		return $this->getChatPrefix()." ".$highestFormat["nameColor"].$this->name.$highestFormat["chatFormat"];
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

	/**
	 * @param Rank|String $rank
	 */
	public function isTempRank($rank) : bool {
		$rank = ($rank instanceof Rank) ? $rank : $this->plugin->getRankManager()->getByName($rank);
		return in_array($rank, $this->tempRanks, true);
	}

	/**
	 * @param Rank|String $rank
	 */
	public function hasRank($rank) : bool {
		$rank = ($rank instanceof Rank) ? $rank : $this->plugin->getRankManager()->getByName($rank);
		return in_array($rank, $this->ranks, true);
	}

	/**
	 * @param Rank|String $rank
	 */
	public function getRankExpTime(Rank $rank) : ?int {
		$rank = ($rank instanceof Rank) ? $rank : $this->plugin->getRankManager()->getByName($rank);
		if ($this->isTempRank($rank)) {
			return $this->tempRanksDuration[$rank->getName()];
		}
		return null;
	}

	/**
	 * @param Int|string $expTime
	 */
	public function setRank(Rank $rank, $expTime = "Never") : bool {
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
			$ev->setCancelled();
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
		$this->plugin->getProvider()->setRank($this->name, $rank->getName(), $expTime);
		$this->updateRanks();
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
			$ev->setCancelled();
			return false;
		}
		$this->unsetRank($rank);
		$this->plugin->getProvider()->removeRank($this->name, $rank->getName());
		$this->updateRanks();
		return true;
	}

	public function getPermissions() : array {
		return $this->permissions;
	}

	public function hasPermission(string $perm) : bool {
		return in_array($perm, $this->permission, true);
	}

	public function loadPermissions() : void {
		# Set Global Perms
		$this->permissions = $this->plugin->getGlobalPerms();
		# Set User Perms
		foreach ($this->plugin->getProvider()->getPermissions($this->name) as $perm) {
			$this->permissions[] = $perm;
		}
		# Set All User Rank Perms
		foreach ($this->ranks as $rank) {
			foreach ($rank->getPermissions() as $perm) {
				$this->permissions[] = $perm;
			}
		}
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
		$this->permissions[] = $perm;
		$this->plugin->getProvider()->setPermission($this->name, $perm);
		$this->updatePermissions();
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
		$this->unsetPermssion($perm);
		$this->plugin->getProvider()->removePermission($this->name, $perm);
		$this->updatePermissions();
		return true;
	}

	public function updateRanks() {
		$this->ranks = $this->plugin->getRankManager()->getHierarchical($this->ranks);
		$player = Ranks::getInstance()->getServer()->getPlayer($this->name);
		if ($player !== null) {
			$this->updatePermissions();
			$player->setNameTag($this->getNameTagFormat());
		}
	}

	public function updatePermissions() {
		$player = Ranks::getInstance()->getServer()->getPlayer($this->name);
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