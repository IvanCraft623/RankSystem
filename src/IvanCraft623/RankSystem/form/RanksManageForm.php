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

use IvanCraft623\RankSystem\libs\_b471fb4b0afaf307\IvanCraft623\languages\Translator;

use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\rank\RankManager;
use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\utils\Utils;
use IvanCraft623\RankSystem\libs\_b471fb4b0afaf307\jojoe77777\FormAPI\SimpleForm;

use pocketmine\player\Player;

final class RanksManageForm {

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
						$player,
						$this->translator->translate($player, "form.ranks_manage.title"),
						$this->translator->translate($player, "form.ranks_manage.create"),
						$this->translator->translate($player, "text.rank") . ":"
					)->onCompletion(
						function (string $rank) use ($player) {
							if (RankManager::getInstance()->exists($rank)) {
								$player->sendMessage($this->translator->translate($player, "rank.already_exists", [
									"{%rank}" => $rank
								]));
							} else {
								FormManager::getInstance()->sendRankEditor(
									$player,
									$rank,
									["prefix" => "§8[§7" . $rank . "§8] ", "nameColor" => "§f"],
									["prefix" => "§8[§7" . $rank . "§8] ", "nameColor" => "§f", "chatFormat" => "§e: §7"]
								);
							}
						}, function () {} // No response
					);
					break;

				case 1:
					FormManager::getInstance()->sendSelectRank($player, $this->translator->translate($player, "form.ranks_manage.title"))->onCompletion(
						function (Rank $rank) use ($player) {
							FormManager::getInstance()->sendRankEditor(
								$player,
								$rank->getName(),
								$rank->getNameTagFormat(),
								$rank->getChatFormat(),
								$rank->getPermissions(),
								Utils::getRanksNames($rank->getInheritance())
							);
						}, function () {} // No response
					);
					break;

				case 2:
					FormManager::getInstance()->sendSelectRank($player, $this->translator->translate($player, "form.ranks_manage.title"))->onCompletion(
						function (Rank $rank) use ($player) {
							FormManager::getInstance()->sendConfirmation(
								$player,
								$this->translator->translate($player, "form.ranks_manage.title"),
								$this->translator->translate($player, "form.ranks_manage.delete.confirm", [
									"{%rank}" => $rank->getName()
								])
							)->onCompletion(
								function (bool $result) use ($player, $rank) {
									if ($result) {
										RankManager::getInstance()->delete($rank);
										$player->sendMessage($this->translator->translate($player, "rank.delete.success", [
											"{%rank}" => $rank->getName()
										]));
									}
								}, function () {} // No response
							);
						}, function () {} // No response
					);
					break;

				case 3:
					FormManager::getInstance()->sendSelectRank($player, $this->translator->translate($player, "form.ranks_manage.title"))->onCompletion(
						function (Rank $rank) use ($player) {
							FormManager::getInstance()->sendRankInfo($player, $rank);
						}, function () {} // No response
					);
					break;

				default:
					# Close Form
					break;
			}
		});
		$form->setTitle($this->translator->translate($player, "form.ranks_manage.title"));
		$form->setContent($this->translator->translate($player, "form.select_category"));
		$form->addButton($this->translator->translate($player, "text.create"), SimpleForm::IMAGE_TYPE_PATH, "textures/ui/anvil-plus");
		$form->addButton($this->translator->translate($player, "text.edit"), SimpleForm::IMAGE_TYPE_PATH, "textures/gui/newgui/Bundle/PaintBrush");
		$form->addButton($this->translator->translate($player, "text.delete"), SimpleForm::IMAGE_TYPE_PATH, "textures/ui/icon_trash");
		$form->addButton($this->translator->translate($player, "text.information"), SimpleForm::IMAGE_TYPE_PATH, "textures/items/map_filled");
		$form->addButton($this->translator->translate($player, "text.exit"), SimpleForm::IMAGE_TYPE_PATH, "textures/blocks/barrier");
		$form->sendToPlayer($player);
	}
}