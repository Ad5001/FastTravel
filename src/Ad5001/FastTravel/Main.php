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

namespace Ad5001\FastTravel;


use pocketmine\item\Item;

use pocketmine\item\ItemBlock;

use pocketmine\block\Block;

use pocketmine\event\Listener;

use pocketmine\plugin\PluginBase;

use pocketmine\Server;

use pocketmine\Player;



use pocketmine\event\player\PlayerToggleSneakEvent;

use pocketmine\event\player\PlayerMoveEvent;






class Main extends PluginBase implements Listener {



    protected $elevator;
    protected $speed;




   public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        // Loading from config
        $this->elevator = Item::fromString($this->getConfig()->get("ElevatorBlock"));
        if(!($this->elevator instanceof ItemBlock)) {
            $this->getLogger()->warning("Invalid block provided as elevator.");
            $this->elevator = Item::get(139);
        }
        $this->speed = Item::fromString($this->getConfig()->get("SpeedBlock"));
        if(!($this->elevator instanceof ItemBlock)) {
            $this->getLogger()->warning("Invalid block provided as speed block.");
            $this->elevator = Item::get(79);
        }

        $this->getServer()->getScheduler()->scheduleRepeatingTask(new tasks\FastingTask($this), 5);
    }




    public function onLoad(){
        $this->saveDefaultConfig();
    }



    /*
    Tests when a player sneaks
    @param     $event   \pocketmine\event\player\PlayerToggleSneakEvent
    @return void
    */
    public function onPlayerSneak(PlayerToggleSneakEvent $event) {
        $b = $event->getPlayer()->getLevel()->getBlock(new \pocketmine\math\Vector3(round($player->x), round($player->y - 1), $player->z));
        if($b->getId() == $this->elevator->getId() && $b->getDamage() == $this->elevator->getDamage() && $event->isSneaking()) { // Checking if the player is sneaking on the block set as elevator.
            $bl = $this->getBlocksUnder($b);
            if(!is_null($bl) && !$this->hasFreeSpace($bl)) {
                while(!is_null($bl) && !$this->hasFreeSpace($bl)) {
                    $bl = $this->getBlocksUnder($bl);
                }
            }
            if(!is_null($bl)) { // A elevator under exists
                $event->getPlayer()->teleport(new \pocketmine\math\Vector3($bl->x, $bl->y + 1, $bl->z));
                $event->getPlayer()->getLevel()->addSound(new \pocketmine\level\sound\EndermanTeleportSound($event->getPlayer()));
            }
            $event->setCancelled();
        }
    }

    /*
    Check when a player jumps
    @param     $event   \pocketmine\event\player\PlayerMoveEvent
    @return void
    */
    public function onPlayerMove(PlayerMoveEvent $event) {
        $b = $event->getPlayer()->getLevel()->getBlock(new \pocketmine\math\Vector3(round($player->x), round($player->y - 1), $player->z));
        if($b->getId() == $this->elevator->getId() && $b->getDamage() == $this->elevator->getDamage() && $event->getTo()->y > $event->getFrom()->y) { // Checking if the player is sneaking on the block set as elevator.
            $bl = $this->getBlocksAbove($b);
            if(!is_null($bl) && !$this->hasFreeSpace($bl)) {
                while(!is_null($bl) && !$this->hasFreeSpace($bl)) {
                    $bl = $this->getBlocksAbove($bl);
                }
            }
            if(!is_null($bl)) { // A elevator above exists
                $event->setTo(new \pocketmine\math\Vector3($bl->x, $bl->y + 1, $bl->z));
                $event->getPlayer()->getLevel()->addSound(new \pocketmine\level\sound\EndermanTeleportSound($event->getPlayer()));
            }
        }
    }

/*
     _      ____    ___ 
    / \    |  _ \  |_ _|
   / _ \   | |_) |  | | 
  / ___ \  |  __/   | | 
 /_/   \_\ |_|     |___|
                        
*/



    /*
    Get blocks under the block provided that have the same id and damage.
    @param     $block    pocketmine\block\Block
    @return     pocketmine\block\Block|null
    */
    public function getBlockUnder(\pocketmine\block\Block $b) {
        for($i = $b->y - 2/* TP atleast under two blocks of the current one*/; $i  > 0; $i--) {
            $block = $b->getLevel()->getBlock(new \pocketmine\math\Vector3($b->x, $i, $b->z));
            if($block->geId() == $b->getId() && $block->getDamage() == $block->getDamage()) return $block;
        }
        return null;
    }



    /*
    Get the first block above the block provided that have the same id and damage.
    @param     $block    pocketmine\block\Block
    @return     pocketmine\block\Block|null
    */
    public function getBlockAbove(\pocketmine\block\Block $b) {
        for($i = $b->y + 2/* TP atleast above two blocks of the current one*/; $i < 128; $i++) {
            $block = $b->getLevel()->getBlock(new \pocketmine\math\Vector3($b->x, $i, $b->z));
            if($block->geId() == $b->getId() && $block->getDamage() == $block->getDamage()) return $block;
        }
        return null;
    }



    /*
    Check if the space above a the provided block has transparent block so the player won't die of suffocation'
    @param     $block    pocketmine\block\Block
    @return     bool
    */
    public function hasFreeSpace(\pocketmine\block\Block $b) : bool {
        $b1 = $b->getLevel()->getBlock(new \pocketmine\math\Vector3($b->x, $b->y + 1, $b->z));
        $b2 = $b->getLevel()->getBlock(new \pocketmine\math\Vector3($b->x, $b->y + 2, $b->z));
        return $b1->isTransparent() && $b2->isTransparent();
    }


    /*
    Return a clone of the elevator block
    @param       
    @return \pocketmine\block\Block
    */
    public function getElevatorBlock() : \pocketmine\block\Block {
        return clone $this->elevator;
    }


    /*
    Return a clone of the speeding block
    @param       
    @return \pocketmine\block\Block
    */
    public function getSpeed() : \pocketmine\block\Block {
        return clone $this->speed;
    }


}