<?php

namespace PartyPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\form\Form;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

    private $partyManager;

    public function onEnable(): void {
        $this->partyManager = new PartyManager();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info(TextFormat::GREEN . "Party Plugin Enabled");
    }

    public function showPartyUI(Player $player): void {
        $form = new class($player) implements Form {
            private $player;

            public function __construct(Player $player) {
                $this->player = $player;
            }

            public function jsonSerialize(): array {
                return [
                    "type" => "form",
                    "title" => "Party Menu",
                    "content" => "Select an action",
                    "buttons" => [
                        ["text" => "Create Party"],
                        ["text" => "Join Party"],
                        ["text" => "Leave Party"],
                        ["text" => "List Parties"],
                        ["text" => "View Party Members"]
                    ]
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) return;
                switch ($data) {
                    case 0:
                        PartyManager::getInstance()->createParty($player);
                        $player->sendMessage("Party Created!");
                        break;
                    case 1:
                        PartyManager::getInstance()->showJoinPartyUI($player);
                        break;
                    case 2:
                        PartyManager::getInstance()->leaveParty($player);
                        break;
                    case 3:
                        PartyManager::getInstance()->listParties($player);
                        break;
                    case 4:
                        PartyManager::getInstance()->showPartyMembers($player);
                        break;
                }
            }
        };
        $player->sendForm($form);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            return false;
        }

        switch (strtolower($command->getName())) {
            case "party":
                $this->showPartyUI($sender);
                return true;
            case "partyinvite":
                if (isset($args[0])) {
                    $invitedPlayer = $this->getServer()->getPlayerByPrefix($args[0]);
                    if ($invitedPlayer !== null) {
                        PartyManager::getInstance()->invitePlayer($sender, $invitedPlayer);
                        $sender->sendMessage("Invited {$invitedPlayer->getName()} to the party!");
                    } else {
                        $sender->sendMessage("Player not found!");
                    }
                } else {
                    $sender->sendMessage("Usage: /partyinvite <player>");
                }
                return true;
            case "partykick":
                if (isset($args[0])) {
                    $targetPlayer = $this->getServer()->getPlayerByPrefix($args[0]);
                    if ($targetPlayer !== null) {
                        PartyManager::getInstance()->kickPlayer($sender, $targetPlayer);
                        $sender->sendMessage("Kicked {$targetPlayer->getName()} from the party!");
                    } else {
                        $sender->sendMessage("Player not found!");
                    }
                } else {
                    $sender->sendMessage("Usage: /partykick <player>");
                }
                return true;
        }

        return false;
    }
}
