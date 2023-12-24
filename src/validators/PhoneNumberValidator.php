<?php
/**
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\validators;

use Craft;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use yii\validators\Validator;

/**
 * Phone number validator
 *
 * @author Ryan Pascoe
 * @package Phone Number
 * @since 1.0
 */
class PhoneNumberValidator extends Validator
{
	/**
	 * @inheritdoc
	 */
	public function validateAttribute($model, $attribute): void
	{
		$valid = false;
		$message = null;
		$value = $model->$attribute;
		$phoneNumberUtil = PhoneNumberUtil::getInstance();

		try {
			$phoneNumber = $phoneNumberUtil->parseAndKeepRawInput($value->number, $value->region);
			$valid = $phoneNumberUtil->isValidNumberForRegion($phoneNumber, $value->region);
		} catch (NumberParseException $e) {
			$message = $e->getMessage();
		}

		if ($valid == false) {
			if (is_null($message)) {
				$message = 'The phone number provided is invalid.';
			}

			$message = Craft::t('phone-number', $message);
			$this->addError($model, $attribute, $message);
		}
	}
}
