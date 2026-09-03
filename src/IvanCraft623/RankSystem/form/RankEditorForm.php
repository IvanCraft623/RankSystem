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

use IvanCraft623\RankSystem\libs\_2f17bee6c3ea9171\IvanCraft623\languages\Translator;
use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\RankSystem;

use IvanCraft623\RankSystem\libs\_2f17bee6c3ea9171\jojoe77777\FormAPI\CustomForm;
use IvanCraft623\RankSystem\libs\_2f17bee6c3ea9171\jojoe77777\FormAPI\SimpleForm;

use pocketmine\player\Player;
use function explode;
use function implode;

/**
 * @phpstan-import-type NameTagFormat from Rank
 * @phpstan-import-type ChatFormat from Rank
 */
final class RankEditorForm {

	private Translator $translator;

	/**
	 * @param NameTagFormat $nametag
	 * @param ChatFormat    $chat
	 * @param string[]      $permissions
	 * @param string[]      $inheritance
	 */
	public function __construct(
		private string $name,
		private array $nametag = ["prefix" => "", "nameColor" => "§f"],
		private array $chat = ["prefix" => "", "nameColor" => "§f", "chatFormat" => "§e: §7"],
		private array $permissions = [],
		private array $inheritance = []
	) {
		$this->translator = RankSystem::getInstance()->getTranslator();
	}

	private function save() : void {
		RankSystem::getInstance()->getRankManager()->saveRankData($this->name, $this->nametag, $this->chat, $this->permissions, $this->inheritance);
	}

	public function send(Player $player) : void {
		$form = new SimpleForm(function (Player $player, int $result = null) {
			if ($result === null) {
				return;
			}
			switch ($result) {
				case 0:
					$this->sendNametagForm($player);
				break;

				case 1:
					$this->sendChatForm($player);
				break;

				case 2:
					$this->sendPermissionsForm($player);
				break;

				case 3:
					$this->sendInheritanceForm($player);
				break;

				case 4:
					$this->save();
				break;

				default:
					# Close Form
				break;
			}
		});
		$form->setTitle($this->translator->translate($player, "form.rank_editor.title"));
		$form->setContent(
			"§f" . $this->translator->translate($player, "text.rank") . ": §a" . $this->name . "\n\n" .
			"§f" . $this->translator->translate($player, "text.nametag") . ": " . $this->nametag["prefix"] . $this->nametag["nameColor"] . "Steve" . "\n" .
			"§f" . $this->translator->translate($player, "text.chat") . ": " . $this->chat["prefix"] . $this->chat["nameColor"] . $this->translator->translate($player, "text.steve") . $this->chat["chatFormat"] . $this->translator->translate($player, "text.hello_world")
		);
		$form->addButton($this->translator->translate($player, "text.nametag"), SimpleForm::IMAGE_TYPE_PATH, "textures/items/name_tag");
		$form->addButton($this->translator->translate($player, "text.chat"), SimpleForm::IMAGE_TYPE_PATH, "textures/gui/newgui/Language18");
		$form->addButton($this->translator->translate($player, "text.permissions"), SimpleForm::IMAGE_TYPE_PATH, "textures/items/map_filled");
		$form->addButton($this->translator->translate($player, "text.inheritance"), SimpleForm::IMAGE_TYPE_PATH, "textures/gui/newgui/Local");
		$form->addButton($this->translator->translate($player, "text.save-exit"), SimpleForm::IMAGE_TYPE_PATH, "textures/ui/check");
		$form->addButton($this->translator->translate($player, "text.exit"), SimpleForm::IMAGE_TYPE_PATH, "textures/blocks/barrier");
		$form->sendToPlayer($player);
	}

	private function sendNametagForm(Player $player) : void {
		$form = new CustomForm(function (Player $player, array $result = null) {
			if ($result !== null) {
				$data = $result;
				unset($data[0]);
				/** @phpstan-var NameTagFormat $data */
				$this->nametag = $data;
			}
			$this->send($player);
		});
		$form->setTitle($this->translator->translate($player, "form.rank_editor.title"));
		$form->addLabel($this->translator->translate($player, "form.rank_editor.content"));
		$form->addInput($this->translator->translate($player, "text.prefix") . ":", "", $this->nametag["prefix"], "prefix");
		$form->addInput($this->translator->translate($player, "text.name_color") . ":", "", $this->nametag["nameColor"], "nameColor");
		$form->sendToPlayer($player);
	}

	private function sendChatForm(Player $player) : void {
		$form = new CustomForm(function (Player $player, array $result = null) {
			if ($result !== null) {
				$data = $result;
				unset($data[0]);
				/** @phpstan-var ChatFormat $data */
				$this->chat = $data;
			}
			$this->send($player);
		});
		$form->setTitle($this->translator->translate($player, "form.rank_editor.title"));
		$form->addLabel($this->translator->translate($player, "form.rank_editor.content"));
		$form->addInput($this->translator->translate($player, "text.prefix") . ":", "", $this->chat["prefix"], "prefix");
		$form->addInput($this->translator->translate($player, "text.name_color") . ":", "", $this->chat["nameColor"], "nameColor");
		$form->addInput($this->translator->translate($player, "text.chat_format") . ":", "", $this->chat["chatFormat"], "chatFormat");
		$form->sendToPlayer($player);
	}

	private function sendPermissionsForm(Player $player) : void {
		$form = new CustomForm(function (Player $player, array $result = null) {
			if ($result !== null) {
				$this->permissions = explode(", ", $result["permissions"]);
			}
			$this->send($player);
		});
		$form->setTitle($this->translator->translate($player, "form.rank_editor.title"));
		$form->addLabel(
			$this->translator->translate($player, "form.rank_editor.content") . "\n\n" .
			$this->translator->translate($player, "form.rank_editor.permissions_example")
		);
		$form->addInput($this->translator->translate($player, "text.permissions") . ":", "", implode(", ", $this->permissions), "permissions");
		$form->sendToPlayer($player);
	}

	private function sendInheritanceForm(Player $player) : void {
		$form = new CustomForm(function (Player $player, array $result = null) {
			if ($result !== null) {
				$this->inheritance = explode(", ", $result["inheritance"]);
			}
			$this->send($player);
		});
		$form->setTitle($this->translator->translate($player, "form.rank_editor.title"));
		$form->addLabel(
			$this->translator->translate($player, "form.rank_editor.inheritance") . "\n\n" .
			$this->translator->translate($player, "form.rank_editor.inheritance_example")
		);
		$form->addInput($this->translator->translate($player, "text.inheritance") . ":", "", implode(", ", $this->inheritance), "inheritance");
		$form->sendToPlayer($player);
	}
}