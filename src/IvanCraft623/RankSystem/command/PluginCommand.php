<?php

declare(strict_types=1);

#Plugin by IvanCraft623 (Twitter: @IvanCraft623)

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

namespace IvanCraft623\RankSystem\command;

use pocketmine\command\Command;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

abstract class PluginCommand extends Command implements PluginOwned {

	private Plugin $owningPlugin;

	public function __construct(string $name, $plugin) {
		parent::__construct($name);
		$this->owningPlugin = $plugin;
		$this->usageMessage = "";
	}

	/**
	 * @return Plugin
	 */
	public function getOwningPlugin() : Plugin {
		return $this->owningPlugin;
	}
}