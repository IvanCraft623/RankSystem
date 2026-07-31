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

namespace IvanCraft623\RankSystem\tag;

use Closure;

use IvanCraft623\RankSystem\session\Session;

use pocketmine\utils\Utils;

final class Tag {

	/**
	 * @param Closure(Session $uset): string $valueCallback
	 */
	public function __construct(
		protected string $name,
		protected Closure $valueCallback
	) {
		Utils::validateCallableSignature(static function(Session $user) : string{ return ""; }, $valueCallback);
	}

	public function getId() : string {
		return "{" . $this->name . "}";
	}

	public function getName() : string {
		return $this->name;
	}

	public function getValue(Session $user) : string {
		return ($this->valueCallback)($user);
	}
}
