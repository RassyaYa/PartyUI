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

    private PartyManager $partyManager; // Tipe data untuk partyManager

    public function onEnable(): void {
        $this->partyManager = new PartyManager(); // Inisialisasi PartyManager
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info(TextFormat::GREEN . "Party Plugin Enabled");
    }

    public function getPartyManager(): PartyManager {
        return $this->partyManager; // Mengembalikan instance PartyManager
    }

    public function showPartyUI(Player $player): void {
        $form = new class($player, $this->partyManager) implements Form {
            private Player $player;
            private PartyManager $partyManager;

            public function __construct(Player $player, PartyManager $partyManager) {
                $this->player = $player; // Menyimpan player
                $this->partyManager = $partyManager; // Menyimpan PartyManager
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
                        $this->partyManager->createParty($this->player); // Gunakan $this->player
                        $this->player->sendMessage("Party Created!");
                        break;
                    case 1:
                        $this->partyManager->showJoinPartyUI($this->player);
                        break;
                    case 2:
                        $this->partyManager->leaveParty($this->player);
                        break;
                    case 3:
                        $this->partyManager->listParties($this->player);
                        break;
                    case 4:
                        $this->partyManager->showPartyMembers($this->player);
                        break;
                }
            }
        };
        $player->sendForm($form); // Kirim form ke player
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            return false; // Pastikan sender adalah Player
        }

        switch (strtolower($command->getName())) {
            case "party":
                $this->showPartyUI($sender); // Tampilkan UI party
                return true;
            case "partyinvite":
                if (isset($args[0])) {
                    $invitedPlayer = $this->getServer()->getPlayerByPrefix($args[0]);
                    if ($invitedPlayer !== null) {
                        $this->partyManager->invitePlayer($sender, $invitedPlayer); // Undang pemain
                        $sender->sendMessage("Invited {$invitedPlayer->getName()} to your party.");
                    } else {
                        $sender->sendMessage("Player not found.");
                    }
                }
                return true;
            // Tambah case lain sesuai kebutuhan
        }

        return false; // Jika command tidak dikenali
    }
}
