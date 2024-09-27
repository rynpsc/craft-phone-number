<?php
/**
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\InlineEditableFieldInterface;
use craft\base\MergeableFieldInterface;
use craft\db\QueryParam;
use craft\gql\GqlEntityRegistry;
use craft\helpers\Db;
use craft\helpers\Html;
use craft\helpers\Json;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use rynpsc\phonenumber\assets\PhoneNumberAsset;
use rynpsc\phonenumber\fields\conditions\PhoneNumberFieldConditionRule;
use rynpsc\phonenumber\gql\types\PhoneNumberType;
use rynpsc\phonenumber\models\PhoneNumberModel;
use rynpsc\phonenumber\PhoneNumber;
use rynpsc\phonenumber\validators\PhoneNumberValidator;
use yii\db\Schema;

/**
 * Phone Number Field
 *
 * @author Ryan Pascoe
 * @package Phone Number
 * @since 1.0
 */
class PhoneNumberField extends Field implements InlineEditableFieldInterface, MergeableFieldInterface
{
	/**
	 * @var string|null
	 */
	public ?string $defaultRegion = null;

	/**
	 * @var string|null
	 */
	public ?string $previewFormat = null;

	/**
	 * @inheritdoc
	 */
	public static function dbType(): array
	{
		return [
			'region' => Schema::TYPE_STRING,
			'number' => Schema::TYPE_STRING,
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function queryCondition(array $instances, mixed $value, array &$params): ?array
	{
		if (!is_array($value)) {
			return parent::queryCondition($instances, $value, $params);
		}

		$operator = QueryParam::extractOperator($value);

		if ($operator === null) {
			array_unshift($value, QueryParam::AND);
		}

		$param = QueryParam::parse($value);

		$condition = [$param->operator];

		/** @var array $values */
		$values = $param->values;

		if (array_is_list($values)) {
			$values = Collection::make($values)->collapse()->toArray();
		}

		if (empty($values)) {
			return null;
		}

		foreach ($values as $k => $v) {
			$valueSql = static::valueSql($instances, $k);
			$condition[] = Db::parseParam($valueSql, $v, columnType: Schema::TYPE_JSON);
		}

		return $condition;
	}

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
    public static function icon(): string
    {
        return 'phone';
    }

	/**
	 * @inheritdoc
	 */
	public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
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

	protected function internalInputHtml(mixed $value, ?ElementInterface $element, bool $static): string
	{
		$view = Craft::$app->getView();

		$id = Html::id($this->handle);
		$namespace = Craft::$app->view->namespaceInputId($id);

		$view->registerAssetBundle(PhoneNumberAsset::class);
		$view->registerJs("new PhoneNumber('{$namespace}');");

		$regions = PhoneNumber::getInstance()->getPhoneNumber()->getAllSupportedRegions();

		return $view->renderTemplate('phone-number/_input.twig', [
			'element' => $element,
			'field' => $this,
			'id' => $id,
			'name' => $this->handle,
			'value' => $value,
			'regions' => $regions,
			'static' => $static,
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function inputHtml(mixed $value, ?ElementInterface $element, bool $inline): string
	{
		return $this->internalInputHtml(value: $value, element: $element, static: false);
	}

	/**
	 * @inheritdoc
	 */
	public function getStaticHtml(mixed $value, ElementInterface $element): string
	{
		return $this->internalInputHtml(value: $value, element: $element, static: true);
	}

	/**
	 * @inheritdoc
	 */
	public function getSettingsHtml(): ?string
	{
		$regions = PhoneNumber::getInstance()->getPhoneNumber()->getAllSupportedRegions();

		return Craft::$app->getView()->renderTemplate('phone-number/_settings.twig', [
			'field' => $this,
			'regions' => $regions,
		]);
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
	public function getContentGqlType(): Type|array
	{
		return PhoneNumberType::getType();
	}

	/**
	 * @inheritdoc
	 */
	public function getContentGqlQueryArgumentType(): Type|array
	{
		$typeName = $this->handle . '_PhoneNumberQueryArgument';

		return [
			'name' => $this->handle,
			'type' => GqlEntityRegistry::getOrCreate($typeName, fn() => new InputObjectType([
				'name' => $typeName,
				'fields' => [
					'region' => [
						'name' => 'region',
						'type' => Type::listOf(Type::string()),
						'description' => 'The region',
					],
					'number' => [
						'name' => 'number',
						'type' => Type::listOf(Type::string()),
						'description' => 'The number',
					],
				],
			])),
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getContentGqlMutationArgumentType(): Type|array
	{
		$typeName = $this->handle . '_PhoneNumberMutationArgument';

		$type = GqlEntityRegistry::getOrCreate($typeName, fn() => new InputObjectType([
			'name' => $typeName,
			'fields' => [
				'region' => [
					'name' => 'region',
					'type' => Type::string(),
					'description' => 'The region',
				],
				'number' => [
					'name' => 'number',
					'type' => Type::string(),
					'description' => 'The number',
				],
			],
		]));

		return [
			'name' => $this->handle,
			'type' => $type,
			'description' => $this->instructions,
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getPreviewHtml(mixed $value, ElementInterface $element): string
	{
		if ($value instanceof PhoneNumberModel) {
			return $value->format($this->previewFormat);
		}

		return parent::getPreviewHtml($value, $element);
	}

	/**
	 * Gets an array of preview options formatted to pass to a select field.
	 *
	 * @since 3.0.0
	 */
	public function getPreviewFormatSettingsOptions(): array
	{
		return [
			['label' => Craft::t('phone-number', 'None'), 'value' => null],
			['label' => Craft::t('phone-number', 'E164'), 'value' => 'e164'],
			['label' => Craft::t('phone-number', 'International'), 'value' => 'international'],
			['label' => Craft::t('phone-number', 'National'), 'value' => 'national'],
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function defineRules(): array
	{
		$rules = parent::defineRules();

		$rules[] = [['previewFormat'], 'string'];

		return $rules;
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

	/**
	 * @inerhitdoc
	 */
	public function getElementConditionRuleType(): array|string|null
	{
		return PhoneNumberFieldConditionRule::class;
	}
}
