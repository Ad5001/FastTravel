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

use pocketmine\scheduler\PluginTask;

use pocketmine\Player;

use Ad5001\FastTravel\Main;




class FastingTask extends PluginTask {




   public function __construct(Main $main) {

        parent::__construct($main);
        $this->main = $main;
        $this->server = $main->getServer();
        $this->boostPlayers = [];

    }




    public function onRun($tick) {
        foreach($this->server->getOnlinePlayers() as $p) {
            $r = $p->round();
            $r->y = $p->getFloorY() - 1;
            if($p->getLevel()->getBlock($r)->getId() == $this->main->getBoostBlock()->getId() && $p->getLevel()->getBlock($r)->getDamage() == $this->main->getBoostBlock()->getDamage()) {
                $attr = $p->getAttributeMap()->getAttribute(\pocketmine\entity\Attribute::MOVEMENT_SPEED);
			    $attr->setValue(0.11 * $this->main->getConfig()->get("BoostAmplifier"));
                $this->boostPlayers[$p->getName()] = 8; // 2 seconds.
            } elseif(isset($this->boostPlayers[$p->getName()])) {
                $this->boostPlayers[$p->getName()]--;
                if($this->boostPlayers[$p->getName()] <= 0) {
                    $attr = $p->getAttributeMap()->getAttribute(\pocketmine\entity\Attribute::MOVEMENT_SPEED);
			        $attr->setValue(0.11);
                }
            }
            if($p->getLevel()->getBlock($r)->getId() == $this->main->getJumpBlock()->getId() && $p->getLevel()->getBlock($r)->getDamage() == $this->main->getJumpBlock()->getDamage()) {
                $p->setMotion(new \pocketmine\math\Vector3($p->getMotion()->x, $this->main->getConfig()->get("JumpAmplifier"), $p->getMotion()->x));
            }
        }
    }




}