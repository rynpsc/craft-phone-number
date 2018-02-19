<?php
/**
 * @link https://www.ryanpascoe.co
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\models;

use rynpsc\phonenumber\validators\PhoneNumberValidator;

use Craft;
use craft\base\Model;
use craft\helpers\Html;
use craft\helpers\Template;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

/**
 * Phone Number Model
 *
 * @author Ryan Pascoe
 * @package Phone Number
 * @since 1.0
 */
class PhoneNumberModel extends Model implements \JsonSerializable
{
    /**
     * @var string
     */
    public $number;

    /**
     * @var string
     */
    public $region;

    /**
     * @var PhoneNumber
     */
    private $phoneNumberObject;

    /**
     * @var PhoneNumberUtil
     */
    private $phoneNumberUtil;

    /**
     * @inheritdoc
     */
    public function __construct($number, $region, array $config = [])
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();

        $this->number = $number;
        $this->region = $region;

        try {
            $this->phoneNumberObject = $this->phoneNumberUtil->parse($number, $region);
        } catch(\Exception $e) {
            // Continue
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return (string)$this->number;
    }

    /**
     * Formats a phone number in the specified format
     *
     * @return string
     */
    public function format(string $format = null)
    {
        $formats = [
            'e164' => PhoneNumberFormat::E164,
            'international' => PhoneNumberFormat::INTERNATIONAL,
            'national' => PhoneNumberFormat::NATIONAL,
            'rfc3966' => PhoneNumberFormat::RFC3966,
        ];

        $format = strtolower($format);

        if (array_key_exists($format, $formats)) {
            return $this->phoneNumberUtil->format($this->phoneNumberObject, $formats[$format]);
        }

        return $this->number;
    }

    /**
     * Returns the country code
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->phoneNumberObject->getCountryCode();
    }

    /**
     * Returns the region code
     *
     * @return string
     */
    public function getRegionCode()
    {
        return $this->phoneNumberUtil->getRegionCodeForNumber($this->phoneNumberObject);
    }

    /**
     * Returns an anchor prefilled with the URL
     *
     * @param array $attributes Attributes to apply to anchor
     * @return \Twig_Markup
     */
    public function getLink($attributes = [])
    {
        if (is_null($this->phoneNumberObject)) {
            return null;
        }

        $href = $this->format('rfc3966');
        $text = Html::encode($this->__toString());
        $link = Html::a($text, $href, $attributes);

        return Template::raw($link);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['number'], PhoneNumberValidator::class],
        ];
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'number' => $this->number,
            'region' => $this->region,
        ];
    }
}
