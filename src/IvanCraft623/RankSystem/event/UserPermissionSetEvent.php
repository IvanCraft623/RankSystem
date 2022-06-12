<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\event;

use IvanCraft623\RankSystem\session\Session;

use pocketmine\event\Event;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

class UserPermissionSetEvent extends Event implements Cancellable {
	use CancellableTrait;

	protected Session $session;

	protected string $permission;

	protected ?int $expTime;

	/**
	 * UserPermissionSetEvent constructor.
	 * @param Session $session
	 * @param string $permission
	 * @param ?int $expTime
	 */
	public function __construct(Session $session, string $permission, ?int $expTime) {
		$this->session = $session;
		$this->permission = $permission;
		$this->expTime = $expTime;
	}

	/**
	 * @return Session
	 */
	public function getSession() : Session {
		return $this->session;
	}

	/**
	 * @return string
	 */
	public function getPermission() : string {
		return $this->permission;
	}

	/**
	 * @return ?Int
	 */
	public function getExpTime() : ?int {
		return $this->expTime;
	}
}