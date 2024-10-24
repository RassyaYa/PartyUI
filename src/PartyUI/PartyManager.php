<?php

namespace PartyUI;

use pocketmine\player\Player;

class PartyManager {

    private static $instance;
    private array $parties = [];

    public function __construct() {
        self::$instance = $this;
    }

    public static function getInstance(): PartyManager {
        return self::$instance;
    }

    public function createParty(Player $player): void {
        if ($this->isInParty($player)) {
            $player->sendMessage("You are already in a party!");
            return;
        }
        $this->parties[$player->getName()] = new Party($player);
    }

    public function invitePlayer(Player $leader, Player $invited): void {
        $party = $this->getPlayerParty($leader);
        if ($party !== null && $party->isLeader($leader)) {
            $party->invite($invited);
            $leader->sendMessage("Invited {$invited->getName()} to your party.");
        } else {
            $leader->sendMessage("You are not the leader of a party!");
        }
    }

    public function kickPlayer(Player $leader, Player $target): void {
        $party = $this->getPlayerParty($leader);
        if ($party !== null && $party->isLeader($leader)) {
            $party->kick($target);
            $leader->sendMessage("Kicked {$target->getName()} from the party.");
        } else {
            $leader->sendMessage("You are not the leader of a party!");
        }
    }

    public function leaveParty(Player $player): void {
        $party = $this->getPlayerParty($player);
        if ($party !== null) {
            $party->removeMember($player);
            $player->sendMessage("You have left the party.");
        } else {
            $player->sendMessage("You are not in a party!");
        }
    }

    public function getPlayerParty(Player $player): ?Party {
        foreach ($this->parties as $party) {
            if ($party->isMember($player)) {
                return $party;
            }
        }
        return null;
    }

    public function isInParty(Player $player): bool {
        return $this->getPlayerParty($player) !== null;
    }

    public function showJoinPartyUI(Player $player): void {
        // Implement UI for listing parties to join
    }

    public function listParties(Player $player): void {
        // Display available parties to the player
    }

    public function showPartyMembers(Player $player): void {
        $party = $this->getPlayerParty($player);
        if ($party !== null) {
            $members = $party->getMembers();
            $player->sendMessage("Party members: " . implode(", ", $members));
        } else {
            $player->sendMessage("You are not in a party!");
        }
    }

    // Metode baru untuk menampilkan undangan
    public function showInvites(Player $player): void {
        $party = $this->getPlayerParty($player);
        if ($party !== null) {
            $invites = $party->getInvites();
            if (empty($invites)) {
                $player->sendMessage("No invitations.");
            } else {
                $player->sendMessage("Invited players: " . implode(", ", $invites));
            }
        } else {
            $player->sendMessage("You are not in a party!");
        }
    }
}
