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

namespace IvanCraft623\RankSystem\form;

use IvanCraft623\RankSystem\RankSystem;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;

use pocketmine\player\Player;

final class RankEditorForm {
	
	public function __construct(
		private string $name,
		private array $nametag = ["prefix" => "", "nameColor" => "§f"],
		private array $chat = ["prefix" => "", "nameColor" => "§f", "chatFormat" => "§e: §7"],
		private array $permissions = [],
		private array $inheritance = []
	) {}

	private function save() : void {
		RankSystem::getInstance()->getRankManager()->saveRankData($this->name, $this->nametag, $this->chat, $this->permissions, $this->inheritance);
	}

	public function send(Player $player) : void {
		$form = new SimpleForm(function (Player $player, int $result = null) {
			if ($result === null) {
				return;
			}
			switch ($result) {
				case 0:
					$this->sendNametagForm($player);
				break;
				
				case 1:
					$this->sendChatForm($player);
				break;
				
				case 2:
					$this->sendPermissionsForm($player);
				break;
				
				case 3:
					$this->sendInheritanceForm($player);
				break;
				
				case 4:
					$this->save();
				break;
				
				default:
					# Close Form
				break;
			}
		});
		$form->setTitle("Rank Editor");
		$form->setContent(
			"§fRank: §a" . $this->name . "\n\n" .
			"§fNametag: " . $this->nametag["prefix"] . $this->nametag["nameColor"] . "Steve" . "\n" .
			"§fChat: " . $this->chat["prefix"] . $this->chat["nameColor"] . "Steve".$this->chat["chatFormat"] . "Hello world!"
		);
		$form->addButton("Nametag", SimpleForm::IMAGE_TYPE_PATH, "textures/items/name_tag");
		$form->addButton("Chat", SimpleForm::IMAGE_TYPE_PATH, "textures/gui/newgui/Language18");
		$form->addButton("Permissions", SimpleForm::IMAGE_TYPE_PATH, "textures/items/map_filled");
		$form->addButton("Inheritance", SimpleForm::IMAGE_TYPE_PATH, "textures/gui/newgui/Local");
		$form->addButton("Save and Exit", SimpleForm::IMAGE_TYPE_PATH, "textures/ui/check");
		$form->addButton("Exit", SimpleForm::IMAGE_TYPE_PATH, "textures/blocks/barrier");
		$form->sendToPlayer($player);
	}

	private function sendNametagForm(Player $player) : void {
		$form = new CustomForm(function (Player $player, array $result = null) {
			if ($result !== null) {
				$data = $result;
				unset($data[0]);
				$this->nametag = $data;
			}
			$this->send($player);
		});
		$form->setTitle("Rank Editor");
		$form->addLabel("§7Modify the data to your liking!");
		$form->addInput("Prefix:", "", $this->nametag["prefix"], "prefix");
		$form->addInput("Name Color:", "", $this->nametag["nameColor"], "nameColor");
		$form->sendToPlayer($player);
	}

	private function sendChatForm(Player $player) : void {
		$form = new CustomForm(function (Player $player, array $result = null) {
			if ($result !== null) {
				$data = $result;
				unset($data[0]);
				$this->chat = $data;
			}
			$this->send($player);
		});
		$form->setTitle("Rank Editor");
		$form->addLabel("§7Modify the data to your liking!");
		$form->addInput("Prefix:", "", $this->chat["prefix"], "prefix");
		$form->addInput("Name Color:", "", $this->chat["nameColor"], "nameColor");
		$form->addInput("Chat Format:", "", $this->chat["chatFormat"], "chatFormat");
		$form->sendToPlayer($player);
	}

	private function sendPermissionsForm(Player $player) : void {
		$form = new CustomForm(function (Player $player, array $result = null) {
			if ($result !== null) {
				$this->permissions = explode(", ", $result["permissions"]);
			}
			$this->send($player);
		});
		$form->setTitle("Rank Editor");
		$form->addLabel("§7Modify the data to your liking!\n\nExample: §eexample.permission, an.awasome.permission");
		$form->addInput("Permissions:", "", implode(", ", $this->permissions), "permissions");
		$form->sendToPlayer($player);
	}

	private function sendInheritanceForm(Player $player) : void {
		$form = new CustomForm(function (Player $player, array $result = null) {
			if ($result !== null) {
				$this->inheritance = explode(", ", $result["inheritance"]);
			}
			$this->send($player);
		});
		$form->setTitle("Rank Editor");
		$form->addLabel("§7It will inherit the permissions from other ranks.\n\nExample: §eAdmin, Owner");
		$form->addInput("Inheritance:", "", implode(", ", $this->inheritance), "inheritance");
		$form->sendToPlayer($player);
	}
}