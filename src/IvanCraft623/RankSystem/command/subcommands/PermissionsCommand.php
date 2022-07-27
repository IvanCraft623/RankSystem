<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\RankSystem;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

final class PermissionsCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("permissions", "See permissions of plugins or pocketmine", ["perms"]);
		$this->setPermission("ranksystem.command.permissions");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RawStringArgument("source"));
		$this->registerArgument(1, new IntegerArgument("page", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$plugin = (strtolower($args["source"]) === 'pocketmine' || strtolower($args["source"]) === 'pmmp') ? 'pocketmine' : $this->plugin->getServer()->getPluginManager()->getPlugin($args["source"]);
		if ($plugin === null) {
			$sender->sendMessage("§cPlugin " . $args["source"] . " does NOT exist!");
			return;
		}
		$permissions = ($plugin instanceof PluginBase) ? $this->plugin->getPluginPerms($plugin) : $this->plugin->getPmmpPerms();
		if (count($permissions) === 0) {
			$sender->sendMessage("§e" . $args["source"] . " doesn't have any permissions!");
			return;
		}
		$pageHeight = $sender instanceof Player ? 6 : 48;
		$chunkedPermissions = array_chunk($permissions, $pageHeight);
		$maxPageNumber = count($chunkedPermissions);
		if (!isset($args["page"]) || $args["page"] <= 0) {
			$pageNumber = 1;
		} elseif ($args["page"] > $maxPageNumber) {
			$pageNumber = $maxPageNumber;
		} else {
			$pageNumber = $args["page"];
		}
		$sender->sendMessage("§bList of all permissions from §e" . $args["source"] . " §f(§2" . $pageNumber . " §7/ §2" . $maxPageNumber . "§f)§b:");
		foreach ($chunkedPermissions[$pageNumber - 1] as $permission) {
			$sender->sendMessage(" §f- §a" . $permission->getName());
		}
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}