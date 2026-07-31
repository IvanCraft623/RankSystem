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

namespace IvanCraft623\RankSystem\form;

use IvanCraft623\RankSystem\RankSystem;

use IvanCraft623\RankSystem\libs\_fbe832378929cc45\jojoe77777\FormAPI\CustomForm;

use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use function abs;

final class InsetTimeForm {

	public function __construct() {
	}

	/**
	 * @phpstan-return Promise<int>
	 */
	public function send(Player $player, string $title, string $content) : Promise {
		/** @var PromiseResolver<int> $resolver */
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