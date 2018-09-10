<?php
/**
 * @link https://www.ryanpascoe.co
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\twigextensions;

use rynpsc\phonenumber\models\PhoneNumberModel;

use Craft;
use craft\helpers\Html;
use craft\helpers\Template;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

/**
 * Phone Number Twig extension
 *
 * @author Ryan Pascoe
 * @package Phone Number
 * @since 1.0
 */
class PhoneNumberExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'Phone Number';
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('tel', [$this, 'findNumbersFilter']),
        ];
    }

    /**
     * Parses a string to automatically add anchors to phone numbers
     *
     * @param string $string The string to parse
     * @param string $region The default region to use when matching
     * @param array $attributes Attributes to set on anchor
     * @return \Twig_Markup
     */
    public function findNumbersFilter(string $string = null, string $region = null, array $attributes = [])
    {
        $offset = 0;

        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        $phoneNumberMatcher = $phoneNumberUtil->findNumbers($string, $region);

        foreach ($phoneNumberMatcher as $phoneNumberMatch) {
            $textLength = strlen($string);

            $start = $phoneNumberMatch->start();
            $length = strlen($phoneNumberMatch->rawString());

            $raw = $phoneNumberMatch->rawString();
            $number = $phoneNumberMatch->number();

            // Create the anchor
            $href = $phoneNumberUtil->format($number, PhoneNumberFormat::RFC3966);
            $link = Html::a($raw, $href, $attributes);

            // Replace number with anchor
            $string = substr_replace($string, $link, ($start + $offset), $length);

            // Account for new string length
            $offset += strlen($string) - $textLength;
        }

        return Template::raw($string);
    }
}
