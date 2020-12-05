<?php
/**
 * @link https://www.ryanpascoe.co
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\fields;

use rynpsc\phonenumber\assets\PhoneNumberAsset;
use rynpsc\phonenumber\gql\types\PhoneNumberType;
use rynpsc\phonenumber\models\PhoneNumberModel;
use rynpsc\phonenumber\validators\PhoneNumberValidator;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\helpers\ArrayHelper;
use craft\helpers\Html;
use craft\helpers\Json;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

use yii\db\Schema;

/**
 * Phone Number Field
 *
 * @author Ryan Pascoe
 * @package Phone Number
 * @since 1.0
 */
class PhoneNumberField extends Field implements PreviewableFieldInterface
{
    /**
     * @var string|null
     */
    public $defaultRegion;

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
    public function getContentColumnType(): string
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
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
    public function serializeValue($value, ElementInterface $element = null)
    {
        if (!is_object($value)) {
            return null;
        }

        return Json::encode($value);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();

        $id = $view->formatInputId($this->handle);
        $namespace = Craft::$app->view->namespaceInputId($id);

        $phoneNumberUtil = PhoneNumberUtil::getInstance();

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
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('phone-number/_settings', [
            'field' => $this,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        if (!$value) {
            return '';
        }

        return $value->getLink();
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules() :array
    {
        return $rules = [
            PhoneNumberValidator::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getContentGqlType()
    {
        return PhoneNumberType::getType();
    }

    /**
     * Get the regions and metadata
     *
     * @return array
     */
    public function getRegionOptions()
    {
        $regions = [];
        $language = Craft::$app->request->getPreferredLanguage();
        $supportedRegions = PhoneNumberUtil::getInstance()->getSupportedRegions();

        foreach ($supportedRegions as $region) {
            if (Craft::$app->getI18n()->getIsIntlLoaded()) {
                $label = \Locale::getDisplayRegion('-'.$region, $language);
            } else {
                $label = Craft::t('app', $region);
            }

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
}
