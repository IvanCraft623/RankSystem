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