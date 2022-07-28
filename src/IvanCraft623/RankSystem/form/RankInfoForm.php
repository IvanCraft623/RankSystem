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

namespace IvanCraft623\RankSystem\form;

use jojoe77777\FormAPI\SimpleForm;

use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\player\Player;

final class RankInfoForm {
	
	public function __construct() {
	}

	public function send(Player $player, Rank $rank) : void {
		$form = new SimpleForm(null);
		$form->setTitle("Rank Info");
		$nametag = $rank->getNameTagFormat();
		$chat = $rank->getChatFormat();
		$permissions = "";
		foreach ($rank->getPermissions() as $permission) {
			$permissions .= "\n §e - " . $permission;
		}
		$form->setContent(
			"§r§fRank: §a" . $rank->getName() . "\n\n" .
			"§r§fNametag: " . $nametag["prefix"] . $nametag["nameColor"] . "Steve" . "\n" .
			"§r§fChat: " . $chat["prefix"] . $chat["nameColor"] . "Steve".$chat["chatFormat"] . "Hello world!" . "\n" .
			"§r§fInheritance: §a" . Utils::ranks2string($rank->getInheritance()) . "\n" .
			"§r§fPermissions: §a" . $permissions
		);
		$form->sendToPlayer($player);
	}
}