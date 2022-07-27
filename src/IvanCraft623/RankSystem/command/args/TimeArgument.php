<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\args;

use CortexPE\Commando\args\RawStringArgument;

use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\command\CommandSender;

final class TimeArgument extends RawStringArgument {

	public function getTypeName() : string {
		return "time";
	}

	public function parse(string $argument, CommandSender $sender) : ?int {
		return Utils::parseDuration($argument);
	}
}