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
use craft\base\PreviewableFieldInterface;
use craft\helpers\ArrayHelper;
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
class PhoneNumberField extends Field implements PreviewableFieldInterface
{
    public ?string $defaultRegion = null;

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

    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ?\craft\base\ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();

        $id = $view->formatInputId($this->handle);
        $namespace = Craft::$app->view->namespaceInputId($id);

        $view->registerAssetBundle(PhoneNumberAsset::class);
        $view->registerJs("new PhoneNumber('{$namespace}');");
        Craft::$app->assetManager->getPublishedUrl('@rynpsc/phonenumber/assets/dist/flags/sprite.png', true);

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
    public function getTableAttributeHtml(mixed $value, ElementInterface $element): string
    {
        if (!$value) {
            return '';
        }

        return $value->getLink();
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
