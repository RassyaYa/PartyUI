<?php

namespace PartyPlugin;

use pocketmine\player\Player;

class Party {

    private $leader;
    private $members = [];
    private $invites = [];

    public function __construct(Player $leader) {
        $this->leader = $leader;
        $this->members[] = $leader;
    }

    public function getLeader(): Player {
        return $this->leader;
    }

    public function isLeader(Player $player): bool {
        return $player === $this->leader;
    }

    public function invite(Player $player): void {
        $this->invites[$player->getName()] = $player;
        $player->sendMessage("You have been invited to join the party by " . $this->leader->getName());
    }

    public function addMember(Player $player): void {
        if (isset($this->invites[$player->getName()])) {
            $this->members[] = $player;
            unset($this->invites[$player->getName()]);
            foreach ($this->members as $member) {
                $member->sendMessage($player->getName() . " has joined the party!");
            }
        }
    }

    public function removeMember(Player $player): void {
        if (($key = array_search($player, $this->members, true)) !== false) {
            unset($this->members[$key]);
            foreach ($this->members as $member) {
                $member->sendMessage($player->getName() . " has left the party.");
            }
        }
    }

    public function isMember(Player $player): bool {
        return in_array($player, $this->members, true);
    }

    public function kick(Player $player): void {
        if (($key = array_search($player, $this->members, true)) !== false) {
            unset($this->members[$key]);
            foreach ($this->members as $member) {
                $member->sendMessage($player->getName() . " has been kicked from the party.");
            }
            $player->sendMessage("You have been kicked from the party.");
        }
    }

    public function getMembers(): array {
        return array_map(function(Player $player) {
            return $player->getName();
        }, $this->members);
    }
}
