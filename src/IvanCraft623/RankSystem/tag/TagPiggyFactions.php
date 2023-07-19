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

namespace IvanCraft623\RankSystem\tag;

use DaPigGuy\PiggyFactions\PiggyFactions;

use IvanCraft623\RankSystem\RankSystem;
use pocketmine\player\Player;

class TagPiggyFactions
{
	/**
	 * @return PiggyFactions
	 */
    public static function getAPI() : ?PiggyFactions {
		$config = RankSystem::getInstance()->getConfig()->get("piggy-factions", boolval(false));
		if ($config === false) return null;
		return RankSystem::getPiggyFactions();
	}

	/**
	 * @param Player $player
	 * @return string
	 */
	public static function getPlayerFaction(Player $player) : string {
		$piggyFactions = self::getAPI();
		if ($piggyFactions === null) return "";
		$member = $piggyFactions->getPlayerManager()->getPlayer($player);
		if ($member === null) return "";
		if ($member !== null)
		{
			$faction = $member->getFaction();
			if ($faction === null) return "";
			return $faction->getName();
		}
	}

	/**
	 * @param Player $player
	 * @return string
	 */
	public static function getPlayerFactionPower(Player $player) : string {
		$piggyFactions = self::getAPI();
		if ($piggyFactions === null) return "";
		$member = $piggyFactions->getPlayerManager()->getPlayer($player);
		if ($member === null) return "";
		if ($member !== null)
		{
			$fpower = round($member->getPower(), 1);
			return "$fpower";
		}
	}
}