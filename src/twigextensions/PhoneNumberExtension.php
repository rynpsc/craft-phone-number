<?php
/**
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\twigextensions;

use rynpsc\phonenumber\PhoneNumber;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFilter;

/**
 * Phone Number Twig extension
 *
 * @author Ryan Pascoe
 * @package Phone Number
 * @since 1.0
 */
class PhoneNumberExtension extends AbstractExtension
{
	/**
	 * Returns the name of the extension
	 *
	 * @return string The extension name
	 */
	public function getName(): string
	{
		return 'Phone Number';
	}

	/**
	 * @inheritdoc
	 */
	public function getFilters(): array
	{
		return [
			new TwigFilter('tel', [$this, 'findNumbersFilter']),
		];
	}

	/**
	 * Parses a string to automatically add anchors to phone numbers
	 *
	 * @param string|null $string $string The string to parse
	 * @param string|null $region The default region to use when matching
	 * @param array $attributes Attributes to set on anchor
	 */
	public function findNumbersFilter(string $string = null, string $region = null, array $attributes = []): ?Markup
	{
		return PhoneNumber::getInstance()->getPhoneNumber()->convertNumbersToLinks($string, $region, $attributes);
	}
}
