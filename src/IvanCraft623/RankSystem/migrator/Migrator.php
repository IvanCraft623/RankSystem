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

use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\rank\RankManager;
use IvanCraft623\RankSystem\session\SessionManager;

use pocketmine\promise\Promise;

abstract class Migrator {

	protected RankSystem $plugin;

	protected RankManager $rankManager;

	protected SessionManager $sessionManager;

	protected string $dataPath;

	public function __construct() {
		$this->plugin = RankSystem::getInstance();
		$this->rankManager = $this->plugin->getRankManager();
		$this->sessionManager = $this->plugin->getSessionManager();
		$server = $this->plugin->getServer();
		$this->dataPath = $server->getDataPath() . ($server->getConfigGroup()->getPropertyBool("plugins.legacy-data-dir", true) ? "plugins" : "plugin_data") . DIRECTORY_SEPARATOR;
	}

	abstract public function getName() : string;

	abstract public function canMigrate() : bool;

	abstract public function hasMigrated() : bool;

	abstract public function setMigrated(bool $value = true) : void;

	abstract public function migrate() : bool;
}