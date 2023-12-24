<?php
/**
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\helpers;

/**
 * String helper class.
 *
 * @author Ryan Pascoe
 * @package Phone Number
 * @since 1.2.2
 */
class StringHelper extends \craft\helpers\StringHelper
{
	/**
	 * Multibyte safe sub string replacement.
	 *
	 * @param string $string The string to replace
	 * @param string $replacement The replacement value
	 * @param int $start
	 * @param int $length
	 * @return string
	 */
	public static function substrReplace(string $string, string $replacement, int $start, int $length): string
	{
		return mb_substr($string, 0, $start) . $replacement . mb_substr($string, $start + $length);
	}
}
