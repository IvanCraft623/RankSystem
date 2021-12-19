<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\event;

use IvanCraft623\RankSystem\session\Session;

use pocketmine\event\Event;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

class UserPermissionRemoveEvent extends Event implements Cancellable {
	use CancellableTrait;

	protected Session $session;

	protected string $permission;

	/**
	 * UserPermissionRemoveEvent constructor.
	 * @param Session $session
	 * @param string $permission
	 */
	public function __construct(Session $session, string $permission) {
		$this->session = $session;
		$this->permission = $permission;
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
}