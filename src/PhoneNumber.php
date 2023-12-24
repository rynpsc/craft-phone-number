<?php
/**
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterGqlTypesEvent;
use craft\feedme\events\RegisterFeedMeFieldsEvent;
use craft\feedme\Plugin as FeedMe;
use craft\feedme\services\Fields as FeedMeFields;
use craft\services\Fields;
use craft\services\Gql;
use craft\web\twig\variables\CraftVariable;
use rynpsc\phonenumber\assets\PhoneNumberAsset;
use rynpsc\phonenumber\fields\PhoneNumberField;
use rynpsc\phonenumber\gql\types\PhoneNumberType;
use rynpsc\phonenumber\integrations\feedme\Field as FeedMeField;
use rynpsc\phonenumber\services\PhoneNumber as PhoneNumberService;
use rynpsc\phonenumber\twigextensions\PhoneNumberExtension;
use yii\base\Event;

/**
 * Phone Number Plugin.
 *
 * @author Ryan Pascoe
 * @package Phone Number
 * @since 1.0
 */
class PhoneNumber extends Plugin
{
    /**
     * @inerhitdoc
     */
    public static function config(): array
    {
        return [
            'components' => [
                'phoneNumber' => ['class' => PhoneNumberService::class],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $view = Craft::$app->getView();
            $view->registerAssetBundle(PhoneNumberAsset::class);
        }

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;

                $variable->set('phoneNumber', PhoneNumberService::class);
            }
        );

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = PhoneNumberField::class;
            }
        );

        Event::on(
            Gql::class,
            Gql::EVENT_REGISTER_GQL_TYPES,
            function(RegisterGqlTypesEvent $event) {
                $event->types[] = PhoneNumberType::class;
            }
        );

        if (class_exists(FeedMe::class)) {
            Event::on(
                FeedMeFields::class,
                FeedMeFields::EVENT_REGISTER_FEED_ME_FIELDS,
                function(RegisterFeedMeFieldsEvent $event) {
                    $event->fields[] = FeedMeField::class;
                }
            );
        }

        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            Craft::$app->getView()->registerTwigExtension(new PhoneNumberExtension());
        }
    }

    public function getPhoneNumber(): PhoneNumberService
    {
        return $this->get('phoneNumber');
    }
}
