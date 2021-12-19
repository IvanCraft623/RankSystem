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

use IvanCraft623\RankSystem\RankSystem;
use pocketmine\utils\SingletonTrait;

class SQLite3 extends Provider {
	use SingletonTrait;

	public \SQLite3 $db;

	public function load() : void {
		$this->db = new \SQLite3($this->plugin->getInstance()->getDataFolder() . "Ranks.db");
		//Users DB
		$this->db->exec("CREATE TABLE IF NOT EXISTS users (
			user TEXT NOT NULL,
			ranks TEXT,
			permissions TEXT,
			UNIQUE(user)
		);");
	}

	public function getName() : string {
		return "SQLite3";
	}

	public function isInDb(string $user) : bool {
		$query = $this->db->querySingle("SELECT user FROM users WHERE user = '$user'");
		return ($query !== null);
	}

	public function getRanks(string $user) : array {
		$ranks = [];
		if ($this->isInDb($user)) {
			$stringRanks = $this->db->querySingle("SELECT ranks FROM users WHERE user = '$user'");
			if ($stringRanks !== "") {
				$stringRanks = explode(", ", $stringRanks);
				foreach ($stringRanks as $stringRank) {
					$data = explode(";", $stringRank);
					$expTime = $data[1];
					if (is_numeric($expTime)) {
						$expTime = (int)$expTime;
					}
					if ($this->plugin->getRankManager()->exists($data[0])) {
						$ranks[$data[0]] = $expTime;
					}
				}
			}
		}
		return $ranks;
	}

	public function setRank(string $user, string $rankName, $expTime = "Never") : void {
		$ranks = $this->getRanks($user);
		$ranks[$rankName] = $expTime;

		$stringRanks = [];
		foreach ($ranks as $rank => $expTime) {
			$stringRanks[] = $rank.";".$expTime;
		}
		$stringRanks = implode(", ", $stringRanks);

		if ($this->isInDb($user)) {
			$this->db->exec("UPDATE `users` SET `ranks`='$stringRanks' WHERE user='$user';");
		} else {
			$dbInfo = $this->db->prepare("INSERT OR IGNORE INTO users(user,ranks,permissions) SELECT :user, :ranks, :permissions WHERE NOT EXISTS(SELECT * FROM users WHERE user = :user);");
			$dbInfo->bindValue(":user", $user, SQLITE3_TEXT);
			$dbInfo->bindValue(":ranks", $stringRanks, SQLITE3_TEXT);
			$dbInfo->bindValue(":permissions", "", SQLITE3_TEXT);
			$dbInfo->execute();
		}
	}

	public function removeRank(string $user, string $rankName) : void {
		$ranks = $this->getRanks($user);
		unset($ranks[$rankName]);

		$stringRanks = [];
		foreach ($ranks as $rank => $expTime) {
			$stringRanks[] = $rank.";".$expTime;
		}
		$stringRanks = implode(", ", $stringRanks);

		if ($ranks === [] && $this->getPermissions($user) === []) {
			$this->db->query("DELETE FROM users WHERE user = '$user';");
		} else {
			$this->db->exec("UPDATE `users` SET `ranks`='$stringRanks' WHERE user='$user';");
		}
	}

	public function getPermissions(string $user) : array {
		$permissions = [];
		if ($this->isInDb($user)) {
			$stringPerms = $this->db->querySingle("SELECT permissions FROM users WHERE user = '$user'");
			$permissions = explode(", ", $stringPerms);
		}
		return $permissions;
	}

	public function setPermission(string $user, string $permission) : void {
		$permissions = $this->getPermissions($user);
		$permissions[] = $permission;

		$stringPerms = implode(", ", $permissions);
		if ($this->isInDb($user)) {
			$this->db->exec("UPDATE `users` SET `permissions`='$stringPerms' WHERE user='$user';");
		} else {
			$dbInfo = $this->db->prepare("INSERT OR IGNORE INTO users(user,ranks,permissions) SELECT :user, :ranks, :permissions WHERE NOT EXISTS(SELECT * FROM users WHERE user = :user);");
			$dbInfo->bindValue(":user", $user, SQLITE3_TEXT);
			$dbInfo->bindValue(":ranks", "", SQLITE3_TEXT);
			$dbInfo->bindValue(":permissions", $stringPerms, SQLITE3_TEXT);
			$dbInfo->execute();
		}
	}

	public function removePermission(string $user, string $permission) : void {
		$permissions = $this->getPermissions($user);
		foreach ($permissions as $key => $perm) {
			if ($perm = $permission) {
				unset($permissions[$key]);
			}
		}

		$stringPerms = implode(", ", $permissions);
		if ($permissions === [] && $this->getRanks($user) === []) {
			$this->db->query("DELETE FROM users WHERE user = '$user';");
		} else {
			$this->db->exec("UPDATE `users` SET `permissions`='$stringPerms' WHERE user='$user';");
		}
	}
}