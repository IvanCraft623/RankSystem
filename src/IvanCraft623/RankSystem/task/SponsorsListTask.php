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

namespace IvanCraft623\RankSystem\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use pocketmine\utils\Utils;
use function is_array;
use function json_decode;

class SponsorsListTask extends AsyncTask {

	private const TLS_KEY_COMPLETION_CALLBACK = "completionCallback";

	private string $sponsorsURL;

	/**
	 * @param \Closure(string[]|null $members) : void $onCompletion
	 */
	public function __construct(string $url, \Closure $onCompletion) {
		$this->storeLocal(self::TLS_KEY_COMPLETION_CALLBACK, $onCompletion);
		$this->sponsorsURL = $url;
	}

	public function onRun() : void {
		$body = Internet::getURL($this->sponsorsURL)?->getBody();
		if ($body === null) {
			$this->setResult(null);
			return;
		}

		$data = json_decode($body, true);
		if (!is_array($data)) {
			$this->setResult(null);
			return;
		}

		try {
			Utils::validateArrayValueType($data, static function(string $_) : void{});
			$this->setResult($data);
		} catch(\TypeError $e) {
			$this->setResult(null);
		}
	}

	public function onCompletion() : void {
		/** @var \Closure|null $callback */
		$callback = $this->fetchLocal(self::TLS_KEY_COMPLETION_CALLBACK);
		if ($callback !== null) {
			$callback($this->getResult());
		}
	}
}
