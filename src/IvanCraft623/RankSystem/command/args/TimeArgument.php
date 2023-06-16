<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\args;

use CortexPE\Commando\args\RawStringArgument;

use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\command\CommandSender;

final class TimeArgument extends RawStringArgument {

	public function getTypeName() : string {
		return "time";
	}

	public function parse(string $argument, CommandSender $sender) : string {
		//Hacky, but Commando no longer allow us to set our owns return types :(
		$result = Utils::parseDuration($argument, RankSystem::getInstance()->getTranslator(), $sender);
		return $result !== null ? ((string) $result) : "null";
	}
}