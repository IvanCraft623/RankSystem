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

namespace IvanCraft623\RankSystem\command\args;

use IvanCraft623\RankSystem\libs\_7534e2e543ef05e7\CortexPE\Commando\args\StringEnumArgument;

use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\rank\RankManager;

use pocketmine\command\CommandSender;
use function array_keys;

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

	/**
	 * @return string[]
	 */
	public function getEnumValues() : array {
		return array_keys(RankManager::getInstance()->getAll());
	}
}