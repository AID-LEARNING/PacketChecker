<?php

namespace SenseiTarzan\PacketChecker;

use pocketmine\plugin\PluginBase;
use SenseiTarzan\ExtraEvent\Component\EventLoader;
use SenseiTarzan\PacketChecker\Listener\NetworkListener;

final class Main extends PluginBase
{

    public function onLoad(): void
    {
        $this->getLogger()->info("PacketChecker powered by SenseiTarzan x Winheberg is loading...");
    }

    protected function onEnable() : void{
        $this->getLogger()->info("PacketChecker powered by SenseiTarzan x Winheberg is enabled");
        EventLoader::loadEventWithClass($this, NetworkListener::class);
    }

    protected function onDisable() : void{
        $this->getLogger()->info("PacketChecker powered by SenseiTarzan x Winheberg is disabled");
    }
}