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
use function count;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function opendir;
use function readdir;
use function unlink;
use const DIRECTORY_SEPARATOR;

/**
 * PurePerms Migrator!
 */
class PurePerms extends Migrator {

	public function getName() : string {
		return "PurePerms";
	}

	public function canMigrate() : bool {
		return is_dir($this->dataPath . "PurePerms");
	}

	public function hasMigrated() : bool {
		return file_exists($this->dataPath . "PurePerms" . DIRECTORY_SEPARATOR . $this->plugin->getName() . "_was_here");
	}

	public function setMigrated(bool $value = true) : void {
		$file = $this->dataPath . "PurePerms" . DIRECTORY_SEPARATOR . $this->plugin->getName() . "_was_here";
		if ($value) {
			file_put_contents($file, "lol");
		} else {
			@unlink($file);
		}
	}

	public function migrate() : bool {
		if (!$this->canMigrate()) {
			return false;
		}
		$pperms = $this->dataPath . "PurePerms";

		$success = false;
		$groupsFile = $pperms . DIRECTORY_SEPARATOR . "groups.yml";

		// Look for groups data...
		foreach ([
			$pperms . DIRECTORY_SEPARATOR . "groups.yml",
			$pperms . DIRECTORY_SEPARATOR . "ranks.yml"
		] as $groupsFile) {
			if (file_exists($groupsFile)) {
				foreach ((new Config($groupsFile, Config::YAML))->getAll() as $groupName => $data) {
					$group = (string) $groupName;
					if (!$this->rankManager->exists($group)) {
						$nameTag = [ //Hacks! >:D
							"prefix" => "§8[" . $group . "] §r",
							"nameColor" => "§f"
						];
						$chat = $nameTag;
						$chat["chatFormat"] = "§f: §7";
						$permissions = $data["permissions"] ?? [];
						$inheritance = $data["inheritance"] ?? [];
						$this->rankManager->create($group, $nameTag, $chat, $permissions, $inheritance);
					}
					if ($data["isDefault"] ?? false) {
						$this->rankManager->setDefault($group);
					}
				}

				$success = true;
			}
		}

		// Look for players data on players directory...
		$playersFolder = $pperms . DIRECTORY_SEPARATOR . "players" . DIRECTORY_SEPARATOR;
		if ($handle = opendir($playersFolder)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry !== '.' && $entry !== '..') {

					// PurePerms always stored data in YAML format, even though it used .json extension.
					$playerData = new Config($playersFolder . $entry, Config::YAML);

					$data = $playerData->getAll();
					if (isset($data["userName"]) && isset($data["group"])) {
						$session = $this->sessionManager->get($data["userName"]);
						$rank = $this->rankManager->getRank($data["group"]);
						$permissions = $data["permissions"] ?? [];

						$session->onInitialize(function() use ($session, $rank, $permissions) {
							if ($rank !== null) {
								$session->setRank($rank);
							}
							if (count($permissions) !== 0) {
								foreach ($permissions as $permission) {
									$session->setPermission($permission);
								}
							}
						});
					}
				}
			}

			$success = true;
		}

		// Look for players data on JSON and YAML files...
		foreach ([
			$pperms . DIRECTORY_SEPARATOR . "players.yml" => Config::YAML,
			$pperms . DIRECTORY_SEPARATOR . "players.json" => Config::JSON
		] as $groupsFile => $format) {
			if (file_exists($groupsFile)) {
				foreach ((new Config($groupsFile, $format))->getAll() as $username => $data) {
					if (isset($data["group"])) {
						$session = $this->sessionManager->get((string) $username);
						$rank = $this->rankManager->getRank($data["group"]);
						$permissions = $data["permissions"] ?? [];

						$session->onInitialize(function() use ($session, $rank, $permissions) {
							if ($rank !== null) {
								$session->setRank($rank);
							}
							if (count($permissions) !== 0) {
								foreach ($permissions as $permission) {
									$session->setPermission($permission);
								}
							}
						});
					}
				}

				$success = true;
			}
		}

		// PurePerms never supported MySQL properly, so we don't care about it.

		$this->setMigrated();
		return $success;
	}
}
