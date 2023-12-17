<?php
/**
 * @link https://www.ryanpascoe.co
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\InlineEditableFieldInterface;
use craft\helpers\ArrayHelper;
use craft\helpers\Html;
use craft\helpers\Json;
use libphonenumber\PhoneNumberUtil;
use Locale;
use rynpsc\phonenumber\assets\PhoneNumberAsset;
use rynpsc\phonenumber\gql\types\PhoneNumberType;
use rynpsc\phonenumber\models\PhoneNumberModel;
use rynpsc\phonenumber\validators\PhoneNumberValidator;

/**
 * Phone Number Field
 *
 * @author Ryan Pascoe
 * @package Phone Number
 * @since 1.0
 */
class PhoneNumberField extends Field implements InlineEditableFieldInterface
{
    public ?string $defaultRegion = null;

    /**
     * @var string
     */
    public string $previewFormat = 'none';

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('phone-number', 'Phone Number');
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue(mixed $value, ?\craft\base\ElementInterface $element = null): mixed
    {
        if ($value instanceof PhoneNumberModel) {
            return $value;
        }

        if (is_string($value) && !empty($value)) {
            $value = Json::decodeIfJson($value);
        }

        if (!is_array($value) || !$value['number']) {
            return null;
        }

        return new PhoneNumberModel(
            $value['number'],
            $value['region'] ?? null
        );
    }

    /**
     * @inheritdoc
     */
    public function serializeValue(mixed $value, ?\craft\base\ElementInterface $element = null): ?string
    {
        if (!is_object($value)) {
            return null;
        }

        return Json::encode($value);
    }

    public function inputHtml(mixed $value, ?ElementInterface $element, bool $inline): string
    {
        $view = Craft::$app->getView();

        $id = Html::id($this->handle);
        $namespace = Craft::$app->view->namespaceInputId($id);

        $view->registerAssetBundle(PhoneNumberAsset::class);
        $view->registerJs("new PhoneNumber('{$namespace}');");

        return $view->renderTemplate('phone-number/_input', [
            'element' => $element,
            'field' => $this,
            'id' => $id,
            'name' => $this->handle,
            'value' => $value,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('phone-number/_settings', [
            'field' => $this,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        return [
            PhoneNumberValidator::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getContentGqlType(): array|\GraphQL\Type\Definition\Type
    {
        return PhoneNumberType::getType();
    }

    /**
     * @inheritdoc
     */
    public function getPreviewHtml(mixed $value, ElementInterface $element): string
    {
        if ($value instanceof PhoneNumberModel) {
            return $value->format($this->previewFormat);
        }

        return parent::getPreviewHtml($value, $element);
    }

    /**
     * Gets an array of preview options formatted to pass to a select field.
     *
     * @since 3.0.0
     */
    public function getPreviewFormatSettingsOptions(): array
    {
        return [
            ['label' => Craft::t('phone-number', 'E164'), 'value' => 'e164'],
            ['label' => Craft::t('phone-number', 'International'), 'value' => 'international'],
            ['label' => Craft::t('phone-number', 'National'), 'value' => 'national'],
            ['label' => Craft::t('phone-number', 'Unformatted'), 'value' => null],
        ];
    }

    /**
     * Get the regions and metadata
     */
    public function getRegionOptions(): array
    {
        $regions = [];
        $language = Craft::$app->request->getPreferredLanguage();
        $supportedRegions = PhoneNumberUtil::getInstance()->getSupportedRegions();

        foreach ($supportedRegions as $region) {
            $label = Locale::getDisplayRegion('-' . $region, $language);

            $countryCode = PhoneNumberUtil::getInstance()->getCountryCodeForRegion($region);

            $regions[] = [
                'code' => $countryCode,
                'label' => $label,
                'value' => $region,
            ];
        }

        ArrayHelper::multisort($regions, 'label');

        return $regions;
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['previewFormat'], 'string', ];

        return $rules;
    }

    /**
     * @inerhitdoc
     */
    protected function searchKeywords(mixed $value, ElementInterface $element): string
    {
        $keywords = [];

        if ($value instanceof PhoneNumberModel) {
            $keywords[] = $value->number;
            $keywords[] = $value->format('national');
            $keywords[] = $value->format('international');
        }

        return parent::searchKeywords($keywords, $element);
    }
}
