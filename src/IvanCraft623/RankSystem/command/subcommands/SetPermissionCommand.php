<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\command\args\TimeArgument;
use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class SetPermissionCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("setpermission", "Set a permissions to a user", ["setperm"]);
		$this->setPermission("ranksystem.command.setpermission");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RawStringArgument("user"));
		$this->registerArgument(1, new RawStringArgument("permission"));
		$this->registerArgument(2, new TimeArgument("time", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$translator = $this->plugin->getTranslator();
		if (array_key_exists("time", $args) && $args["time"] === "null") {
			$sender->sendMessage(
				$translator->translate($sender, "time.invalid")."\n".
				$translator->translate($sender, "time.arguments")."\n".
				$translator->translate($sender, "time.example")
			);
		} else {
			$session = $this->plugin->getSessionManager()->get($args["user"]);
			$session->onInitialize(function () use ($session, $sender, $args, $translator) {
				if ($session->hasUserPermission($args["permission"])) {
					$sender->sendMessage($translator->translate($sender, "user.set_permission.already_has", [
						"{%user}" => $session->getName(),
						"{%permission}" => $args["permission"]
					]));
				} else {
					$time = isset($args["time"]) ? ((int) ($args["time"])) : null;

					$session->setPermission($args["permission"], $args["time"] ?? null);
					$sender->sendMessage($translator->translate($sender, "user.set_permission.success", [
						"{%user}" => $session->getName(),
						"{%permission}" => $args["permission"],
						"{%time}" => (isset($args["time"]) ? Utils::getTimeTranslated($time - time(), $translator, $sender) : $translator->translate($sender, "text.never"))
					]));
				}
			});
		}
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}