<?php

namespace Vote;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Internet;

class VoteCommand extends Command
{

    public function __construct()
    {
        parent::__construct("vote", Main::getInstance()->getConfig()->get("command-description"), "/vote", []);
    }

    /**
     * @inheritDoc
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player){
            $key = Main::getInstance()->getConfig()->get("vote-key");
            $senderName = $sender->getName();

            $voteAsync = new VoteAsync(function (VoteAsync $a) use ($key, $senderName){
                $get = Internet::getURL("https://minecraftpocket-servers.com/api/?object=votes&element=claim&key=" . $key . "&username=" . $senderName);
                $a->setResult($get->getBody());
            }, function (VoteAsync $a) use ($senderName, $key){
                if ($p = Server::getInstance()->getPlayerExact($senderName)){
                    switch ($a->getResult()){
                        case "0":
                            $p->sendMessage(Main::getInstance()->getConfig()->get("you-must-vote"));
                        break;

                        case "1":
                            $v = new VoteAsync(function (VoteAsync $a) use ($key, $senderName){
                                $get = Internet::getURL("https://minecraftpocket-servers.com/api/?action=post&object=votes&element=claim&key=" . $key . "&username=". $senderName);
                                $a->setResult($get->getBody());
                            }, function (VoteAsync $a) use ($senderName){
                                if ($p = Server::getInstance()->getPlayerExact($senderName)) {
                                    Main::getInstance()->loot($p);
                                    $p->getServer()->broadcastMessage(str_replace("{player}", $senderName, Main::getInstance()->getConfig()->get("broadcast-vote")));
                                }
                            });

                            Server::getInstance()->getAsyncPool()->submitTask($v);
                        break;

                        case "2":
                            $p->sendMessage(Main::getInstance()->getConfig()->get("already-vote"));
                        break;
                    }
                }
            });

            Server::getInstance()->getAsyncPool()->submitTask($voteAsync);
        }
    }
}