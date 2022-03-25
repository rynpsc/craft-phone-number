<?php
/**
 * @link https://www.ryanpascoe.co
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\models;

use rynpsc\phonenumber\validators\PhoneNumberValidator;

use Craft;
use JsonSerializable;
use Twig\Markup;
use craft\base\Model;
use craft\helpers\ArrayHelper;
use craft\helpers\Html;
use craft\helpers\Template;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberToTimeZonesMapper;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;

/**
 * Phone Number Model
 *
 * @author Ryan Pascoe
 * @package Phone Number
 * @since 1.0
 */
class PhoneNumberModel extends Model implements JsonSerializable
{
    public string $number;

    public string $region;

    private ?PhoneNumber $phoneNumberObject;

    private PhoneNumberUtil $phoneNumberUtil;

    private PhoneNumberOfflineGeocoder $geoCoder;

    /**
     * @inheritdoc
     */
    public function __construct(string $number, ?string $region, array $config = [])
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
        $this->geoCoder = PhoneNumberOfflineGeocoder::getInstance();

        $this->number = $number;
        $this->region = $region;

        try {
            $this->phoneNumberObject = $this->phoneNumberUtil->parse($number, $region);
        } catch (\Exception $e) {
            // Continue
        }

        parent::__construct($config);
    }

    public function __toString()
    {
        return (string)$this->number;
    }

    /**
     * Gets the name of the carrier for the given phone number, in the language provided.
     */
    public function getCarrierName(string $locale = null): string
    {
        $mapper = PhoneNumberToCarrierMapper::getInstance();

        if (!isset($locale)) {
            $locale = Craft::$app->language;
        }

        return $mapper->getNameForNumber($this->phoneNumberObject, $locale);
    }

    /**
     * Returns the extension for this phone number.
     */
    public function getExtension(): ?string
    {
        return $this->phoneNumberObject->getExtension();
    }

    /**
     * Formats a phone number in the specified format
     *
     * @param string|null $format
     */
    public function format(string $format = null): string
    {
        $formats = [
            'e164' => PhoneNumberFormat::E164,
            'international' => PhoneNumberFormat::INTERNATIONAL,
            'national' => PhoneNumberFormat::NATIONAL,
            'rfc3966' => PhoneNumberFormat::RFC3966,
            'tel' => PhoneNumberFormat::RFC3966,
        ];

        $format = strtolower($format);

        if (array_key_exists($format, $formats)) {
            return $this->phoneNumberUtil->format($this->phoneNumberObject, $formats[$format]);
        }

        return $this->number;
    }

    /**
     * Formats a phone number for out-of-country dialing purposes.
     *
     * @param string|null $region The region where the call is being placed.
     */
    public function formatForCountry(string $region = null): string
    {
        return $this
            ->phoneNumberUtil
            ->formatOutOfCountryCallingNumber($this->phoneNumberObject, $region);
    }

    /**
     * Returns a number formatted in such a way that it can be dialed from a mobile phone in the specific region.
     *
     * @param string $region The region where the call is being placed.
     * @param bool $format Whether the number should be returned with formatting symbols, such as spaces and dashes.
     */
    public function formatForMobileDialing(string $region, bool $format = true): string
    {
        return $this
            ->phoneNumberUtil
            ->formatNumberForMobileDialing($this->phoneNumberObject, $region, $format);
    }

    /**
     * Returns the country code
     */
    public function getCountryCode(): string
    {
        return $this->phoneNumberObject->getCountryCode();
    }

    /**
     * Returns the region code
     */
    public function getRegionCode(): string
    {
        return $this->phoneNumberUtil->getRegionCodeForNumber($this->phoneNumberObject);
    }

    /**
     * Returns the type of number
     */
    public function getType(): int
    {
        return $this->phoneNumberUtil->getNumberType($this->phoneNumberObject);
    }

    /**
     * Returns the numbers' description (country or geographical area)
     */
    public function getDescription(string $locale = null, string $region = null): ?string
    {
        if (!$this->geoCoder) {
            return null;
        }

        if (!isset($locale)) {
            $locale = Craft::$app->language;
        }

        return $this->geoCoder->getDescriptionForNumber($this->phoneNumberObject, $locale, $region);
    }

    /**
     * Generates a hyperlink tag formatted with the phone number
     *
     * @param array $attributes Attributes to apply to the hyperlink
     */
    public function getLink(array $attributes = []): ?Markup
    {
        if ($this->phoneNumberObject === null) {
            return null;
        }

        $html = ArrayHelper::remove($attributes, 'html');
        $text = ArrayHelper::remove($attributes, 'text', $this->__toString());

        $content = $html ?? Html::encode($text);

        $attributes['href'] = $this->format('rfc3966');

        $link = Html::tag('a', $content, $attributes);

        return Template::raw($link);
    }

    /**
     * Returns a list of time zones to which this phone number belongs.
     */
    public function getTimeZones(): array
    {
        $mapper = PhoneNumberToTimeZonesMapper::getInstance();

        return $mapper->getTimeZonesForNumber($this->phoneNumberObject);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['number'], PhoneNumberValidator::class],
        ];
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return [
            'number' => $this->number,
            'region' => $this->region,
        ];
    }
}
