<?php

namespace rynpsc\phonenumber\integrations\feedme;

use Cake\Utility\Hash;
use craft\feedme\base\Field as BaseField;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\DataHelper;
use rynpsc\phonenumber\fields\PhoneNumberField;

class Field extends BaseField implements FieldInterface
{
    public static string $name = 'Phone Number';

    public static string $class = PhoneNumberField::class;

    /**
     * @inheritdoc
     */
    public function getMappingTemplate(): string
    {
        return 'phone-number/integrations/feedme/mapping-template';
    }

    /**
     * @inheritdoc
     */
    public function parseField(): mixed
    {
        $preppedData = [];

        $fields = Hash::get($this->fieldInfo, 'fields');

        if (!$fields) {
            return null;
        }

        foreach ($fields as $subFieldHandle => $subFieldInfo) {
            $preppedData[$subFieldHandle] = DataHelper::fetchValue($this->feedData, $subFieldInfo, $this->feed);
        }

        return $preppedData;
    }
}
