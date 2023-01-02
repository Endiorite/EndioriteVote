<?php

namespace Vote;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;

class Main extends PluginBase
{

    use SingletonTrait;

    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    protected function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->getServer()->getCommandMap()->register("vote", new VoteCommand());
    }

    public function loot(Player $player){
        $items = Main::getInstance()->getConfig()->getNested("Loot.Item");
        $command = Main::getInstance()->getConfig()->getNested("Loot.Command");
        foreach($items as $key => $item){
            $exp = explode(",", $item);
            $drop = ItemFactory::getInstance()->get((int)$exp[0], (int)$exp[1], (int)$exp[2]);
            if(isset($exp[3]) && isset($exp[4])){
                $drop->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse($exp[3]), $exp[4]));
            }

            $player->getInventory()->addItem($drop);
        }

        foreach($command as $key => $cmd){
            Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace(["{player}"], ["\"" . $player->getName() . "\""], $cmd));
        }
    }

}