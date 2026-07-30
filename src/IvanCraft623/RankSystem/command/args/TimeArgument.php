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

use IvanCraft623\RankSystem\libs\_226f1fc83fe584a7\CortexPE\Commando\args\RawStringArgument;

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