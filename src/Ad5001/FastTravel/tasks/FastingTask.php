<?php

/*
  _____                 _     _____                                 _ 
 |  ___|   __ _   ___  | |_  |_   _|  _ __    __ _  __   __   ___  | |
 | |_     / _` | / __| | __|   | |   | '__|  / _` | \ \ / /  / _ \ | |
 |  _|   | (_| | \__ \ | |_    | |   | |    | (_| |  \ V /  |  __/ | |
 |_|      \__,_| |___/  \__|   |_|   |_|     \__,_|   \_/    \___| |_|

Travel in MCPE faster and easier than you ever tought !

@author Ad5001
@link http://github.com/Ad5001/FastTravel
*/

namespace Ad5001\FastTravel\tasks;


use pocketmine\Server;

use pocketmine\schedulerPluginTask;

use pocketmine\Player;

use Ad5001\FastTravel\Main;




class FastingTask extends PluginTask {




   public function __construct(Main $main) {

        parent::__construct($main);
        $this->main = $main;
        $this->server = $main->getServer();

    }




    public function onRun($tick) {
        foreach($this->server->getPlayers() as $p) {

        }
    }




}