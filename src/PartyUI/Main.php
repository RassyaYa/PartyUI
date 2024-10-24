<?php

namespace PartyUI;

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
        // Inisialisasi PartyManager dan memastikan kita memanfaatkannya.
        $this->partyManager = new PartyManager();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info(TextFormat::GREEN . "Party Plugin Enabled");
    }

    // Menggunakan partyManager
    public function getPartyManager(): PartyManager {
        return $this->partyManager;
    }

    public function showPartyUI(Player $player): void {
        $form = new class($player, $this->getPartyManager()) implements Form {
            private $player;
            private $partyManager;

            public function __construct(Player $player, PartyManager $partyManager) {
                $this->player = $player;
                $this->partyManager = $partyManager;
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
                        $this->partyManager->createParty($player); // Menggunakan partyManager
                        $player->sendMessage("Party Created!");
                        break;
                    case 1:
                        $this->partyManager->showJoinPartyUI($player);
                        break;
                    case 2:
                        $this->partyManager->leaveParty($player);
                        break;
                    case 3:
                        $this->partyManager->listParties($player);
                        break;
                    case 4:
                        $this->partyManager->showPartyMembers($player);
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
                        $this->partyManager->invitePlayer($sender, $invitedPlayer);
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
                        $this->partyManager->kickPlayer($sender, $targetPlayer);
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
