<?php
/**
 * @link https://www.ryanpascoe.co
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber;

use rynpsc\phonenumber\fields\PhoneNumberField;
use rynpsc\phonenumber\gql\types\PhoneNumberType;
use rynpsc\phonenumber\twigextensions\PhoneNumberExtension;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterGqlTypesEvent;
use craft\services\Fields;
use craft\services\Gql;

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
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Register field
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = PhoneNumberField::class;
        });

        Event::on(Gql::class, Gql::EVENT_REGISTER_GQL_TYPES, function(RegisterGqlTypesEvent $event) {
            $event->types[] = PhoneNumberType::class;
        });

        // Register Twig extension
        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            Craft::$app->getView()->registerTwigExtension(new PhoneNumberExtension());
        }
    }
}
