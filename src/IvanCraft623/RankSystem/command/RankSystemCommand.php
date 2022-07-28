<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command;

use CortexPE\Commando\BaseCommand;

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
		$this->registerSubCommand(new CreditsCommand());
		$this->registerSubCommand(new DeleteCommand($this->plugin));
		$this->registerSubCommand(new EditCommand($this->plugin));
		$this->registerSubCommand(new HelpCommand());
		$this->registerSubCommand(new ListCommand());
		$this->registerSubCommand(new ManageCommand($this->plugin));
		$this->registerSubCommand(new PermissionsCommand($this->plugin));
		$this->registerSubCommand(new RankInfoCommand($this->plugin));
		$this->registerSubCommand(new RemovePermissionCommand($this->plugin));
		$this->registerSubCommand(new RemoveRankCommand($this->plugin));
		$this->registerSubCommand(new SetPermissionCommand($this->plugin));
		$this->registerSubCommand(new SetRankCommand($this->plugin));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$sender->sendMessage("§cNo subcommand provided, try using: /" . $aliasUsed . " help");
	}
}