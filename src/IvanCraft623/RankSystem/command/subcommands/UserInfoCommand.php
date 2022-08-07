<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class UserInfoCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("userinfo", "Shows info about a user");
		$this->setPermission("ranksystem.command.userinfo");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RawStringArgument("user"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$session = $this->plugin->getSessionManager()->get($args["user"]);
		if ($sender instanceof Player) {
			$this->plugin->getFormManager()->sendUserInfo($sender, $session, $sender->hasPermission("ranksystem.command.manage"));
		} else {
			$session->onInitialize(function () use ($sender, $session) {
				$translator = $this->plugin->getTranslator();
				$permissions = "";
				foreach ($session->getUserPermissions() as $permission) {
					$time = $session->getPermissionExpTime($permission);
					if ($time !== null) {
						$time = $time - time();
						if ($time < 0) {
							$time = null;
						}
					}
					$permissions .= "\n §e - " . $permission . " §7(" . ($time === null ? $translator->translate($sender, "text.never") : Utils::getTimeTranslated($time, $translator, $sender)) . ")";
				}
				$ranks = "";
				foreach ($session->getRanks() as $rank) {
					$time = $session->getRankExpTime($rank);
					if ($time !== null) {
						$time = $time - time();
						if ($time < 0) {
							$time = null;
						}
					}
					$ranks .= "\n §e - " . $rank->getName() . " §7(" . ($time === null ? $translator->translate($sender, "text.never") : Utils::getTimeTranslated($time, $translator, $sender)) . ")";
				}
				$sender->sendMessage(
					"§r§f" . $translator->translate($sender, "text.user") . ": §a" . $session->getName() . "\n\n" .
					"§r§f" . $translator->translate($sender, "text.nametag") . ": " . $session->getNameTagFormat() . "\n" .
					"§r§f" . $translator->translate($sender, "text.chat") . ": " . $session->getChatFormat() . $translator->translate($sender, "text.hello_world") . "\n\n" .
					"§r§f" . $translator->translate($sender, "text.ranks") . ": " . $ranks . "\n" .
					"§r§f" . $translator->translate($sender, "text.permissions") . ": §a" . $permissions
				);
			});
		}
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}