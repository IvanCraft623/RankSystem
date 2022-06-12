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

use pocketmine\promise\Promise;

abstract class Provider {

	protected RankSystem $plugin;

	public function __construct() {
		$this->plugin = RankSystem::getInstance();
	}

	abstract public function load() : void;

	abstract public function unload() : void;

	abstract public function getname() : string;

	/**
	 * @phpstan-return Promise<?UserData>
	 */
	abstract public function getUserData(string $name) : Promise;

	/**
	 * @phpstan-return Promise<bool>
	 */
	abstract public function isInDb(string $name) : Promise;

	/**
	 * @param array<string, ?int> $ranks
	 */
	abstract public function setRanks(string $name, array $ranks, ?callable $onSuccess = null, ?callable $onError = null) : void;

	/**
	 * @phpstan-return Promise<array<string, ?int>>
	 */
	abstract public function setRank(string $name, string $rank, ?int $expTime = null) : Promise;

	abstract public function removeRank(string $name, string $rank) : Promise;
	
	/**
	 * @param array<string, ?int> $permisions
	 */
	abstract public function setPermissions(string $name, array $permisions, ?callable $onSuccess = null, ?callable $onError = null) : void;
	
	/**
	 * @phpstan-return Promise<array<string, ?int>>
	 */
	abstract public function setPermission(string $name, string $permission, ?int $expTime = null) : Promise;

	abstract public function removePermission(string $name, string $permission) : Promise;

	abstract public function delete(string $name, ?callable $onSuccess = null, ?callable $onError = null) : void;
}