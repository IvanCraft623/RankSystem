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
			$translator = $this->plugin->getTranslator();
			if (!$session->hasUserPermission($args["permission"])) {
				$sender->sendMessage($translator->translate($sender, "user.remove_permission.no_permission", [
					"{%user}" => $args["user"],
					"{%permission}" => $args["permission"]
				]));
			} else {
				$session->removePermission($args["permission"]);
				$sender->sendMessage($translator->translate($sender, "user.remove_permission.success", [
					"{%user}" => $args["user"],
					"{%permission}" => $args["permission"]
				]));
			}
		});
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}