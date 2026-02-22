<?php

namespace SenseiTarzan\PacketChecker\Utils;

use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\login\clientdata\ClientData;
use pocketmine\network\PacketHandlingException;

final class LoginPacketDecode
{
    /**
     * @throws PacketHandlingException
     */
    public static function parseClientData(
        NetworkSession $session,
        string $clientDataJwt) : ClientData{
        try{
            [, $clientDataClaims, ] = JwtUtils::parse($clientDataJwt);
        }catch(JwtException $e){
            throw PacketHandlingException::wrap($e);
        }

        $mapper = ParseJsonMapper::defaultJsonMapper($session, "ClientData JWT body");
        try{
            $clientData = $mapper->map($clientDataClaims, new ClientData());
        }catch(\JsonMapper_Exception $e){
            throw PacketHandlingException::wrap($e);
        }
        return $clientData;
    }
}