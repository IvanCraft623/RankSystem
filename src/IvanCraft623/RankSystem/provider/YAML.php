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

namespace IvanCraft623\RankSystem\provider;

use IvanCraft623\RankSystem\RankSystem as Ranks;
use pocketmine\utils\{SingletonTrait, Config};

class YAML extends Provider {
	use SingletonTrait;

	/** @var \Config */
	public $db;

	public function load() : void {
		$this->db = Ranks::getInstance()->getConfigs("users.yml");
	}

	public function getName() : string {
		return "Yaml";
	}

	public function isInDb(string $user) : bool {
		return isset($this->db->getAll()[$user]);
	}

	public function getRanks(string $user) : array {
		$ranks = [];
		if ($this->isInDb($user)) {
			$all = $this->db->getAll();
			$ranks = $all[$user]["ranks"];
		}
		return $ranks;
	}

	public function setRank(string $user, string $rankName, $expTime = "Never") : void {
		if ($this->isInDb($user)) {
			$all = $this->db->getAll();
			$all[$user]["ranks"][$rankName] = $expTime;
			$this->db->setAll($all);
		} else {
			$data = [
				"ranks" => [$rankName => $expTime],
				"permissions" => []
			];
			$this->db->set($user, $data);
		}
		$this->db->save();
	}

	public function removeRank(string $user, string $rankName) : void {
		$ranks = $this->getRanks($user);
		unset($ranks[$rankName]);

		$all = $this->db->getAll();
		if ($ranks === [] && $this->getPermissions($user) === []) {
			unset($all[$user]);
		} else {
			$all[$user]["ranks"] = $ranks;
		}
		$this->db->setAll($all);
		$this->db->save();
	}

	public function getPermissions(string $user) : array {
		$permissions = [];
		if ($this->isInDb($user)) {
			$all = $this->db->getAll();
			$ranks = $all[$user]["permissions"];
		}
		return $permissions;
	}

	public function setPermission(string $user, string $permission) : void {
		if ($this->isInDb($user)) {
			$all = $this->db->getAll();
			$all[$user]["permissions"][] = $permission;
			$this->db->setAll($all);
		} else {
			$data = [
				"ranks" => [],
				"permissions" => [$permission]
			];
			$this->db->set($user, $data);
		}
		$this->db->save();
	}

	public function removePermission(string $user, string $permission) : void {
		$permissions = $this->getPermissions($user);
		foreach ($permissions as $key => $perm) {
			if ($perm = $permission) {
				unset($permissions[$key]);
			}
		}

		$all = $this->db->getAll();
		if ($permissions === [] && $this->getRanks($user) === []) {
			unset($all[$user]);
		} else {
			$all[$user]["permissions"] = $permissions;
		}
		$this->db->setAll($all);
	}
}