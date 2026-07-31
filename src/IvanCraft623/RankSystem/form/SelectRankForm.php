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

use IvanCraft623\RankSystem\rank\Rank;

use IvanCraft623\RankSystem\rank\RankManager;
use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\libs\_fa599532ba07189f\jojoe77777\FormAPI\SimpleForm;

use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;

final class SelectRankForm {

	public function __construct() {
	}

	/**
	 * @param ?Rank[] $ranks
	 *
	 * @phpstan-return Promise<Rank>
	 */
	public function send(Player $player, string $title, ?array $ranks = null) : Promise {
		/** @var PromiseResolver<Rank> $resolver */
		$resolver = new PromiseResolver();
		$form = new SimpleForm(function (Player $player, ?Rank $rank = null) use ($resolver) {
			if ($rank === null) {
				$resolver->reject();
			} else {
				$resolver->resolve($rank);
			}
		});
		$form->setTitle($title);
		$form->setContent(RankSystem::getInstance()->getTranslator()->translate($player, "form.select_rank.content"));
		foreach (($ranks ?? RankManager::getInstance()->getAll()) as $rank) {
			$form->addButton($rank->getName(), -1, "", $rank);
		}
		$form->sendToPlayer($player);
		return $resolver->getPromise();
	}
}