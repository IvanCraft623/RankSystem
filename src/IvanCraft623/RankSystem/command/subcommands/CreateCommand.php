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

namespace IvanCraft623\RankSystem\command\subcommands;

use IvanCraft623\RankSystem\libs\_3363d0a4024b663e\CortexPE\Commando\args\RawStringArgument;
use IvanCraft623\RankSystem\libs\_3363d0a4024b663e\CortexPE\Commando\BaseCommand;
use IvanCraft623\RankSystem\libs\_3363d0a4024b663e\CortexPE\Commando\BaseSubCommand;
use IvanCraft623\RankSystem\libs\_3363d0a4024b663e\CortexPE\Commando\constraint\InGameRequiredConstraint;

use IvanCraft623\RankSystem\RankSystem;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;

use function is_string;

final class CreateCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("create", "Create a Rank");
		$this->setPermission("ranksystem.command.create");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RawStringArgument("rank"));
		$this->addConstraint(new InGameRequiredConstraint($this));
	}

	/**
	 * @param Player  $sender
	 * @param mixed[] $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$rankName = $args["rank"];
		if (!is_string($rankName)) {
			throw new AssumptionFailedError("Expected string argument \"rank\"");
		}

		if ($this->plugin->getRankManager()->exists($rankName)) {
			$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "rank.already_exists", [
				"{%rank}" => $rankName
			]));
		} else {
			$this->plugin->getFormManager()->sendRankEditor(
				$sender,
				$rankName,
				["prefix" => "§8[§7" . $rankName . "§8] ", "nameColor" => "§f"],
				["prefix" => "§8[§7" . $rankName . "§8] ", "nameColor" => "§f", "chatFormat" => "§e: §7"]
			);
		}
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}