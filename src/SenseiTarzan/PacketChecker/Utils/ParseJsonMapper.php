<?php

namespace SenseiTarzan\PacketChecker\Utils;

use pocketmine\network\mcpe\NetworkSession;

final class ParseJsonMapper
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