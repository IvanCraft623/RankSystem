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

use IvanCraft623\RankSystem\libs\_95221d011a678a74\CortexPE\Commando\args\IntegerArgument;
use IvanCraft623\RankSystem\libs\_95221d011a678a74\CortexPE\Commando\BaseCommand;
use IvanCraft623\RankSystem\libs\_95221d011a678a74\CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\RankSystem;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function array_chunk;
use function count;
use function is_int;
use function spl_object_id;

final class HelpCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("help", "See RankSystem command", ["?"]);
		$this->setPermission("ranksystem.command.help");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new IntegerArgument("page", true));
	}

	/**
	 * @param mixed[] $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$avaiable = [];
		foreach ($this->parent->getSubCommands() as $subCommand) {
			$id = spl_object_id($subCommand);
			if (!isset($avaiable[$id]) && $subCommand->testPermissionSilent($sender)) {
				$avaiable[$id] = $subCommand;
			}
		}
		$pageHeight = $sender instanceof Player ? 6 : 48;
		$chunkedCommands = array_chunk($avaiable, $pageHeight);
		$maxPageNumber = count($chunkedCommands);
		if (!isset($args["page"]) || !is_int($args["page"]) || $args["page"] <= 0) {
			$pageNumber = 1;
		} elseif ($args["page"] > $maxPageNumber) {
			$pageNumber = $maxPageNumber;
		} else {
			$pageNumber = $args["page"];
		}
		$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "command.help.text", [
			"{%page}" => $pageNumber,
			"{%total_pages}" => $maxPageNumber
		]));
		foreach ($chunkedCommands[$pageNumber - 1] as $subCommand) {
			$sender->sendMessage("/ranksystem " . $subCommand->getName() . " §7(" . $subCommand->getDescription() . ")");
		}
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}