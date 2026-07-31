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

namespace IvanCraft623\RankSystem\rank;

use InvalidArgumentException;

use IvanCraft623\RankSystem\RankSystem;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Config;

use pocketmine\utils\SingletonTrait;
use RuntimeException;
use function in_array;
use function is_array;
use function is_string;
use function spl_object_id;
use function strtolower;

/**
 * @phpstan-import-type NameTagFormat from Rank
 * @phpstan-import-type ChatFormat from Rank
 *
 * @phpstan-type RankData array{
 * 	nametag: NameTagFormat,
 * 	chat: ChatFormat,
 * 	permissions: string[],
 * 	inheritance?: string[]
 * }
 */
final class RankManager {
	use SingletonTrait;

	private RankSystem $plugin;

	private Config $data;

	/** @var array<string, Rank> */
	private array $ranks;

	private Rank $defaultRank;

	/** @var array<string, Rank> */
	private array $hierarchy;

	public function __construct() {
		$this->plugin = RankSystem::getInstance();
	}

	public function load() : void {
		$this->data = $this->plugin->getConfigs("ranks.yml");
		/** @var array<string, RankData> $ranksData */
		$ranksData = $this->data->getAll();
		foreach ($ranksData as $name => $data) {
			$name = (string) $name;
			$this->ranks[strtolower($name)] = new Rank($name, $data["nametag"], $data["chat"], $data["permissions"]);
		}

		# Inheritance
		foreach ($this->ranks as $rank) {
			if (isset($ranksData[$rank->getName()]["inheritance"])) {
				foreach ($ranksData[$rank->getName()]["inheritance"] as $name) {
					$rank_that_inherits_permissions_to_another_rank = $this->getRank($name);
					if ($rank_that_inherits_permissions_to_another_rank !== null) {
						$rank->addInheritance($rank_that_inherits_permissions_to_another_rank);
					}
				}
			}
		}
	}

	public function reload() : void {
		$this->ranks = [];
		unset($this->hierarchy);
		unset($this->defaultRank);
		$this->load();
		$this->plugin->getSessionManager()->reload();
	}

	/**
	 * @return array<string, Rank>
	 */
	public function getAll() : array {
		return $this->ranks;
	}

	public function getRank(string $name) : ?Rank {
		return $this->ranks[strtolower($name)] ?? null;
	}

	public function getDefault() : Rank {
		if (!isset($this->defaultRank)) {
			$name = $this->plugin->getConfig()->get("Default_Rank");
			if ($name === false) {
				throw new RuntimeException("The default rank is not specified!");
			}
			if (!is_string($name)) {
				throw new AssumptionFailedError("Expected string for \"Default_Rank\" config");
			}

			$rank = $this->getRank($name);
			if ($rank === null) {
				throw new RuntimeException("The rank: " . $name . " specified as default does not exist!");
			}
			$this->defaultRank = $rank;
		}
		return $this->defaultRank;
	}

	/**
	 * This change is not reflected until ranks are reloaded
	 *
	 * @see reload()
	 */
	public function setDefault(string $rank) : void {
		if (!$this->exists($rank)) {
			throw new InvalidArgumentException("Rank " . $rank . " not found");
		}
		$this->plugin->getConfig()->set("Default_Rank", $rank);
		$this->plugin->getConfig()->save();
	}

	public function exists(string $name) : bool {
		return $this->getRank($name) !== null;
	}

	/**
	 * @return Rank[]
	 */
	public function getHierarchy() : array {
		if (!isset($this->hierarchy)) {
			$this->hierarchy = [];
			$hierarchyConfig = $this->plugin->getConfig()->get("Hierarchy", []);
			if (!is_array($hierarchyConfig)) {
				throw new AssumptionFailedError("Expected array for \"Hierarchy\" config");
			}
			foreach ($hierarchyConfig as $name) {
				if (!is_string($name)) {
					throw new AssumptionFailedError("Expected string for hierarchy rank name");
				}
				$rank = $this->getRank($name);
				if ($rank !== null) {
					$this->hierarchy[$name] = $rank;
				}
			}
			foreach ($this->ranks as $rank) {
				if (!isset($this->hierarchy[$rank->getName()])) {
					$this->hierarchy[$rank->getName()] = $rank;
				}
			}
		}
		return $this->hierarchy;
	}

	/**
	 * @param Rank[] $ranks
	 * @return Rank[]
	 */
	public function getHierarchical(array $ranks) : array {
		$hierarchicalRanks = [];
		foreach ($this->getHierarchy() as $rank) {
			if (in_array($rank, $ranks, true)) {
				$hierarchicalRanks[spl_object_id($rank)] = $rank;
			}
		}
		return $hierarchicalRanks;
	}

	/* Example of how provide the variables:
	 *
	 * $nametag = [
	 *		"prefix" => "§2[§aCat§2] ",
	 *		"nameColor" => "§6"
	 * ];
	 *
	 * $chat = [
	 *		"prefix" => "§2[§aCat§2] ",
	 *		"nameColor" => "§6",
	 *		"chatFormat" => "§5: §b"
	 * ];
	 *
	 * $permissions = ["example.perm", "example.perm2"]:
	 *
	 * $inheritance = ["Guest"];
	 */
	/**
	 * @param NameTagFormat $nametag
	 * @param ChatFormat    $chat
	 * @param string[]      $permissions
	 * @param string[]      $inheritance
	 */
	public function create(string $name, array $nametag, array $chat, array $permissions = [], array $inheritance = []) : void {
		if (!$this->exists($name)) {
			$this->saveRankData($name, $nametag, $chat, $permissions, $inheritance);
		}
	}

	public function delete(Rank|string $rank) : void {
		$rank = ($rank instanceof Rank) ? $rank->getName() : $rank;
		$this->data->remove($rank);
		$this->data->save();
	}

	/**
	 * @param NameTagFormat $nametag
	 * @param ChatFormat    $chat
	 * @param string[]      $permissions
	 * @param string[]      $inheritance
	 */
	public function saveRankData(string $name, array $nametag, array $chat, array $permissions = [], array $inheritance = []) : void {
		$data = [
			"nametag" => $nametag,
			"chat" => $chat,
			"permissions" => $permissions,
			"inheritance" => $inheritance
		];
		$this->data->set($name, $data);
		$this->data->save();
		$this->reload();
	}
}
