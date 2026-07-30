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

namespace IvanCraft623\RankSystem\provider;

use Closure;

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
	 * @param array<string, ?int>  $ranks
	 * @param null|Closure(): void $onSuccess
	 * @param null|Closure(): void $onError
	 */
	abstract public function setRanks(string $name, array $ranks, ?Closure $onSuccess = null, ?Closure $onError = null) : void;

	/**
	 * @phpstan-return Promise<array<string, ?int>>
	 */
	abstract public function setRank(string $name, string $rank, ?int $expTime = null) : Promise;

	/**
	 * @phpstan-return Promise<array<string, ?int>>
	 */
	abstract public function removeRank(string $name, string $rank) : Promise;

	/**
	 * @param array<string, ?int>  $permisions
	 * @param null|Closure(): void $onSuccess
	 * @param null|Closure(): void $onError
	 */
	abstract public function setPermissions(string $name, array $permisions, ?Closure $onSuccess = null, ?Closure $onError = null) : void;

	/**
	 * @phpstan-return Promise<array<string, ?int>>
	 */
	abstract public function setPermission(string $name, string $permission, ?int $expTime = null) : Promise;

	/**
	 * @phpstan-return Promise<array<string, ?int>>
	 */
	abstract public function removePermission(string $name, string $permission) : Promise;

	/**
	 * @param null|Closure(): void $onSuccess
	 * @param null|Closure(): void $onError
	 */
	abstract public function delete(string $name, ?Closure $onSuccess = null, ?Closure $onError = null) : void;
}