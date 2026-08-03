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

use IvanCraft623\RankSystem\libs\_dd2697c4b4b2f1c0\CortexPE\Commando\args\RawStringArgument;
use IvanCraft623\RankSystem\libs\_dd2697c4b4b2f1c0\CortexPE\Commando\BaseCommand;
use IvanCraft623\RankSystem\libs\_dd2697c4b4b2f1c0\CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\command\args\RankArgument;
use IvanCraft623\RankSystem\command\args\TimeArgument;
use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\command\CommandSender;
use function array_key_exists;
use function time;

final class SetRankCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("setrank", "Set a rank to a user", ["set"]);
		$this->setPermission("ranksystem.command.setrank");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RawStringArgument("user"));
		$this->registerArgument(1, new RankArgument("rank"));
		$this->registerArgument(2, new TimeArgument("time", true));
	}

	/**
	 * @param mixed[] $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$translator = $this->plugin->getTranslator();
		if (array_key_exists("time", $args) && $args["time"] === "null") {
			$sender->sendMessage(
				$translator->translate($sender, "time.invalid") . "\n" .
				$translator->translate($sender, "time.arguments") . "\n" .
				$translator->translate($sender, "time.example")
			);
		} else {
			$session = $this->plugin->getSessionManager()->get($args["user"]);
			$session->onInitialize(function () use ($session, $sender, $args, $translator) {
				if ($session->hasRank($args["rank"])) {
					$sender->sendMessage($translator->translate($sender, "user.set_rank.already_has", [
						"{%user}" => $session->getName(),
						"{%rank}" => $args["rank"]->getName()
					]));
				} else {
					$time = isset($args["time"]) ? ((int) ($args["time"])) : null;

					$session->setRank($args["rank"], $time);
					$sender->sendMessage($translator->translate($sender, "user.set_rank.success", [
						"{%user}" => $session->getName(),
						"{%rank}" => $args["rank"]->getName(),
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