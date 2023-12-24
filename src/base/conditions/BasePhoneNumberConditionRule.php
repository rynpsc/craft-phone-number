<?php

namespace rynpsc\phonenumber\base\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\helpers\Cp;
use craft\helpers\Db;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use rynpsc\phonenumber\PhoneNumber;
use yii\base\InvalidConfigException;

abstract class BasePhoneNumberConditionRule extends BaseConditionRule
{
	protected const FIELD_REGION = 'region';
	protected const FIELD_NUMBER = 'number';

	public string $subField = self::FIELD_REGION;

	public string $regionOperator = self::OPERATOR_IN;

	public string $numberOperator = self::OPERATOR_CONTAINS;

	public array $region = [];

	public string $number = '';

	/**
	 * @inerhitdoc
	 */
	public function getConfig(): array
	{
		return array_merge(parent::getConfig(), [
			'subField' => $this->subField,
			'regionOperator' => $this->regionOperator,
			'numberOperator' => $this->numberOperator,
			'region' => $this->region,
			'number' => $this->number,
		]);
	}

	/**
	 * @inerhitdoc
	 */
	protected function inputHtml(): string
	{
		$s = 'select';
		$html = [];

		$html[] = Html::hiddenLabel(Html::encode($this->getLabel()), $s);

		$html[] = Cp::selectHtml([
			'id' => $s,
			'options' => $this->subFieldOptions(),
			'name' => 'subField',
			'value' => $this->subField,
			'inputAttributes' => [
				'hx' => [
					'post' => UrlHelper::actionUrl('conditions/render'),
				],
			],
		]);

		if ($this->subField === 'region') {
			$html[] = Html::hiddenLabel(Html::encode($this->getLabel()), 'region');
			$html[] = Html::hiddenLabel(Html::encode($this->getLabel()), 'regionOperator');

			$html[] = Cp::selectHtml([
				'id' => 'regionOperator',
				'name' => 'regionOperator',
				'value' => $this->regionOperator,
				'options' => $this->regionOperators(),
			]);

			$regions = PhoneNumber::getInstance()->getPhoneNumber()->getAllSupportedRegions();

			$html[] = Cp::selectizeHtml([
				'id' => 'region',
				'class' => 'flex-grow',
				'name' => 'region',
				'values' => $this->region,
				'multi' => true,
				'options' => $regions->map(fn($region) => $region['countryName']),
			]);
		}

		if ($this->subField === 'number') {
			$html[] = Html::hiddenLabel(Html::encode($this->getLabel()), 'number');
			$html[] = Html::hiddenLabel(Html::encode($this->getLabel()), 'numberOperator');

			$html[] = Cp::selectHtml([
				'id' => 'numberOperator',
				'name' => 'numberOperator',
				'value' => $this->numberOperator,
				'options' => $this->numberOperators(),
			]);

			$html[] = Cp::textHtml([
				'id' => 'number',
				'name' => 'number',
				'value' => $this->number,
				'class' => 'flex-grow flex-shrink',
			]);
		}

		return join("\n", $html);
	}

	protected function regionOperators(): array
	{
		return [
			self::OPERATOR_IN => self::operatorLabel(self::OPERATOR_IN),
			self::OPERATOR_NOT_IN => self::operatorLabel(self::OPERATOR_NOT_IN),
		];
	}

	protected function numberOperators(): array
	{
		return [
			self::OPERATOR_CONTAINS => self::operatorLabel(self::OPERATOR_CONTAINS),
			self::OPERATOR_BEGINS_WITH => self::operatorLabel(self::OPERATOR_BEGINS_WITH),
			self::OPERATOR_ENDS_WITH => self::operatorLabel(self::OPERATOR_ENDS_WITH),
		];
	}

	protected function subFieldOptions(): array
	{
		return [
			self::FIELD_REGION => Craft::t('phone-number', 'Region'),
			self::FIELD_NUMBER => Craft::t('phone-number', 'Number'),
			self::OPERATOR_EMPTY => self::operatorLabel(self::OPERATOR_EMPTY),
			self::OPERATOR_NOT_EMPTY => self::operatorLabel(self::OPERATOR_NOT_EMPTY),
		];
	}

	/**
	 * @inerhitdoc
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['subField'], 'in', 'range' => array_keys($this->subFieldOptions())],
			[['regionOperator'], 'in', 'range' => array_keys($this->regionOperators())],
			[['numberOperator'], 'in', 'range' => array_keys($this->numberOperators())],
			[['region'], 'safe'],
			[['number'], 'safe'],
		]);
	}

	protected function paramValue(): array|string|null
	{
		switch ($this->subField) {
			case self::OPERATOR_EMPTY:
				return ':empty:';
			case self::OPERATOR_NOT_EMPTY:
				return 'not :empty:';
		}

		if ($this->subField === self::FIELD_NUMBER) {
			$number = Db::escapeParam($this->number);

			if ($this->number === '') {
				return null;
			}

			$value = match ($this->numberOperator) {
				self::OPERATOR_BEGINS_WITH => "$number*",
				self::OPERATOR_CONTAINS => "*$number*",
				self::OPERATOR_ENDS_WITH => "*$number",
				default => throw new InvalidConfigException("Invalid operator: $this->numberOperator"),
			};

			return [self::FIELD_NUMBER => $value];
		}

		if ($this->subField === self::FIELD_REGION) {
			if (empty($this->region)) {
				return null;
			}
			$value = match ($this->regionOperator) {
				self::OPERATOR_IN => $this->region,
				self::OPERATOR_NOT_IN => array_merge(['not'], $this->region),
				default => throw new InvalidConfigException("Invalid operator: $this->regionOperator"),
			};

			return [self::FIELD_REGION => $value];
		}

		throw new InvalidConfigException("Invalid operator: $this->subField");
	}

	protected function matchValue(mixed $value): bool
	{
		$matches = false;

		if ($value === null) {
			return false;
		}

		if ($this->subField === self::FIELD_NUMBER) {
			$matches = match ($this->numberOperator) {
				self::OPERATOR_BEGINS_WITH => is_string($value->number) && StringHelper::startsWith($value->number, $this->number),
				self::OPERATOR_CONTAINS => is_string($value->number) && StringHelper::contains($value->number, $this->number),
				self::OPERATOR_ENDS_WITH => is_string($value->number) && StringHelper::endsWith($value->number, $this->number),
				default => throw new InvalidConfigException("Invalid operator: $this->numberOperator"),
			};
		}

		if ($this->subField === self::FIELD_REGION) {
			$matches = match ($this->regionOperator) {
				self::OPERATOR_IN => in_array($value->region, $this->region),
				self::OPERATOR_NOT_IN => !in_array($value->region, $this->region),
				default => throw new InvalidConfigException("Invalid operator: $this->regionOperator"),
			};
		}

		return $matches;
	}
}
