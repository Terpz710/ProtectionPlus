<?php

declare(strict_types=1);

namespace Terpz710\ProtectionPlus\Command;

use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

class PvPCommand extends Command implements Listener {

    private $damageEnabled = true;

    public function __construct(PluginBase $plugin) {
        parent::__construct("pvp", "Toggle PvP/Damage Protection");
        $this->setPermission("protectionplus.damage");
        $plugin->getServer()->getCommandMap()->register($this->getName(), $this);
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
                        $this->damageEnabled = false;
                        $sender->sendMessage("§ePvP/Damage§r protection is now §aenabled§r!");
                        break;
                    case "off":
                        $this->damageEnabled = true;
                        $sender->sendMessage("§ePvP/Damage§r is now §4disabled§r!");
                        break;
                    default:
                        $sender->sendMessage("Usage: /pvp <on|off>");
                        return false;
                }
            } else {
                $sender->sendMessage("Usage: /pvp <on|off>");
            }
        } else {
            $sender->sendMessage("This command can only be used in-game");
        }
        return true;
    }

    public function onEntityDamage(EntityDamageEvent $event): void {
        if ($event->getEntity() instanceof Player && !$this->damageEnabled) {
            $event->cancel();
        }
        
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if ($damager instanceof Player && !$this->damageEnabled) {
                $damager->sendMessage("§4PvP§r is §4not allowed§r here!");
                $damager->sendTitle("§4§lERROR");
                $event->cancel();
            }
        }
    }
}
