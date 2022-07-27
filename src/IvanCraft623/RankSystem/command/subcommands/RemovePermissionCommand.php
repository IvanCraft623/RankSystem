<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\RankSystem;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class RemovePermissionCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("removepermission", "Remove a permission from a user", ["removeperm"]);
		$this->setPermission("ranksystem.command.removepermission");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RawStringArgument("user"));
		$this->registerArgument(1, new RawStringArgument("permission"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$session = $this->plugin->getSessionManager()->get($args["user"]);
		$session->onInitialize(function () use ($session, $sender, $args) {
			if (!$session->hasPermission($args["permission"])) {
				$sender->sendMessage("§c" . $args["user"] . " does not has the " . $args["permission"] . " permission!");
			} else {
				$session->removePermission($args["permission"]);
				$sender->sendMessage("§bYou have successfully §cremoved§b the §e" . $args["permission"] . " §bpermission from §a". $args["user"]);
			}
		});
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}