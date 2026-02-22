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
