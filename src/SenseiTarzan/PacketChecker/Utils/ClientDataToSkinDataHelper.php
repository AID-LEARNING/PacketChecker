<?php

namespace SenseiTarzan\PacketChecker\Utils;

use pocketmine\network\mcpe\protocol\PacketDecodeException;

final class ClientDataToSkinDataHelper
{
    /**
     * @throws PacketDecodeException
     */
    public static function safeB64Decode(string $base64, string $context) : string{
        $result = base64_decode($base64, true);
        if($result === false){
            throw new PacketDecodeException("$context: Malformed base64, cannot be decoded");
        }
        return $result;
    }
}