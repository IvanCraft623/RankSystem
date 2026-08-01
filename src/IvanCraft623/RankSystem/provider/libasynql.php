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
use pocketmine\promise\PromiseResolver;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;

use IvanCraft623\RankSystem\libs\_723798d82b3d20a4\poggit\libasynql\DataConnector;
use IvanCraft623\RankSystem\libs\_723798d82b3d20a4\poggit\libasynql\libasynql as libasynqlDatabase;
use IvanCraft623\RankSystem\libs\_723798d82b3d20a4\poggit\libasynql\SqlError;
use function count;
use function is_array;
use function is_string;
use function json_encode;
use function strtolower;
use const JSON_THROW_ON_ERROR;

class libasynql extends Provider {
	use SingletonTrait;

	protected DataConnector $database;

	protected string $name;

	public function load() : void {
		$this->plugin = RankSystem::getInstance();

		$configData = $this->plugin->getConfig()->get("database");
		if (!is_array($configData)) {
			throw new AssumptionFailedError("Expected array for \"database\" config");
		}
		$this->database = libasynqlDatabase::create($this->plugin, $configData, [
			"sqlite" => "database/sqlite.sql",
			"mysql" => "database/mysql.sql",
		]);
		$dbType = $configData["type"] ?? "libasynql";
		if (!is_string($dbType)) {
			throw new AssumptionFailedError("Expected string for \"database.type\" config");
		}
		$this->name = strtolower($dbType);

		$this->database->executeGeneric('table.users');
	}

	public function unload() : void {
		if (isset($this->database)) {
			$this->database->close();
		}
	}

	public function getName() : string {
		return $this->name;
	}

	/**
	 * @phpstan-return Promise<?UserData>
	 */
	public function getUserData(string $name) : Promise {
		/** @phpstan-var PromiseResolver<?UserData> $dataPromiseResolver */
		$dataPromiseResolver = new PromiseResolver();
		$this->database->executeSelect("data.users.get", [
			"name" => $name
		], function (array $rows) use ($dataPromiseResolver) {
			if (isset($rows[0])) {
				$dataPromiseResolver->resolve(UserData::jsonDeserialize($rows[0]));
			} else {
				$dataPromiseResolver->resolve(null);
			}
		}, function (SqlError $result) use ($dataPromiseResolver) {
			$this->plugin->getLogger()->emergency($result->getQuery() . ' - ' . $result->getErrorMessage());
			$dataPromiseResolver->reject();
		});
		return $dataPromiseResolver->getPromise();
	}

	/**
	 * @phpstan-return Promise<bool>
	 */
	public function isInDb(string $name) : Promise {
		/** @phpstan-var PromiseResolver<bool> $promiseResolver */
		$promiseResolver = new PromiseResolver();
		$this->getUserData($name)->onCompletion(
			function (?UserData $userData) use ($promiseResolver) {
				$promiseResolver->resolve($userData !== null);
			},
			fn() => $promiseResolver->reject()
		);
		return $promiseResolver->getPromise();
	}

	/**
	 * @param array<string, ?int> $ranks
	 */
	public function setRanks(string $name, array $ranks, ?Closure $onSuccess = null, ?Closure $onError = null) : void {
		$this->database->executeGeneric("data.users.setRanks", [
			"name" => $name,
			"ranks" => json_encode($ranks, JSON_THROW_ON_ERROR)
		], $onSuccess, function (SqlError $result) use ($onError) {
			$this->plugin->getLogger()->emergency($result->getQuery() . ' - ' . $result->getErrorMessage());
			if ($onError !== null) {
				$onError();
			}
		});
	}

	/**
	 * @phpstan-return Promise<array<string, ?int>>
	 */
	public function setRank(string $name, string $rank, ?int $expTime = null) : Promise {
		/** @phpstan-var PromiseResolver<array<string, ?int>> $resultPromise */
		$resultPromise = new PromiseResolver();
		$this->getUserData($name)->onCompletion(
			function (?UserData $userData) use ($name, $rank, $expTime, $resultPromise) {
				$ranks = [];
				if ($userData !== null) {
					$ranks = $userData->getRanks();
				}
				$ranks[$rank] = $expTime;
				$this->setRanks($name, $ranks, function() use ($ranks, $resultPromise) {
					$resultPromise->resolve($ranks);
				}, fn() => $resultPromise->reject());
			},
			fn() => $resultPromise->reject()
		);
		return $resultPromise->getPromise();
	}

