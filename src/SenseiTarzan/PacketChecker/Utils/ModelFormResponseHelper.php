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

use pocketmine\form\Form;
use function ceil;

final class ModelFormResponseHelper
{
	public const MARGIN_LARGE_FORM = 1.5;

	public static function predictLargeSizeInOctetResponse(Form $form) : int{
		$json = $form->jsonSerialize();
		$typeForm = $json["type"] ?? null;
		return match ($typeForm) {
			"form" => 20,
			"custom_form" => self::predictLargeSizeResponseCustomForm($json),
			default => 5,
		};
	}

	public static  function predictLargeSizeResponseCustomForm(array $json) : int
	{
		$result = 1;
		$content = $json["content"] ?? [];
		foreach ($content as $element) {
			$type = $element["type"] ?? null;
			switch ($type) {
				case "input": {
					$result += 512; //512 for input text
					break;
				}
				case "step_slider": {
					$result += 12; // size of int
					break;
				}
				case "slider": {
					$result += 24; // size of long
					break;
				}
				case "label":
				case "divider":
				case "header": {
					$result += 4; // size of null
					break;
				}
				case "toggle": {
					$result += 1; // size of char, as they don't have a response content, just a boolean or no content at all
					break;
				}
				case "dropdown": {
					$result += 20; // size of long, as the response is the index of the selected option
					break;
				}
			}
		}
		return (int) ceil($result * self::MARGIN_LARGE_FORM);
	}
}
