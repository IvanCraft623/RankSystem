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

namespace IvanCraft623\RankSystem\migrator;

use pocketmine\utils\Config;

/**
 * LegacyRankSystem Migrator!
 */
class LegacyRankSystem extends Migrator {
	
	public function getName() : string {
		return "LegacyRankSystem";
	}

	public function canMigrate() : bool {
		$dataFolder = $this->plugin->getDataFolder();
		return file_exists($dataFolder . "Ranks.db") || file_exists($dataFolder . "users.yml");
	}

	public function hasMigrated() : bool {
		return !$this->canMigrate();
	}

	public function setMigrated(bool $value = true) : void {
		if ($value) {
			$dataFolder = $this->plugin->getDataFolder();
			if (file_exists($dataFolder . "Ranks.db")) {
				rename($dataFolder . "Ranks.db", $dataFolder . "old-Ranks.db");
			}
			if (file_exists($dataFolder . "users.yml")) {
				rename($dataFolder . "users.yml", $dataFolder . "old-users.yml");
			}
		} else {
			// nope...
		}
	}

	public function migrate() : bool {
		if (!$this->canMigrate()) {
			return false;
		}
		$dataFolder = $this->plugin->getDataFolder();
		if (file_exists($dataFolder . "Ranks.db")) {
			$db = new \SQLite3($dataFolder . "Ranks.db");
			$results = $db->query("SELECT * FROM users");
			while ($row = $results->fetchArray()) {
				$user = $row["user"] ?? null;
				if ($user !== null) {
					$session = $this->sessionManager->get($user);
					$ranks = [];
					$ranksExpTime = [];
					$permissions = explode(", ", ($row["permissions"] ?? ""));
					$stringRanks = $row["ranks"] ?? "";
					if ($stringRanks !== "") {
						$stringRanks = explode(", ", $stringRanks);
						foreach ($stringRanks as $stringRank) {
							$data = explode(";", $stringRank);
							$expTime = $data[1] ?? null;
							if (is_numeric($expTime)) {
								$expTime = (int) $expTime;
							} else {
								$expTime = null;
							}
							$rank = $this->rankManager->getRank($data[0]);
							if ($rank !== null) {
								$ranks[] = $rank;
								$ranksExpTime[$rank->getName()] = $expTime;
							}
						}
					}
					$session->onInitialize(function() use ($session, $ranks, $ranksExpTime, $permissions) {
						foreach ($ranks as $rank) {
							$session->setRank($rank, $ranksExpTime[$rank->getName()] ?? null);
						}
						foreach ($permissions as $permission) {
							if ($permission !== "") {
								$session->setPermission($permission);
							}
						}
					});
				}
			}
			$db->close();
		}
		if (file_exists($dataFolder . "users.yml")) {
			$usersData = new Config($dataFolder . "users.yml", Config::YAML);
			foreach ($usersData->getAll() as $user => $data) {
				$session = $this->sessionManager->get($user);
				$session->onInitialize(function() use ($session, $data) {
					foreach ($data["ranks"] as $name => $expTime) {
						$rank = $this->rankManager->getRank($name);
						if ($rank !== null) {
							$session->setRank($rank, is_numeric($expTime) ? $expTime : null);
						}
						foreach ($data["permissions"] as $permission) {
							$session->setPermission($permission);
						}
					}
				});
			}
		}
		$this->setMigrated();
		return true;
	}
}