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

use pocketmine\utils\SingletonTrait;
use IvanCraft623\RankSystem\RankSystem as Ranks;

use RuntimeException;

final class RankManager {
	use SingletonTrait;

	/** @var Array */
	private $hierarchy = [];

	/** @var ?Rank */
	private $defaultRank = null;

	public function load() : void {
	 	foreach (Ranks::getInstance()->getConfigs("ranks.yml")->getAll() as $name => $data) {
	 		new Rank($name, $data["nametag"], $data["chat"], $data["permissions"]);
	 	}
	}

	public function getAll() : array {
		return Rank::getAll();
	}

	/**
	 * @param String|Array $rank
	 * @return ?Rank|Array
	 */
	public function getByName($names) {
		if (is_array($names)) {
			$ranks = [];
			foreach ($names as $name) {
				$rank = Rank::getByName($name);
				if ($rank !== null) {
					$ranks[] = $rank;
				}
			}
		} else {
			$ranks = Rank::getByName($names);
		}
		return $ranks;
	}

	public function getDefault() : Rank {
		if ($this->defaultRank === null) {
			$name = Ranks::getInstance()->getConfigs("config.yml")->get("Default_Rank");
			if ($name === false) {
				throw new RuntimeException("The default rank is not specified!");
			}
			if (!$this->exists($name)) {
				throw new RuntimeException("The rank: ".$name." specified as default does not exist!");
			}
			$this->defaultRank = $this->getByName($name);
		}
		return $this->defaultRank;
	}

	public function exists(string $name) : bool {
		return ($this->getByName($name) !== null);
	}

	/**
	 * @return Rank[]
	 */
	public function getHierarchy() : array {
		if ($this->hierarchy === []) {
			$this->hierarchy = $this->getByName(Ranks::getInstance()->getConfigs("config.yml")->get("Hierarchy"));
			foreach ($this->getAll() as $rank) {
				if (!in_array($rank, $this->hierarchy)) {
					$this->hierarchy[] = $rank;
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
				$hierarchicalRanks[] = $rank;
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
	 */
	public function create(string $name, array $nametag, array $chat, array $permissions = []) : void {
		$ranksConfig = Ranks::getInstance()->getConfigs("ranks.yml");
		$ranksAll = $ranksConfig->getAll();
		$newRank = [
			"nametag" => $nametag,
			"chat" => $chat,
			"permissions" => $permissions
		];
		$ranksAll[$name] = $newRank;
		$ranksConfig->setAll($ranksAll);
		$ranksConfig->save();
		$rank = new Rank($name, $nametag, $chat, $permissions);
	}

	/**
	 * @param String|Rank $rank
	 */
	public function delete($rank) {
		$rank = ($rank instanceof Rank) ? $rank->getName() : $rank;
		$ranksConfig = Ranks::getInstance()->getConfigs("ranks.yml");
		$ranksConfig->remove($rank);
		$ranksConfig->save();
	}
}