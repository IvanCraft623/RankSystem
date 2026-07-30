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

namespace IvanCraft623\RankSystem\rank;

use function array_merge;

/**
 * @phpstan-type NameTagFormat array{
 * 	prefix: string,
 * 	nameColor: string
 * }
 *
 * @phpstan-type ChatFormat array{
 * 	prefix: string,
 * 	nameColor: string,
 * 	chatFormat: string
 * }
 */
final class Rank {

	private string $name;

	/** @var NameTagFormat */
	private array $nametag;

	/** @var ChatFormat */
	private array $chat;

	/** @var string[] */
	private array $permissions = [];

	/** @var Rank[] */
	private array $inheritance = [];

	/**
	 * Example of how provide the variables:
	 *
	 * $nametag = [
	 *		"prefix" => "§2[§aCat§2] ",
	 *		"nameColor" => "§6"
	 * ];
	 *
	 * $chat = [
	 *		"prefix" => "§2[§aCat§2] ",
	 *		"nameColor" => "§6",
	 *		"chatFormat" => "§5: §b"
	 * ];
	 *
	 * $perms = ["example.perm", "example.perm2"];
	 *
	 * @param NameTagFormat $nametag
	 * @param ChatFormat    $chat
	 * @param string[]      $permissions
	 */
	public function __construct(string $name, array $nametag, array $chat, array $permissions = []) {
		$this->name = $name;
		$this->nametag = $nametag;
		$this->chat = $chat;
		$this->permissions = $permissions;
	}

	public function getName() : string {
		return $this->name;
	}

	/**
	 * @return NameTagFormat
	 */
	public function getNameTagFormat() : array {
		return $this->nametag;
	}

	/**
	 * @return ChatFormat
	 */
	public function getChatFormat() : array {
		return $this->chat;
	}

	/**
	 * @return string[]
	 */
	public function getPermissions() : array {
		return $this->permissions;
	}

	/**
	 * @return Rank[]
	 */
	public function getInheritance() : array {
		return $this->inheritance;
	}

	/**
	 * @internal
	 */
	public function addInheritance(Rank $rank) : void {
		if ($rank === $this) {
			throw new \InvalidArgumentException("A rank cannot inherit ranks from itself");
		}

		$this->inheritance[] = $rank;
		$this->permissions = array_merge($this->permissions, $rank->getPermissions());
	}
}