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

use IvanCraft623\RankSystem\libs\_1fd8385a67447511\IvanCraft623\languages\Translator;

use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\session\Session;
use IvanCraft623\RankSystem\session\SessionManager;
use IvanCraft623\RankSystem\libs\_1fd8385a67447511\jojoe77777\FormAPI\SimpleForm;

use pocketmine\player\Player;

final class UserManageForm {

	private Translator $translator;

	public function __construct() {
		$this->translator = RankSystem::getInstance()->getTranslator();
	}

	public function send(Player $player) : void {
		$form = new SimpleForm(function (Player $player, int $result = null) {
			if ($result === null) {
				return;
			}
			switch ($result) {
				case 0:
					FormManager::getInstance()->sendInsertText(
						$player, $this->translator->translate($player, "form.user_manage.title"), $this->translator->translate($player, "form.user_manage.insert_user"), $this->translator->translate($player, "text.user") . ":"
					)->onCompletion(
						function (string $user) use ($player) {
							FormManager::getInstance()->sendUserInfo($player, SessionManager::getInstance()->get($user), true);
						}, function () {} // No response
					);
					break;

				case 1:
					$sessions = [];
					foreach ($player->getServer()->getOnlinePlayers() as $pl) {
						$sessions[] = SessionManager::getInstance()->get($pl);
					}
					$this->sendSelectUserForm($player, $sessions);
					break;

				case 2:
					$this->sendSelectUserForm($player, SessionManager::getInstance()->getAll());
					break;

				default:
					# Close Form
					break;
			}
		});
		$form->setTitle($this->translator->translate($player, "form.user_manage.title"));
		$form->setContent($this->translator->translate($player, "form.user_manage.content"));
		$form->addButton($this->translator->translate($player, "form.user_manage.insert_user"), SimpleForm::IMAGE_TYPE_PATH, "textures/ui/infobulb");
		$form->addButton($this->translator->translate($player, "form.user_manage.online_users"), SimpleForm::IMAGE_TYPE_PATH, "textures/ui/World");
		$form->addButton($this->translator->translate($player, "form.user_manage.loaded_users"), SimpleForm::IMAGE_TYPE_PATH, "textures/ui/icon_map");
		$form->addButton($this->translator->translate($player, "text.exit"), SimpleForm::IMAGE_TYPE_PATH, "textures/blocks/barrier");
		$form->sendToPlayer($player);
	}

	/**
	 * @param Session[] $sessions
	 */
	public function sendSelectUserForm(Player $player, array $sessions) : void {
		$form = new SimpleForm(function (Player $player, Session $session = null) {
			if ($session === null) {
				return;
			}
			FormManager::getInstance()->sendUserInfo($player, $session, true);
		});
		$form->setTitle($this->translator->translate($player, "form.user_manage.title"));
		$form->setContent($this->translator->translate($player, "form.user_manage.select_user"));
		foreach ($sessions as $session) {
			$form->addButton($session->getName(), -1, "", $session);
		}
		$form->sendToPlayer($player);
	}
}