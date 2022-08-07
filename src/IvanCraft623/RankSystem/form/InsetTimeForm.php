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

use IvanCraft623\RankSystem\RankSystem;

use jojoe77777\FormAPI\CustomForm;

use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;

final class InsetTimeForm {
	
	public function __construct() {
	}

	/**
	 * @phpstan-return Promise<int>
	 */
	public function send(Player $player, string $title, string $content) : Promise {
		$resolver = new PromiseResolver();
		$form = new CustomForm(function (Player $player, array $result = null) use ($resolver) {
			if ($result === null) {
				$resolver->reject();
			} else {
				unset($result["content"]);
				$time = 0;
				foreach ($result as $seconds => $value) {
					$time += (int) $seconds * abs((int) $value);
				}
				$resolver->resolve($time);
			}
		});
		$translator = RankSystem::getInstance()->getTranslator();
		$form->setTitle($title);
		if ($content !== "") {
			$form->addLabel($content, "content");
		}
		# TODO: Upper case the first letter
		$form->addInput($translator->translate($player, "text.time.months") . ":", "", "0", "2628000");
		$form->addInput($translator->translate($player, "text.time.days") . ":", "", "0", "86400");
		$form->addInput($translator->translate($player, "text.time.minutes") . ":", "", "0", "60");
		$form->addInput($translator->translate($player, "text.time.seconds") . ":", "", "0", "1");
		$form->sendToPlayer($player);
		return $resolver->getPromise();
	}
}