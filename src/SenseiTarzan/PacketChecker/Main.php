<?php

/*
 *
 *            _____ _____         _      ______          _____  _   _ _____ _   _  _____
 *      /\   |_   _|  __ \       | |    |  ____|   /\   |  __ \| \ | |_   _| \ | |/ ____|
 *     /  \    | | | |  | |______| |    | |__     /  \  | |__) |  \| | | | |  \| | |  __
 *    / /\ \   | | | |  | |______| |    |  __|   / /\ \ |  _  /| . ` | | | | . ` | | |_ |
 *   / ____ \ _| |_| |__| |      | |____| |____ / ____ \| | \ \| |\  |_| |_| |\  | |__| |
 *  /_/    \_\_____|_____/       |______|______/_/    \_\_|  \_\_| \_|_____|_| \_|\_____|
 *
 * Copyright (c) 2026 Sensei Tarzan, Winheberg
 *
 * This work is licensed under the Creative Commons Attribution-NonCommercial 4.0
 * International License (CC BY-NC 4.0).
 * https://creativecommons.org/licenses/by-nc/4.0/
 *
 * @authors AID-LEARNING x Winheberg
 * @link https://github.com/AID-LEARNING
 * @link https://github.com/Winheberg
 *
 */

declare(strict_types=1);

namespace SenseiTarzan\PacketChecker;

use pocketmine\plugin\PluginBase;
use SenseiTarzan\ExtraEvent\Component\EventLoader;
use SenseiTarzan\PacketChecker\Listener\NetworkListener;

final class Main extends PluginBase
{

	public function onLoad() : void
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
