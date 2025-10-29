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

namespace IvanCraft623\RankSystem\libs\CortexPE\Commando;

use IvanCraft623\RankSystem\libs\CortexPE\Commando\exception\HookAlreadyRegistered;
use IvanCraft623\RankSystem\libs\CortexPE\Commando\store\SoftEnumStore;
use IvanCraft623\RankSystem\libs\CortexPE\Commando\traits\IArgumentable;
use IvanCraft623\RankSystem\libs\CortexPE\Commando\libs\muqsit\simplepackethandler\SimplePacketHandler;
use pocketmine\command\CommandSender;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use ReflectionClass;

class PacketHooker implements Listener {
	private static bool $isRegistered = false;
	private static bool $isIntercepting = false;

	public static function isRegistered(): bool {
		return self::$isRegistered;
	}

	public static function register(Plugin $registrant): void {
		if (self::$isRegistered) {
			throw new HookAlreadyRegistered("Event listener is already registered by another plugin.");
		}

		$interceptor = SimplePacketHandler::createInterceptor($registrant, EventPriority::NORMAL, false);
		$interceptor->interceptOutgoing(function (AvailableCommandsPacket $pk, NetworkSession $target): bool {
			if (self::$isIntercepting) return true;
			$p = $target->getPlayer();

			foreach ($pk->commandData as $commandName => $commandData) {
				// --- FIX: prevenir crash si la key no es string ---
				if (!is_string($commandName)) {
					continue;
				}
				// ---------------------------------------------------
				$cmd = Server::getInstance()->getCommandMap()->getCommand($commandName);
				if ($cmd instanceof BaseCommand) {
					foreach ($cmd->getConstraints() as $constraint) {
						if (!$constraint->isVisibleTo($p)) {
							continue 2;
						}
					}
					$pk->commandData[$commandName]->overloads = self::generateOverloads($p, $cmd);
				}
			}

			$pk->softEnums = SoftEnumStore::getEnums();
			self::$isIntercepting = true;
			$target->sendDataPacket($pk);
			self::$isIntercepting = false;
			return false;
		});

		self::$isRegistered = true;
	}

	private static function generateOverloads(CommandSender $cs, BaseCommand $command): array {
		$overloads = [];

		foreach ($command->getSubCommands() as $label => $subCommand) {
			if (!$subCommand->testPermissionSilent($cs) || $subCommand->getName() !== $label) { // hide aliases
				continue;
			}
			foreach ($subCommand->getConstraints() as $constraint) {
				if (!$constraint->isVisibleTo($cs)) {
					continue 2;
				}
			}
			$scParam = new CommandParameter();
			$scParam->paramName = $label;
			$scParam->paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_FLAG_ENUM;
			$scParam->isOptional = false;
			$scParam->enum = new CommandEnum($label, [$label]);

			$overloadList = self::generateOverloadList($subCommand);
			if (!empty($overloadList)) {
				foreach ($overloadList as $overload) {
					$overloads[] = new CommandOverload(false, [$scParam, ...$overload->getParameters()]);
				}
			} else {
				$overloads[] = new CommandOverload(false, [$scParam]);
			}
		}

		foreach (self::generateOverloadList($command) as $overload) {
			$overloads[] = $overload;
		}

		return $overloads;
	}

	private static function generateOverloadList(IArgumentable $argumentable): array {
		$input = $argumentable->getArgumentList();
		$combinations = [];
		$outputLength = array_product(array_map("count", $input));
		$indexes = [];
		foreach ($input as $k => $charList) {
			$indexes[$k] = 0;
		}
		do {
			$set = [];
			foreach ($indexes as $k => $index) {
				$param = $set[$k] = clone $input[$k][$index]->getNetworkParameterData();

				if (isset($param->enum) && $param->enum instanceof CommandEnum) {
					$refClass = new ReflectionClass(CommandEnum::class);
					$refProp = $refClass->getProperty("enumName");
					$refProp->setAccessible(true);
					$refProp->setValue($param->enum, "enum#" . spl_object_id($param->enum));
				}
			}
			$combinations[] = new CommandOverload(false, $set);

			foreach ($indexes as $k => $v) {
				$indexes[$k]++;
				$lim = count($input[$k]);
				if ($indexes[$k] >= $lim) {
					$indexes[$k] = 0;
					continue;
				}
				break;
			}
		} while (count($combinations) !== $outputLength);

		return $combinations;
	}
}
