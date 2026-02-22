<?php

namespace SenseiTarzan\PacketCheckerPMMP\Utils;

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