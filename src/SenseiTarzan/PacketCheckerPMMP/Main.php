<?php

namespace SenseiTarzan\PacketCheckerPMMP;

use pocketmine\plugin\PluginBase;
use SenseiTarzan\ExtraEvent\Component\EventLoader;
use SenseiTarzan\PacketCheckerPMMP\Listener\NetworkListener;

class Main extends PluginBase
{

    public function onLoad(): void
    {
        $this->getLogger()->info("PacketCheckerPMMP loading");
    }

    protected function onEnable() : void{
        $this->getLogger()->info("PacketCheckerPMMP enabled");
        EventLoader::loadEventWithClass($this, NetworkListener::class);
    }

    protected function onDisable() : void{
        $this->getLogger()->info("PacketCheckerPMMP disabled");
    }
}