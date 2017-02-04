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

use pocketmine\entity\Entity;

use pocketmine\Player;



use pocketmine\event\player\PlayerToggleSneakEvent;

use pocketmine\event\player\PlayerMoveEvent;






class Main extends PluginBase implements Listener {



    protected $elevator;
    protected $boost;
    protected $jump;
    public $cancelUpAfterDown = [];




   public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        // Loading from config
        $this->elevator = Item::fromString($this->getConfig()->get("ElevatorBlock"));
        if(!($this->elevator instanceof ItemBlock)) {
            $this->getLogger()->warning("Invalid block provided as elevator.");
            $this->elevator = Item::get(139);
        }
        $this->boost = Item::fromString($this->getConfig()->get("BoostBlock"));
        if(!($this->elevator instanceof ItemBlock)) {
            $this->getLogger()->warning("Invalid block provided as boost block.");
            $this->elevator = Item::get(79);
        }
        $this->jump = Item::fromString($this->getConfig()->get("JumpBlock"));
        if(!($this->elevator instanceof ItemBlock)) {
            $this->getLogger()->warning("Invalid block provided as jump block.");
            $this->elevator = Item::get(170);
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
        $b = $event->getPlayer()->getLevel()->getBlock(new \pocketmine\math\Vector3(round($event->getPlayer()->x), floor($event->getPlayer()->y - 1), round($event->getPlayer()->z)));
        if($b->getId() == $this->elevator->getId() && $b->getDamage() == $this->elevator->getDamage() && $event->isSneaking()) { // Checking if the player is sneaking on the block set as elevator.
            $bl = $this->getBlockUnder($b);
            if(!is_null($bl) && !$this->hasFreeSpace($bl)) {
                while(!is_null($bl) && !$this->hasFreeSpace($bl)) {
                    $bl = $this->getBlockUnder($bl);
                }
            }
            if(!is_null($bl)) { // A elevator under exists
                $event->getPlayer()->teleport(new \pocketmine\math\Vector3($bl->x, $bl->y + 1.5, $bl->z));
                $this->setCooldown($event->getPlayer());
                $event->getPlayer()->getLevel()->addSound(new \pocketmine\level\sound\EndermanTeleportSound($event->getPlayer()));
            }
        }
    }

    /*
    Check when a player jumps
    @param     $event   \pocketmine\event\player\PlayerMoveEvent
    @return void
    */
    public function onPlayerMove(PlayerMoveEvent $event) {
        $b = $event->getPlayer()->getLevel()->getBlock(new \pocketmine\math\Vector3(round($event->getFrom()->x), floor($event->getFrom()->y - 1), round($event->getFrom()->z)));
        if($b->getId() == $this->elevator->getId() && $b->getDamage() == $this->elevator->getDamage() && $event->getTo()->y > $event->getFrom()->y) { // Checking if the player is sneaking on the block set as elevator.
            // $this->getLogger()->debug($event->getFrom() . "/" . $event->getTo());
            $bl = $this->getBlockAbove($b);
            if(!is_null($bl) && !$this->hasFreeSpace($bl)) {
                while(!is_null($bl) && !$this->hasFreeSpace($bl)) {
                    $bl = $this->getBlockAbove($bl);
                }
            }
            if(!is_null($bl) && !isset($this->cancelUpAfterDown[$event->getPlayer()->getName()])) { // A elevator above exists
                // $this->getLogger()->debug("Up" . $bl->y);
                $event->setTo(new \pocketmine\level\Location($bl->x, $bl->y + 1.5, $bl->z));
                $this->setCooldown($event->getPlayer());
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
            if($block->getId() == $b->getId() && $block->getDamage() == $block->getDamage()) return $block;
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
            if($block->getId() == $b->getId() && $block->getDamage() == $block->getDamage()) return $block;
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
    @return \pocketmine\block\Block
    */
    public function getElevatorBlock() : \pocketmine\block\Block {
        return clone $this->elevator->getBlock();
    }


    /*
    Return a clone of the boosting block
    @return \pocketmine\block\Block
    */
    public function getBoostBlock() : \pocketmine\block\Block {
        return clone $this->boost->getBlock();
    }


    /*
    Return a clone of the jumpig block block
    @return \pocketmine\block\Block
    */
    public function getJumpBlock() : \pocketmine\block\Block {
        return clone $this->jump->getBlock();
    }



    /*
    Sets a cooldown of using the up & down blocks  to a player
    @param     $player    Player
    @return void
    */
    public function setCooldown(Player $player) {
        $this->cancelUpAfterDown[$player->getName()] = true;
        $this->getServer()->getScheduler()->scheduleDelayedTask(new tasks\UpCooldownTask($this, $player), 20);
    }


}