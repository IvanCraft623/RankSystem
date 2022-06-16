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

use JsonSerializable;
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
		return new UserData(
			(string) $data["name"],
			(array) ($data["ranks"] === null ? [] : json_decode($data["ranks"], true)),
			(array) ($data["permissions"] === null ? [] : json_decode($data["permissions"], true)),
			(int) ($data["generationTime"] ?? time())
		);
	}
}