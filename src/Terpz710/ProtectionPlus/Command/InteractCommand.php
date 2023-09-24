<?php

declare(strict_types=1);

namespace Terpz710\ProtectionPlus\Command;

use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\BaseInventory;
use pocketmine\inventory\PlayerCraftingInventory;
use pocketmine\inventory\CreativeInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\FlintSteel;
use pocketmine\plugin\PluginBase;

class InteractCommand extends Command implements Listener {

    private $interactionEnabled = true;

    public function __construct(PluginBase $plugin) {
        parent::__construct("interaction", "Toggle Block Interaction");
        $this->setPermission("protectionplus.interaction");
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    public function execute(CommandSender $sender, string $label, array $args): bool {
        if ($sender instanceof Player) {
            if (!$this->testPermission($sender)) {
                $sender->sendMessage("§cYou do not have permission to use this command");
                return true;
            }

            if (isset($args[0])) {
                $action = strtolower($args[0]);
                switch ($action) {
                    case "on":
                        $this->interactionEnabled = false;
                        $sender->sendMessage("§eBlock Interaction§r is now §aenabled§r!");
                        break;
                    case "off":
                        $this->interactionEnabled = true;
                        $sender->sendMessage("§eBlock Interaction§r is now §4disabled§r!");
                        break;
                    default:
                        $sender->sendMessage("Usage: /interaction <on|off>");
                        return false;
                }
            } else {
                $sender->sendMessage("Usage: /interaction <on|off>");
            }
        } else {
            $sender->sendMessage("This command can only be used in-game");
        }
        return true;
    }

    /**
     * @param InventoryOpenEvent $event
     * @priority HIGHEST
     */
    public function onInventoryOpen(InventoryOpenEvent $event): void {
        $player = $event->getPlayer();
        $inventory = $event->getInventory();

        $this->handleInteractionAction($event, $player);
    }

    /**
     * @param PlayerInteractEvent $event
     * @priority HIGHEST
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($item instanceof FlintSteel) {
            $this->handleInteractionAction($event, $player);
        }
    }

    /**
     * Handle interaction action and send a message to the player.
     *
     * @param $event
     * @param Player $player
     */
    private function handleInteractionAction($event, Player $player): void {
        if (!$this->interactionEnabled && ($event instanceof InventoryOpenEvent || $event instanceof PlayerInteractEvent)) {
            if ($event->isCancelled()) return;
            $event->cancel();
        }
    }
}
