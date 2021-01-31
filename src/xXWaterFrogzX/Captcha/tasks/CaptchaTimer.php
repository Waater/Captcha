<?php
declare(strict_types=1);

namespace xXWaterFrogzX\Captcha\tasks;


use pocketmine\Player;
use pocketmine\scheduler\Task;
use xXWaterFrogzX\Captcha\Main;

class CaptchaTimer extends Task {
    private $main;
    public $player;
    public $timer;
    private $taskID;

    public function __construct(Main $main, Player $player) {
        $this->setPlayer($player);
        $this->setMain($main);
        $this->setHandler($this->getMain()->getScheduler()->scheduleRepeatingTask($this, 20));
        $this->timer = $this->getMain()->getConfig()->get("captcha-time");
    }
    public function onRun(int $currentTick) {
        $player = $this->getPlayer();
        if (!in_array($player, $this->getMain()->getServer()->getOnlinePlayers(), true)) {
            $this->getHandler()->cancel();
        } else {
            $this->timer--;
            if ($this->timer <= 0) {
                $this->getMain()->sendCaptcha($player);
                $this->timer = $this->getMain()->getConfig()->get("captcha-time");
            }
        }
    }
    public function getMain() : Main {
        return $this->main;
    }
    public function setMain(Main $main) {
        $this->main = $main;
    }
    public function getPlayer() : Player {
        return $this->player;
    }
    public function setPlayer(Player $player) {
        $this->player = $player;
    }
}