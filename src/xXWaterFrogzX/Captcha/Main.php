<?php

declare(strict_types=1);

namespace xXWaterFrogzX\Captcha;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use xXWaterFrogzX\Captcha\command\CaptchaCommand;
use xXWaterFrogzX\Captcha\tasks\CaptchaTimer;

class Main extends PluginBase implements Listener {

    public $menu;
    public $passedCaptcha = false;

    public function onEnable() {
        $this->saveDefaultConfig();
        $this->getResource("config.yml");

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
        $this->getServer()->getCommandMap()->registerAll("captcha", [
            new CaptchaCommand($this)
        ]);
    }
    public function setCaptcha(Player $player, bool $passedCaptcha) {
        $this->passedCaptcha = $passedCaptcha;
    }
    public function getCaptcha(Player $player) : bool {
        return $this->passedCaptcha;
    }
    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        new CaptchaTimer($this, $player);
    }
    public function sendCaptcha(Player $player) {
        $this->menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $this->menu->setListener(InvMenu::readonly(\Closure::fromCallable([$this, "onClick"])));
        $itemC = Item::get($this->getConfig()->get("captcha-item"));
        $this->menu->getInventory()->setItem(mt_rand(0, 53), $itemC);
        $this->menu->setName(TextFormat::RESET . "Click the: " . $itemC->getName());
        foreach ($this->menu->getInventory()->getContents(true) as $slot => $item) {
            if ($item->getId() == Item::AIR) {
                $this->menu->getInventory()->setItem($slot, Item::get(Item::STAINED_GLASS_PANE, 7, 1)->setCustomName(" "));
            }
        }
        $this->menu->setInventoryCloseListener(function(Player $player) : void{
            if ($this->getCaptcha($player) == false) {
                $player->kick($this->getConfig()->get("captcha-kick"), false);
            }
        });
        $this->menu->send($player);
    }

    public function onClick(DeterministicInvMenuTransaction $transaction) : void {
        $player = $transaction->getPlayer();
        $action = $transaction->getAction();
        $itemClickedOn = $transaction->getItemClicked();
        if ($itemClickedOn->getId() == $this->getConfig()->get("captcha-item")) {
            $player->sendMessage($this->getConfig()->get("captcha-pass"));
            $this->setCaptcha($player, true);
            $player->removeWindow($action->getInventory());
            $this->setCaptcha($player, false);
        } else {
            $this->setCaptcha($player, false);
            $player->removeWindow($action->getInventory());
        }
    }
}