<?php
/**
 * @link https://www.ryanpascoe.co
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\twigextensions;

use rynpsc\phonenumber\models\PhoneNumberModel;
use rynpsc\phonenumber\helpers\StringHelper;

use Craft;
use craft\helpers\ArrayHelper;
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
        if ($string == null) {
            return null;
        }

        $offset = 0;
        $charset = Craft::$app->charset;

        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        $phoneNumberMatches = $phoneNumberUtil->findNumbers($string, $region);

        foreach ($phoneNumberMatches as $phoneNumberMatch) {
            $textLength = mb_strlen($string, $charset);

            $start = $phoneNumberMatch->start();
            $length = mb_strlen($phoneNumberMatch->rawString(), $charset);

            $raw = $phoneNumberMatch->rawString();
            $number = $phoneNumberMatch->number();

            // Create the anchor
            $href = $phoneNumberUtil->format($number, PhoneNumberFormat::RFC3966);
            $link = Html::tag('a', $raw, ArrayHelper::merge($attributes,
                ['href' => $phoneNumberUtil->format($number, PhoneNumberFormat::RFC3966)]
            ));

            // Replace number with anchor
            $string = StringHelper::substrReplace($string, $link, ($start + $offset), $length);

            // Account for new string length
            $offset += mb_strlen($string, $charset) - $textLength;
        }

        return Template::raw($string);
    }
}
