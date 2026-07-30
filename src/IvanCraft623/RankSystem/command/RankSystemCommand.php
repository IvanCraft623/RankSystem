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

namespace IvanCraft623\RankSystem\command;

use IvanCraft623\RankSystem\libs\_d3d86656f72b4055\CortexPE\Commando\BaseCommand;

use IvanCraft623\RankSystem\command\subcommands\CreateCommand;
use IvanCraft623\RankSystem\command\subcommands\CreditsCommand;
use IvanCraft623\RankSystem\command\subcommands\DeleteCommand;
use IvanCraft623\RankSystem\command\subcommands\EditCommand;
use IvanCraft623\RankSystem\command\subcommands\HelpCommand;
use IvanCraft623\RankSystem\command\subcommands\ListCommand;
use IvanCraft623\RankSystem\command\subcommands\ManageCommand;
use IvanCraft623\RankSystem\command\subcommands\PermissionsCommand;
use IvanCraft623\RankSystem\command\subcommands\RankInfoCommand;
use IvanCraft623\RankSystem\command\subcommands\RemovePermissionCommand;
use IvanCraft623\RankSystem\command\subcommands\RemoveRankCommand;
use IvanCraft623\RankSystem\command\subcommands\SetPermissionCommand;
use IvanCraft623\RankSystem\command\subcommands\SetRankCommand;
use IvanCraft623\RankSystem\command\subcommands\UserInfoCommand;
use IvanCraft623\RankSystem\RankSystem;

use pocketmine\command\CommandSender;

final class RankSystemCommand extends BaseCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct($plugin, "ranksystem", "A ranks & permissions manager by IvanCraft623.");
		$this->setAliases(["ranks"]);
		$this->setPermission("ranksystem.command");
		$this->setPermissionMessage("§cYou don't have permission to us this command!");
	}

	public function prepare() : void {
		$this->registerSubCommand(new CreateCommand($this->plugin));
		$this->registerSubCommand(new CreditsCommand($this->plugin));
		$this->registerSubCommand(new DeleteCommand($this->plugin));
		$this->registerSubCommand(new EditCommand($this->plugin));
		$this->registerSubCommand(new HelpCommand($this->plugin));
		$this->registerSubCommand(new ListCommand($this->plugin));
		$this->registerSubCommand(new ManageCommand($this->plugin));
		$this->registerSubCommand(new PermissionsCommand($this->plugin));
		$this->registerSubCommand(new RankInfoCommand($this->plugin));
		$this->registerSubCommand(new RemovePermissionCommand($this->plugin));
		$this->registerSubCommand(new RemoveRankCommand($this->plugin));
		$this->registerSubCommand(new SetPermissionCommand($this->plugin));
		$this->registerSubCommand(new SetRankCommand($this->plugin));
		$this->registerSubCommand(new UserInfoCommand($this->plugin));
	}

	/**
	 * @param mixed[] $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$sender->sendMessage("§cNo subcommand provided, try using: /" . $aliasUsed . " help");
	}
}