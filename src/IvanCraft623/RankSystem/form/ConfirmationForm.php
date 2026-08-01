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

use IvanCraft623\RankSystem\libs\_95221d011a678a74\jojoe77777\FormAPI\ModalForm;

use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;

final class ConfirmationForm {

	public function __construct() {
	}

	/**
	 * @phpstan-return Promise<bool>
	 */
	public function send(Player $player, string $title, string $content) : Promise {
		/** @var PromiseResolver<bool> $resolver */
		$resolver = new PromiseResolver();
		$form = new ModalForm(function (Player $player, ?bool $result = null) use ($resolver) {
			if ($result === null) {
				$resolver->reject();
			} else {
				$resolver->resolve($result);
			}
		});
		$translator = RankSystem::getInstance()->getTranslator();
		$form->setTitle($title);
		$form->setContent($content);
		$form->setButton1($translator->translate($player, "text.yes"));
		$form->setButton2($translator->translate($player, "text.no"));
		$form->sendToPlayer($player);
		return $resolver->getPromise();
	}
}