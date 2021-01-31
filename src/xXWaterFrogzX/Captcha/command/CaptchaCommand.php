<?php


namespace xXWaterFrogzX\Captcha\command;


use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xXWaterFrogzX\Captcha\Main;


class CaptchaCommand extends PluginCommand {
    private $main;
    private $player;
    public function __construct(Main $main) {
        parent::__construct("captcha", $main);
        $this->setAliases(["cp"]);
        $this->setPermission("captcha.command");
        $this->setDescription("Send a captcha to a player!");
        $this->setUsage("/captcha (Player)");
        $this->main = $main;
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be executed in game!");
        } else {
            if ($sender->hasPermission($this->getPermission())) {
                if (!isset($args[0])) {
                    $sender->sendMessage(TextFormat::RED . "Usage: /captcha (player)");
                }
                if (isset($args[0])) {
                    $player = $sender->getServer()->getPlayer($args[0]);
                    if ($player instanceof Player) {
                        $sender->sendMessage($this->main->getConfig()->get("captcha-sentmessage"));
                        $this->main->sendCaptcha($player);
                    } else {
                        $sender->sendMessage(TextFormat::RED . "No player online with that name!");
                    }
                }
            } else {
                $sender->sendMessage($this->main->getConfig()->get("captcha-noperm"));
            }
        }
    }
}