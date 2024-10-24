<?php

namespace PartyUI;

use pocketmine\player\Player;

class Party {

    private Player $leader;
    private array $members = [];
    private array $invites = []; // Properti undangan

    public function __construct(Player $leader) {
        $this->leader = $leader;
        $this->members[] = $leader;
    }

    public function isLeader(Player $player): bool {
        return $this->leader === $player;
    }

    public function invite(Player $invited): void {
        $this->invites[$invited->getName()] = $invited; // Menyimpan undangan
    }

    public function kick(Player $player): void {
        $key = array_search($player, $this->members);
        if ($key !== false) {
            unset($this->members[$key]);
        }
    }

    public function removeMember(Player $player): void {
        $key = array_search($player, $this->members);
        if ($key !== false) {
            unset($this->members[$key]);
        }
    }

    public function addMember(Player $player): void {
        $this->members[] = $player;
    }

    public function isMember(Player $player): bool {
        return in_array($player, $this->members);
    }

    public function getMembers(): array {
        return array_map(fn($member) => $member->getName(), $this->members);
    }

    // Metode baru untuk mendapatkan undangan
    public function getInvites(): array {
        return array_keys($this->invites); // Mengembalikan daftar nama pemain yang diundang
    }
}
