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

use Closure;
use InvalidArgumentException;

use IvanCraft623\RankSystem\RankSystem;

use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\utils\SingletonTrait;

use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql as libasynqlDatabase;
use poggit\libasynql\SqlError;

class libasynql extends Provider {
	use SingletonTrait;

	protected DataConnector $database;

	protected string $name;

	public function load() : void {
		$this->plugin = RankSystem::getInstance();

		$configData = $this->plugin->getConfig()->get("database");
		$this->database = libasynqlDatabase::create($this->plugin, $configData, [
			"sqlite" => "database/sqlite.sql",
			"mysql"  => "database/mysql.sql",
		]);
		$this->name = strtolower($configData["type"] ?? "libasynql");

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
		$dataPromiseResolver = new PromiseResolver();
		$this->database->executeSelect("data.users.get", [
			"name" => $name
		], function (array $rows) use ($dataPromiseResolver) {
			$playerdata = null;
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
	public function setRanks(string $name, array $ranks, ?callable $onSuccess = null, ?callable $onError = null) : void {
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
	 * @phpstan-return Promise<array>
	 */
	public function removeRank(string $name, string $rank) : Promise {
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
	 * @param string[] $permisions
	 */
	public function setPermissions(string $name, array $permisions, ?callable $onSuccess = null, ?callable $onError = null) : void {
		$this->database->executeGeneric("data.users.setPermissions", [
			"name" => $name,
			"permissions" => json_encode($permisions, JSON_THROW_ON_ERROR)
		], $onSuccess, function (SqlError $result) use ($onError) {
			$this->plugin->getLogger()->emergency($result->getQuery() . ' - ' . $result->getErrorMessage());
			if ($onError !== null) {
				$onError();
			}
		});
	}

	/**
	 * @phpstan-return Promise<string[]>
	 */
	public function setPermission(string $name, string $permission) : Promise {
		$resultPromise = new PromiseResolver();
		$this->getUserData($name)->onCompletion(
			function (?UserData $userData) use ($name, $permission, $resultPromise) {
				$permissions = [];
				if ($userData !== null) {
					$permissions = $userData->getPermissions();
				}
				$permissions[] = $permission;
				$this->setPermissions($name, $permissions, function() use ($permissions, $resultPromise) {
					$resultPromise->resolve($permissions);
				}, fn() => $resultPromise->reject());
			},
			fn() => $resultPromise->reject()
		);
		return $resultPromise->getPromise();
	}

	public function removePermission(string $name, string $permission) : Promise {
		$resultPromise = new PromiseResolver();
		$this->getUserData($name)->onCompletion(
			function (?UserData $userData) use ($name, $permission, $resultPromise) {
				$permissions = [];
				if ($userData !== null) {
					$permissions = $userData->getPermissions();
					foreach ($permissions as $key => $perm) {
						if ($perm === $permission) {
							unset($permissions[$key]);
						}
					}
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

	public function delete(string $name, ?callable $onSuccess = null, ?callable $onError = null) : void {
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