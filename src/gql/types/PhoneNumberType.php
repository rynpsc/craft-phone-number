<?php
/**
 * @link https://www.ryanpascoe.co
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\gql\types;

use craft\gql\GqlEntityRegistry;
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
    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'phoneNumber_PhoneNumber';
    }

    /**
     * @return Type
     */
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

    /**
     * @return array
     */
    public static function getFieldDefinitions(): array
    {
        return [
            'region' => [
                'name' => 'region',
                'type' => Type::string(),
            ],
            'number' => [
                'name' => 'number',
                'type' => Type::string(),
            ],
            'countryCode' => [
                'name' => 'countryCode',
                'type' => Type::string(),
            ],
            'regionCode' => [
                'name' => 'regionCode',
                'type' => Type::string(),
            ],
            'type' => [
                'name' => 'type',
                'type' => Type::int(),
            ],
            'description' => [
                'name' => 'description',
                'type' => Type::string(),
            ],
        ];
    }
}
