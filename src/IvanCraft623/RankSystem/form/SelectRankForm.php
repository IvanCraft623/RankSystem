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
use IvanCraft623\RankSystem\rank\RankManager;

use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;

final class SelectRankForm {
	
	public function __construct() {
	}

	/**
	 * @param Rank[] $rank
	 *
	 * @phpstan-return Promise<Rank>
	 */
	public function send(Player $player, string $title, ?array $ranks = null) : Promise {
		$resolver = new PromiseResolver();
		$form = new SimpleForm(function (Player $player, ?Rank $rank = null) use ($resolver) {
			if ($rank === null) {
				$resolver->reject();
			} else {
				$resolver->resolve($rank);
			}
		});
		$form->setTitle($title);
		$form->setContent("Select a rank.");
		foreach (($ranks ?? RankManager::getInstance()->getAll()) as $rank) {
			$form->addButton($rank->getName(), -1, "", $rank);
		}
		$form->sendToPlayer($player);
		return $resolver->getPromise();
	}
}