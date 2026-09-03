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

use IvanCraft623\RankSystem\libs\_7c604e93589d947c\CortexPE\Commando\BaseCommand;
use IvanCraft623\RankSystem\libs\_7c604e93589d947c\CortexPE\Commando\BaseSubCommand;
use IvanCraft623\RankSystem\libs\_7c604e93589d947c\CortexPE\Commando\constraint\InGameRequiredConstraint;

use IvanCraft623\RankSystem\RankSystem;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class ManageCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("manage", "Open a form to manage RankSystem");
		$this->setPermission("ranksystem.command.manage");
	}

	protected function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));
	}

	/**
	 * @param Player  $sender
	 * @param mixed[] $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$this->plugin->getFormManager()->sendManager($sender);
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}