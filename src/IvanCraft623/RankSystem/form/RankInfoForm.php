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

use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\utils\Utils;
use IvanCraft623\RankSystem\libs\_99cb2885b9e1a116\jojoe77777\FormAPI\SimpleForm;

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
			"§r§f" . $translator->translate($player, "text.rank") . ": §a" . $rank->getName() . "\n\n" .
			"§r§f" . $translator->translate($player, "text.nametag") . ": " . $nametag["prefix"] . $nametag["nameColor"] . "Steve" . "\n" .
			"§r§f" . $translator->translate($player, "text.chat") . ": " . $chat["prefix"] . $chat["nameColor"] . $translator->translate($player, "text.steve") . $chat["chatFormat"] . $translator->translate($player, "text.hello_world") . "\n" .
			"§r§f" . $translator->translate($player, "text.inheritance") . ": §a" . Utils::ranks2string($rank->getInheritance()) . "\n" .
			"§r§f" . $translator->translate($player, "text.permissions") . ": §a" . $permissions
		);
		$form->sendToPlayer($player);
	}
}