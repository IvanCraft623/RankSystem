<?php

#Plugin By:

/*
	8888888                            .d8888b.                   .d888 888     .d8888b.   .d8888b.   .d8888b.  
	  888                             d88P  Y88b                 d88P"  888    d88P  Y88b d88P  Y88b d88P  Y88b 
	  888                             888    888                 888    888    888               888      .d88P 
	  888  888  888  8888b.  88888b.  888        888d888 8888b.  888888 888888 888d888b.       .d88P     8888"  
	  888  888  888     "88b 888 "88b 888        888P"      "88b 888    888    888P "Y88b  .od888P"       "Y8b. 
	  888  Y88  88P .d888888 888  888 888    888 888    .d888888 888    888    888    888 d88P"      888    888 
	  888   Y8bd8P  888  888 888  888 Y88b  d88P 888    888  888 888    Y88b.  Y88b  d88P 888"       Y88b  d88P 
	8888888  Y88P   "Y888888 888  888  "Y8888P"  888    "Y888888 888     "Y888  "Y8888P"  888888888   "Y8888P"  
*/

declare(strict_types=1);

namespace IvanCraft623\RankSystem\rank;

use IvanCraft623\RankSystem\RankSystem as Ranks;
use IvanCraft623\RankSystem\form\{Form, CustomForm, ModalForm, SimpleForm};

use pocketmine\Player;

/**
 * Class RankModifier
 */
final class RankModifier {

	/** @var Player */
	private $player;

	/** @var String */
	private $name;

	/** @var Array */
	private $nametag = [];

	/** @var Array */
	private $chat = [];

	/** @var String[] */
	private $permissions = [];
	
	public function __construct(Player $player, string $name, array $nametag = ["prefix" => "", "nameColor" => "§f"], array $chat = ["prefix" => "", "nameColor" => "§f", "chatFormat" => "§a: §7"], array $permissions = []) {
		$this->player = $player;
		$this->name = $name;
		$this->nametag = $nametag;
		$this->chat = $chat;
		$this->permissions = $permissions;

		$this->sendMainForm();
	}

	private function save() : void {
		Ranks::getInstance()->getRankManager()->saveRankData($this->name, $this->nametag, $this->chat, $this->permissions);
	}

	private function sendMainForm() : void {
		$player = $this->player;
		$form = new SimpleForm(function (Player $player, int $result = null) {
			if ($result === null) {
				return;
			}
			switch ($result) {
				case 0:
					$this->sendNametagForm();
				break;
				
				case 1:
					$this->sendChatForm();
				break;
				
				case 2:
					$this->sendPermissionsForm();
				break;
				
				case 3:
					$this->save();
				break;
				
				case 3:
					# Close Form
				break;
			}
		});
		$form->setTitle("§l§7» §5Rank Modifier Panel §7«");
		$form->setContent(
			"§fRank: §a".$this->name."\n\n".
			"§fNametag: ". $this->nametag["prefix"].$this->nametag["nameColor"]."Steve"."\n".
			"§fChat: ". $this->chat["prefix"].$this->chat["nameColor"]."Steve".$this->chat["chatFormat"]."Hello world!"
		);
		$form->addButton("Nametag", 0, "textures/ui/icon_steve");
		$form->addButton("Chat", 0, "textures/ui/message");
		$form->addButton("Permissions", 0, "textures/items/map_filled");
		$form->addButton("Save and Exit", 0, "textures/ui/check");
		$form->addButton("Exit", 0, "textures/blocks/barrier");
		$form->sendToPlayer($player);
	}

	private function sendNametagForm() : void {
		$player = $this->player;
		$form = new CustomForm(function (Player $player, array $result = null) {
			if ($result !== null) {
				$data = $result;
				unset($data[0]);
				$this->nametag = $data;
			}
			$this->sendMainForm();
		});
		$form->setTitle("§l§7» §5Rank Modifier Panel §7«");
		$form->addLabel("§7Modify the data to your liking!");
		$form->addInput("Prefix:", "", $this->nametag["prefix"], "prefix");
		$form->addInput("Name Color:", "", $this->nametag["nameColor"], "nameColor");
		$form->sendToPlayer($player);
	}

	private function sendChatForm() : void {
		$player = $this->player;
		$form = new CustomForm(function (Player $player, array $result = null) {
			if ($result !== null) {
				$data = $result;
				unset($data[0]);
				$this->chat = $data;
			}
			$this->sendMainForm();
		});
		$form->setTitle("§l§7» §5Rank Modifier Panel §7«");
		$form->addLabel("§7Modify the data to your liking!");
		$form->addInput("Prefix:", "", $this->chat["prefix"], "prefix");
		$form->addInput("Name Color:", "", $this->chat["nameColor"], "nameColor");
		$form->addInput("Chat Format:", "", $this->chat["chatFormat"], "chatFormat");
		$form->sendToPlayer($player);
	}

	private function sendPermissionsForm() : void {
		$player = $this->player;
		$form = new CustomForm(function (Player $player, array $result = null) {
			if ($result !== null) {
				$this->permissions = explode(", ", $result["permissions"]);
			}
			$this->sendMainForm();
		});
		$form->setTitle("§l§7» §5Rank Modifier Panel §7«");
		$form->addLabel("§7Modify the data to your liking!\n\nExample: §eexample.permission, an.awasome.permission");
		$form->addInput("Permissions:", "", implode(", ", $this->permissions), "permissions");
		$form->sendToPlayer($player);
	}
}