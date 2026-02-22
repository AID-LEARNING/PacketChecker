<?php

namespace SenseiTarzan\PacketCheckerPMMP\Utils;

use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\types\login\AuthenticationInfo;
use pocketmine\network\mcpe\protocol\types\login\AuthenticationType;
use pocketmine\network\mcpe\protocol\types\login\legacy\LegacyAuthChain;
use pocketmine\network\mcpe\protocol\types\login\legacy\LegacyAuthIdentityData;
use pocketmine\network\PacketHandlingException;
use Ramsey\Uuid\Uuid;

class ParseJsonMapper
{

    public static function defaultJsonMapper(
        NetworkSession $session,
        string $logContext) : \JsonMapper{
        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $mapper->undefinedPropertyHandler = self::warnUndefinedJsonPropertyHandler($session, $logContext);
        $mapper->bStrictObjectTypeChecking = true;
        $mapper->bEnforceMapType = false;
        return $mapper;
    }

    /**
     * @phpstan-return \Closure(object, string, mixed) : void
     */
    private static function warnUndefinedJsonPropertyHandler(
        NetworkSession $session,
        string $context
    ) : \Closure{
        return fn(object $object, string $name, mixed $value) => $session->getLogger()->warning(
            "$context: Unexpected JSON property for " . (new \ReflectionClass($object))->getShortName() . ": " . $name . " = " . var_export($value, return: true)
        );
    }
}