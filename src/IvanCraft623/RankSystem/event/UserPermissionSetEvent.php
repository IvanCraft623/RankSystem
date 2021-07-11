<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\event;

use IvanCraft623\RankSystem\{RankSystem as Ranks, session\Session};

use pocketmine\event\{Event, Cancellable, CancellableTrait};

class UserPermissionSetEvent extends Event implements Cancellable {
	use CancellableTrait;

	/** @var null $handlerList */
	public static $handlerList = \null;

	/** @var Session $session */
	protected $session;

	/** @var String $permission */
	protected $permission;

	/**
	 * UserPermissionSetEvent constructor.
	 * @param Session $session
	 * @param String $permission
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