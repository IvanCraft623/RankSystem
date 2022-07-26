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

namespace IvanCraft623\RankSystem\rank;

use IvanCraft623\RankSystem\RankSystem;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

use InvalidArgumentException;
use RuntimeException;

final class RankManager {
	use SingletonTrait;

	private RankSystem $plugin;

	private Config $data;

	/**
	 * @var array<string, Rank>
	 */
	private array $ranks;

	private Rank $defaultRank;

	private array $hierarchy;

	public function __construct() {
		$this->plugin = RankSystem::getInstance();
	}

	public function load() : void {
		$this->data = $this->plugin->getConfigs("ranks.yml");
		$ranksData = $this->data->getAll();
	 	foreach ($ranksData as $name => $data) {
	 		$this->ranks[$name] = new Rank($name, $data["nametag"], $data["chat"], $data["permissions"]);
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
		return $this->ranks[$name] ?? null;
	}

	public function getDefault() : Rank {
		if (!isset($this->defaultRank)) {
			$name = $this->plugin->getConfig()->get("Default_Rank");
			if ($name === false) {
				throw new RuntimeException("The default rank is not specified!");
			}
			if (!$this->exists((string) $name)) {
				throw new RuntimeException("The rank: ".$name." specified as default does not exist!");
			}
			$this->defaultRank = $this->getRank($name);
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
			throw new InvalidArgumentException("Rank ". $rank . " not found");
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
			foreach ($this->plugin->getConfig()->get("Hierarchy", []) as $name) {
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
	 * $inheritance = ["Guest"]:
	 */
	public function create(string $name, array $nametag, array $chat, array $permissions = [], array $inheritance = []) : void {
		if (!$this->exists($name)) {
			$this->saveRankData($name, $nametag, $chat, $permissions, $inheritance);
		}
	}

	/**
	 * @param String|Rank $rank
	 */
	public function delete($rank) {
		$rank = ($rank instanceof Rank) ? $rank->getName() : $rank;
		$this->data->remove($rank);
		$this->data->save();
	}

	public function saveRankData(string $name, array $nametag, array $chat, array $permissions = [], array $inheritance = []) {
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