	/**
	 * @phpstan-return Promise<array<string, ?int>>
	 */
	public function removeRank(string $name, string $rank) : Promise {
		/** @phpstan-var PromiseResolver<array<string, ?int>> $resultPromise */
		$resultPromise = new PromiseResolver();
		$this->getUserData($name)->onCompletion(
			function (?UserData $userData) use ($name, $rank, $resultPromise) {
				$ranks = [];
				if ($userData !== null) {
					$ranks = $userData->getRanks();
					unset($ranks[$rank]);
					if (count($ranks) === 0 && count($userData->getPermissions()) === 0) {
						$this->delete($name, function() use ($ranks, $resultPromise) {
							$resultPromise->resolve($ranks);
						}, fn() => $resultPromise->reject());
					} else {
						$this->setRanks($name, $ranks, function() use ($ranks, $resultPromise) {
							$resultPromise->resolve($ranks);
						}, fn() => $resultPromise->reject());
					}
				} else {
					$resultPromise->resolve($ranks);
				}
			},
			fn() => $resultPromise->reject()
		);
		return $resultPromise->getPromise();
	}

	/**
	 * @param array<string, ?int> $permissions
	 */
	public function setPermissions(string $name, array $permissions, ?Closure $onSuccess = null, ?Closure $onError = null) : void {
		$this->database->executeGeneric("data.users.setPermissions", [
			"name" => $name,
			"permissions" => json_encode($permissions, JSON_THROW_ON_ERROR)
		], $onSuccess, function (SqlError $result) use ($onError) {
			$this->plugin->getLogger()->emergency($result->getQuery() . ' - ' . $result->getErrorMessage());
			if ($onError !== null) {
				$onError();
			}
		});
	}

	/**
	 * @phpstan-return Promise<array<string, ?int>>
	 */
	public function setPermission(string $name, string $permission, ?int $expTime = null) : Promise {
		/** @phpstan-var PromiseResolver<array<string, ?int>> $resultPromise */
		$resultPromise = new PromiseResolver();
		$this->getUserData($name)->onCompletion(
			function (?UserData $userData) use ($name, $permission, $expTime, $resultPromise) {
				$permissions = [];
				if ($userData !== null) {
					$permissions = $userData->getPermissions();
				}
				$permissions[$permission] = $expTime;
				$this->setPermissions($name, $permissions, function() use ($permissions, $resultPromise) {
					$resultPromise->resolve($permissions);
				}, fn() => $resultPromise->reject());
			},
			fn() => $resultPromise->reject()
		);
		return $resultPromise->getPromise();
	}

	/**
	 * @phpstan-return Promise<array<string, ?int>>
	 */
	public function removePermission(string $name, string $permission) : Promise {
		/** @phpstan-var PromiseResolver<array<string, ?int>> $resultPromise */
		$resultPromise = new PromiseResolver();
		$this->getUserData($name)->onCompletion(
			function (?UserData $userData) use ($name, $permission, $resultPromise) {
				$permissions = [];
				if ($userData !== null) {
					$permissions = $userData->getPermissions();
					unset($permissions[$permission]);
					if (count($permissions) === 0 && count($userData->getRanks()) === 0) {
						$this->delete($name, function() use ($permissions, $resultPromise) {
							$resultPromise->resolve($permissions);
						}, fn() => $resultPromise->reject());
					} else {
						$this->setPermissions($name, $permissions, function() use ($permissions, $resultPromise) {
							$resultPromise->resolve($permissions);
						}, fn() => $resultPromise->reject());
					}
				} else {
					$resultPromise->resolve($permissions);
				}
			},
			fn() => $resultPromise->reject()
		);
		return $resultPromise->getPromise();
	}

	public function delete(string $name, ?Closure $onSuccess = null, ?Closure $onError = null) : void {
		$this->database->executeGeneric('data.users.delete', [
			"name" => $name
		], $onSuccess, function (SqlError $result) use ($onError) {
			$this->plugin->getLogger()->emergency($result->getQuery() . ' - ' . $result->getErrorMessage());
			if ($onError !== null) {
				$onError();
			}
		});
	}
}