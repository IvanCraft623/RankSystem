<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\event;

use IvanCraft623\RankSystem\{RankSystem as Ranks, rank\Rank, session\Session};

use pocketmine\event\{Cancellable, Event};

class UserRankRemoveEvent extends Event implements Cancellable {

	/** @var null $handlerList */
	public static $handlerList = \null;

	/** @var Session $session */
	protected $session;

	/** @var Rank $rank */
	protected $rank;

	/**
	 * UserRankRemoveEvent constructor.
	 * @param Session $session
	 * @param Rank $rank
	 */
	public function __construct(Session $session, Rank $rank) {
		$this->session = $session;
		$this->rank = $rank;
	}

	/**
	 * @return Session
	 */
	public function getSession() : Session {
		return $this->session;
	}

	/**
	 * @return Rank
	 */
	public function getRank() : Rank {
		return $this->rank;
	}
}