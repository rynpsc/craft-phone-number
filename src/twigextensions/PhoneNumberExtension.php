<?php
/**
 * @link https://www.ryanpascoe.co
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\twigextensions;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Html;
use craft\helpers\Template;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use rynpsc\phonenumber\helpers\StringHelper;
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
        if ($string == null) {
            return null;
        }

        $offset = 0;
        $charset = Craft::$app->charset;

        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        $phoneNumberMatches = $phoneNumberUtil->findNumbers($string, $region);

        foreach ($phoneNumberMatches as $phoneNumberMatch) {
            $attrs = $attributes;
            $textLength = mb_strlen($string, $charset);

            $start = $phoneNumberMatch->start();
            $length = mb_strlen($phoneNumberMatch->rawString(), $charset);

            $html = ArrayHelper::remove($attrs, 'html');
            $text = ArrayHelper::remove($attrs, 'text', $phoneNumberMatch->rawString());

            $content = $html ?? Html::encode($text);

            $attrs['href'] = $phoneNumberUtil->format($phoneNumberMatch->number(), PhoneNumberFormat::RFC3966);

            $link = Html::tag('a', $content, $attrs);

            // Replace number with anchor
            $string = StringHelper::substrReplace($string, $link, ($start + $offset), $length);

            // Account for new string length
            $offset += mb_strlen($string, $charset) - $textLength;
        }

        return Template::raw($string);
    }
}
