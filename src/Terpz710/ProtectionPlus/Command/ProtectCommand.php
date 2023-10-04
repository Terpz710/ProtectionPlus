<?php

declare(strict_types=1);

namespace Terpz710\ProtectionPlus\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\block\Block;

class ProtectCommand extends Command implements Listener {

    private $protectionActive = [];

    public function __construct(PluginBase $plugin) {
        parent::__construct("protection", "Toggle Block Protection");
        $this->setPermission("protectionplus.protect");
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    public function execute(CommandSender $sender, string $label, array $args): bool {
        if ($sender instanceof Player) {
            if (!$this->testPermission($sender)) {
                $sender->sendMessage("§cYou do not have permission to use this command");
                return true;
            }

            $world = $sender->getWorld()->getFolderName();
            $action = strtolower($args[0] ?? "");

            switch ($action) {
                case "on":
                    $this->protectionActive[$world] = true;
                    $sender->sendMessage("§eBlock Protection§r is now §aenabled§r!");
                    break;
                case "off":
                    unset($this->protectionActive[$world]);
                    $sender->sendMessage("§eBlock Protection§r is now §4disabled§r!");
                    break;
                default:
                    $sender->sendMessage("Usage: /protection <on|off>");
            }
        } else {
            $sender->sendMessage("This command can only be used in-game");
        }
        return true;
    }

    /**
     * @param BlockBreakEvent $event
     * @priority HIGHEST
     */
    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $world = $player->getWorld()->getFolderName();
        if (isset($this->protectionActive[$world])) {
            $player->sendMessage("§4Breaking§r blocks is §4not allowed§r here!");
            $event->cancel();
        }
        $this->handleBlockAction($event, $player);
    }

    /**
     * @param BlockPlaceEvent $event
     * @priority HIGHEST
     */
    public function onBlockPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $world = $player->getWorld()->getFolderName();
        if (isset($this->protectionActive[$world])) {
            $player->sendMessage("§4Placing§r blocks is §4not allowed§r here!");
            $event->cancel();
        }
        $this->handleBlockAction($event, $player);
    }

    /**
     * @param PlayerBucketEmptyEvent $event
     * @priority HIGHEST
     */
    public function onPlayerEmptyBucket(PlayerBucketEmptyEvent $event): void {
        $player = $event->getPlayer();
        $world = $player->getWorld()->getFolderName();
        if (isset($this->protectionActive[$world])) {
            $player->sendMessage("Using §4buckets§r is §4not allowed§r here!");
            $event->cancel();
        }
    }

    /**
     * @param PlayerBucketFillEvent $event
     * @priority HIGHEST
     */
    public function onPlayerFillBucket(PlayerBucketFillEvent $event): void {
        $player = $event->getPlayer();
        $world = $player->getWorld()->getFolderName();
        if (isset($this->protectionActive[$world])) {
            $player->sendMessage("Using §4buckets§r is §4not allowed§r here!");
            $event->cancel();
        }
    }

    /**
     * @param PlayerDropItemEvent $event
     * @priority HIGHEST
     */
    public function onPlayerDropItem(PlayerDropItemEvent $event): void {
        $player = $event->getPlayer();
        $world = $player->getWorld()->getFolderName();
        if (isset($this->protectionActive[$world])) {
            $player->sendMessage("Dropping §4items§r is §4not allowed§r here!");
            $event->cancel();
        }
    }

    /**
     * @param PlayerMoveEvent $event
     * @priority HIGHEST
     */
    public function onPlayerMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $to = $event->getTo();
        $world = $player->getWorld()->getFolderName();

        if (isset($this->protectionActive[$world])) {
            $x = (int) $to->x;
            $y = (int) $to->y;
            $z = (int) $to->z;
            $block = $player->getWorld()->getBlockAt($x, $y - 1, $z);

            if (in_array($block->getId(), [Block::WHEAT_BLOCK, Block::CARROT_BLOCK, Block::POTATO_BLOCK])) {
                $player->sendMessage("Jumping on crops is not allowed here!");
                $event->cancel();
            }
        }
    }

    /**
     * Handle block action and send a message to the player.
     *
     * @param $event
     * @param Player $player
     */
    private function handleBlockAction($event, Player $player): void {
        if (isset($this->protectionActive[$player->getWorld()->getFolderName()])) {
            if ($event->isCancelled()) return;
            $event->cancel();
        }
    }
}
