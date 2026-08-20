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

use IvanCraft623\RankSystem\libs\_ab9d215404d83cdb\CortexPE\Commando\BaseCommand;
use IvanCraft623\RankSystem\libs\_ab9d215404d83cdb\CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\command\args\RankArgument;
use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\RankSystem;

use pocketmine\command\CommandSender;
use pocketmine\utils\AssumptionFailedError;

final class DeleteCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("delete", "Delete a Rank");
		$this->setPermission("ranksystem.command.delete");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RankArgument("rank"));
	}

	/**
	 * @param mixed[] $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$rank = $args["rank"];
		if (!$rank instanceof Rank) {
			throw new AssumptionFailedError("Expected Rank argument \"rank\"");
		}

		if ($rank === $this->plugin->getRankManager()->getDefault()) {
			$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "rank.delete.default"));
		} else {
			$this->plugin->getRankManager()->delete($rank);
			$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "rank.delete.success"));
		}
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}