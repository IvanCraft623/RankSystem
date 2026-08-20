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

use JsonSerializable;

use pocketmine\utils\Utils as PMUtils;
use function is_array;
use function json_decode;
use function time;

class UserData implements JsonSerializable {

	/**
	 * @param array<string, ?int> $ranks
	 * @param array<string, ?int> $permissions
	 */
	public function __construct(
		protected string $name,
		protected array $ranks,
		protected array $permissions,
		protected int $generationTime //Time at which the data was obtained
	) {
	}

	public function getName() : string {
		return $this->name;
	}

	public function getGenerationTime() : int {
		return $this->generationTime;
	}

	/**
	 * @return array<string, ?int>
	 */
	public function getRanks() : array {
		return $this->ranks;
	}

	/**
	 * @return array<string, ?int>
	 */
	public function getPermissions() : array {
		return $this->permissions;
	}

	/**
	 * Returns an array of player data properties that can be serialized to json.
	 *
	 * @return mixed[]
	 */
	public function jsonSerialize() : array {
		return [
			"name" => $this->name,
			"ranks" => $this->ranks,
			"permissions" => $this->permissions,
			"generationTime" => $this->generationTime
		];
	}

	/**
	 * Returns a UserData from properties created in an array by {@link UserData#jsonSerialize}
	 * @param mixed[] $data
	 * @phpstan-param array{
	 * 	name: string,
	 * 	ranks: ?string,
	 * 	permissions: ?string,
	 * 	generationTime: ?int
	 * } $data
	 */
	public static function jsonDeserialize(array $data) : UserData {
		$intOrNullValidator = static function(?int $_) : void{};

		/** @var array<string, ?int> $ranks */
		$ranks = isset($data["ranks"]) && $data["ranks"] !== "" ? json_decode($data["ranks"], true) : [];
		if (!is_array($ranks)) {
			throw new \TypeError("Expected array for \"ranks\"");
		}
		PMUtils::validateArrayValueType($ranks, $intOrNullValidator);

		/** @var array<string, ?int> $permissions */
		$permissions = isset($data["permissions"]) && $data["permissions"] !== "" ? json_decode($data["permissions"], true) : [];
		if (!is_array($permissions)) {
			throw new \TypeError("Expected array for \"permissions\"");
		}
		PMUtils::validateArrayValueType($permissions, $intOrNullValidator);

		return new UserData(
			(string) $data["name"],
			$ranks,
			$permissions,
			(int) ($data["generationTime"] ?? time())
		);
	}
}