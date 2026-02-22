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

final class TextPacketDecode
{
	public const CATEGORY_MESSAGE_ONLY = 0;
	public const CATEGORY_AUTHORED_MESSAGE = 1;
	public const CATEGORY_MESSAGE_WITH_PARAMETERS = 2;
}
