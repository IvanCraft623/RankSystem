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

namespace IvanCraft623\RankSystem\migrator;

use pocketmine\utils\Config;
use function explode;
use function file_exists;
use function is_numeric;
use function rename;

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
			if ($results === false) {
				return false;
			}

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
				$session = $this->sessionManager->get((string) $user);
				$session->onInitialize(function() use ($session, $data) {
					foreach ($data["ranks"] as $name => $expTime) {
						$rank = $this->rankManager->getRank($name);
						if ($rank !== null) {
							$session->setRank($rank, is_numeric($expTime) ? ((int) $expTime) : null);
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
