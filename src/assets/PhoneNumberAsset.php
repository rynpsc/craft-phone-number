<?php
/**
 * @copyright Copyright (c) Ryan Pascoe
 * @license MIT
 */

namespace rynpsc\phonenumber\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Asset bundle for Phone Number field
 *
 * @author Ryan Pascoe
 * @package Phone Number
 * @since 1.0
 */
class PhoneNumberAsset extends AssetBundle
{
	/**
	 * @inheritdoc
	 */
	public function init(): void
	{
		$this->sourcePath = "@rynpsc/phonenumber/assets/dist";

		$this->depends = [
			CpAsset::class,
		];

		$this->css = [
			'styles/main.css',
			'styles/sprite.css',
		];

		$this->js = [
			'scripts/main.js',
		];

		parent::init();
	}
}
