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

namespace SenseiTarzan\PacketChecker\Utils;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\VarInt;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PacketDecodeException;

final class HeaderPacketDecode
{

	public static function SkipHeader(
		ByteBufferReader $buffer,
		int $expectedNetworkId
	)
	{
		$header = VarInt::readUnsignedInt($buffer);
		$pid = $header & DataPacket::PID_MASK;
		if($pid !== $expectedNetworkId){
			//TODO: this means a logical error in the code, but how to prevent it from happening?
			throw new PacketDecodeException("Expected " . $expectedNetworkId . " for packet ID, got $pid");
		}
	}
}
