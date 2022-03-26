<?php
/**
 * @link https://www.ryanpascoe.co
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\gql\types;

use craft\gql\GqlEntityRegistry;
use craft\helpers\ArrayHelper;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class PhoneNumberType
 *
 * @author Jayden Smith
 * @package Phone Number
 * @since 1.4.0
 */
class PhoneNumberType
{
    public static function getName(): string
    {
        return 'phoneNumber_PhoneNumber';
    }

    public static function getType(): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::class)) {
            return $type;
        }

        return GqlEntityRegistry::createEntity(self::class, new ObjectType([
            'name' => static::getName(),
            'fields' => self::class . '::getFieldDefinitions',
            'description' => 'This is the interface implemented by all phone number fields.',
        ]));
    }

    public static function getFieldDefinitions(): array
    {
        return [
            'countryCode' => [
                'name' => 'countryCode',
                'type' => Type::string(),
            ],
            'description' => [
                'name' => 'description',
                'type' => Type::string(),
                'args' => [
                    'locale' => Type::string(),
                    'region' => Type::string(),
                ],
                'resolve' => function($source, $arguments) {
                    return $source->getDescription(
                        ArrayHelper::getValue($arguments, 'locale'),
                        ArrayHelper::getValue($arguments, 'region'),
                    );
                }
            ],
            'extension' => [
                'name' => 'extension',
                'type' => Type::string(),
            ],
            'format' => [
                'name' => 'format',
                'type' => Type::string(),
                'args' => ['format' => Type::string()],
                'resolve' => function($source, $arguments) {
                    return $source->format(ArrayHelper::getValue($arguments, 'format'));
                }
            ],
            'formatCountry' => [
                'name' => 'formatForCountry',
                'type' => Type::string(),
                'args' => ['region' => Type::string()],
                'resolve' => function($source, $arguments) {
                    return $source->formatForCountry(ArrayHelper::getValue($arguments, 'region'));
                },
            ],
            'formatMobileDialing' => [
                'name' => 'formatForMobileDialing',
                'type' => Type::string(),
                'args' => [
                    'region' => Type::string(),
                    'format' => Type::boolean(),
                ],
                'resolve' => function($source, $arguments) {
                    return $source->formatForMobileDialing(
                        ArrayHelper::getValue($arguments, 'region'),
                        ArrayHelper::getValue($arguments, 'format'),
                    );
                },
            ],
            'number' => [
                'name' => 'number',
                'type' => Type::string(),
            ],
            'region' => [
                'name' => 'region',
                'type' => Type::string(),
            ],
            'regionCode' => [
                'name' => 'regionCode',
                'type' => Type::string(),
            ],
            'timezones' => [
                'name' => 'timezones',
                'type' => Type::listOf(Type::string()),
            ],
            'type' => [
                'name' => 'type',
                'type' => Type::int(),
            ],
        ];
    }
}
