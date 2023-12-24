<?php

namespace rynpsc\phonenumber\fields\conditions;

use craft\fields\conditions\FieldConditionRuleInterface;
use craft\fields\conditions\FieldConditionRuleTrait;
use rynpsc\phonenumber\base\conditions\BasePhoneNumberConditionRule;

class PhoneNumberFieldConditionRule extends BasePhoneNumberConditionRule implements FieldConditionRuleInterface
{
	use FieldConditionRuleTrait;

	/**
	 * @inheritdoc
	 */
	protected function elementQueryParam(): mixed
	{
		return $this->paramValue();
	}

	/**
	 * @inheritdoc
	 */
	protected function matchFieldValue($value): bool
	{
		return $this->matchValue($value);
	}
}
