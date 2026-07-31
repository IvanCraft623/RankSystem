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

use IvanCraft623\RankSystem\libs\_3d291e54ddfd903d\jojoe77777\FormAPI\SimpleForm;

use pocketmine\player\Player;

final class ManageForm {

	public function __construct() {
	}

	public function send(Player $player) : void {
		$form = new SimpleForm(function (Player $player, int $result = null) {
			if ($result === null) {
				return;
			}
			switch ($result) {
				case 0:
					FormManager::getInstance()->sendRanksManager($player);
					break;

				case 1:
					FormManager::getInstance()->sendUserManager($player);
					break;

				default:
					# Close Form
					break;
			}
		});
		$translator = RankSystem::getInstance()->getTranslator();
		$form->setTitle($translator->translate($player, "form.manage.title"));
		$form->setContent($translator->translate($player, "form.select_category"));
		$form->addButton($translator->translate($player, "text.ranks"), SimpleForm::IMAGE_TYPE_PATH, "textures/ui/op");
		$form->addButton($translator->translate($player, "text.users"), SimpleForm::IMAGE_TYPE_PATH, "textures/ui/FriendsIcon");
		$form->addButton($translator->translate($player, "text.exit"), SimpleForm::IMAGE_TYPE_PATH, "textures/blocks/barrier");
		$form->sendToPlayer($player);
	}
}