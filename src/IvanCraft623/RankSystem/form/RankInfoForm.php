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
use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\player\Player;

final class RankInfoForm {
	
	public function __construct() {
	}

	public function send(Player $player, Rank $rank) : void {
		$translator = RankSystem::getInstance()->getTranslator();
		$form = new SimpleForm(null);
		$form->setTitle($translator->translate($player, "form.rank_info.title"));
		$nametag = $rank->getNameTagFormat();
		$chat = $rank->getChatFormat();
		$permissions = "";
		foreach ($rank->getPermissions() as $permission) {
			$permissions .= "\n §e - " . $permission;
		}
		$form->setContent(
			"§r§f" . $translator->translate($player, "text.rank") . ": §a" . $args["rank"]->getName() . "\n\n" .
			"§r§f" . $translator->translate($player, "text.nametag") . ": " . $nametag["prefix"] . $nametag["nameColor"] . "Steve" . "\n" .
			"§r§f" . $translator->translate($player, "text.chat") . ": " . $chat["prefix"] . $chat["nameColor"] . $translator->translate($player, "text.steve") . $chat["chatFormat"] . $translator->translate($player, "text.hello_world") . "\n" 	.
			"§r§f" . $translator->translate($player, "text.inheritance") . ": §a" . Utils::ranks2string($args["rank"]->getInheritance()) . "\n" .
			"§r§f" . $translator->translate($player, "text.permissions") . ": §a" . $permissions
		);
		$form->sendToPlayer($player);
	}
}