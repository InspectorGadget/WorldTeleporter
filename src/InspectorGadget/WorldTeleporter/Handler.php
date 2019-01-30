<?php
/**
 * Created by PhpStorm.
 * User: RTG
 * Date: 1/30/2019
 * Time: 10:59 AM
 *
 * .___   ________
 * |   | /  _____/
 * |   |/   \  ___
 * |   |\    \_\  \
 * |___| \______  /
 *              \/
 *
 * All rights reserved InspectorGadget (c) 2019
 **/


namespace InspectorGadget\WorldTeleporter;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

use pocketmine\utils\TextFormat as TF;

class Handler extends PluginBase {

    public function onEnable(): void {
        if (!is_dir($this->getDataFolder())) { @mkdir($this->getDataFolder()); }
        if (!is_file($this->getDataFolder() . "config.yml")) { $this->saveDefaultConfig(); }

        $this->getLogger()->info('Loading all levels for you');
        $this->loadAllLevels();
    }

    public function loadAllLevels() {
        $levelName = scandir($this->getServer()->getDataPath() . "worlds/");

        foreach ($levelName as $level) {
            if ($level === "." || $level === "..") {
                continue;
            }
            $this->getServer()->loadLevel($level);
        }
    }

    public function getAllWorlds(CommandSender $sender) {
        $levels = $this->getServer()->getLevels();
        $sender->sendMessage(TF::GREEN . " -- Loaded Worlds -- ");
        foreach($levels as $level) {
            $sender->sendMessage(TF::GREEN . "- {$level->getName()}");
        }
    }

    public function enableConsole(): bool {
        $cfg = $this->getConfig()->get("console-log");
        if ($cfg !== true) {
            return false;
        } else {
            return true;
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch(strtolower($command->getName())) {
            case "world":
                if (!$sender->isOp() || !$sender->hasPermission("world.command")) {
                    $sender->sendMessage(TF::RED . "You have no permission to use this command!");
                    return true;
                }

                if (!isset($args[0])) {
                    $sender->sendMessage("Usage: /world help");
                    return true;
                }

                switch(strtolower($args[0])) {
                    case "help":
                        $sender->sendMessage("-- Commands -- \n /world list - Lists all worlds! \n /world tp [world] - Teleports you to a certain world! \n ~ Author: " . TF::RED . "InspectorGadget");
                        return true;
                    break;

                    case "list":
                        $this->getAllWorlds($sender);
                        return true;
                    break;

                    case "teleport":
                    case "tp":
                        if (!$sender instanceof Player) {
                            $sender->sendMessage(TF::RED . "[WorldTeleporter] Please be in-game!");
                            return true;
                        }

                        if (!isset($args[1])) {
                            $sender->sendMessage(TF::GREEN . "[WorldTeleporter] Usage: /world tp [world]");
                            return true;
                        }

                        if (!$this->getServer()->getLevelByName($args[1])) {
                            $sender->sendMessage("[WorldTeleporter] Well, does the world: " . TF::RED . "{$args[1]}" . TF::RESET . " even exist? \n World name should be the same as /world list");
                            return true;
                        }

                        $sender->teleport($this->getServer()->getLevelByName($args[1])->getSafeSpawn());
                        $sender->sendMessage(TF::GREEN . "[WorldTeleporter] Teleported you to world: " . TF::RED . "{$args[1]}");
                        if ($this->enableConsole()) {
                            $this->getLogger()->info("{$sender->getName()} has teleported to world: {$args[1]}");
                        }
                        return true;
                    break;
                }
            break;
        }
    }

    public function onDisable(): void {
        $this->getLogger()->info("Goodbye sir!");
    }

}