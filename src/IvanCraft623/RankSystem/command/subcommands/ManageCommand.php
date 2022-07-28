<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;

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
	 * @param Player $sender
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$this->plugin->getFormManager()->sendManager($sender);
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}