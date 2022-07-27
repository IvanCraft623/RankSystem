<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\args;

use CortexPE\Commando\args\StringEnumArgument;

use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\rank\RankManager;

use pocketmine\command\CommandSender;

final class RankArgument extends StringEnumArgument {

	public function getTypeName() : string {
		return "rank";
	}

	public function canParse(string $testString, CommandSender $sender) : bool {
		return $this->getValue($testString) instanceof Rank;
	}

	public function parse(string $argument, CommandSender $sender) : ?Rank {
		return $this->getValue($argument);
	}

	public function getValue(string $string) : ?Rank {
		return RankManager::getInstance()->getRank($string);
	}

	public function getEnumValues() : array {
		return array_keys(RankManager::getInstance()->getAll());
	}
}