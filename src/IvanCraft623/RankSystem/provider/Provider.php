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
use pocketmine\utils\SingletonTrait;

abstract class Provider {

	abstract public function load() : void;

	abstract public function getName() : string;

	abstract public function isInDb(string $user) : bool;

	abstract public function getRanks(string $user) : array;

	/**
	 * @param Int|string $expTime
	 */
	abstract public function setRank(string $user, string $rankName, $expTime = "Never") : void;

	abstract public function removeRank(string $user, string $rankName) : void;

	abstract public function getPermissions(string $user) : array;
	
	abstract public function setPermission(string $user, string $permission) : void;

	abstract public function removePermission(string $user, string $permission) : void;
}