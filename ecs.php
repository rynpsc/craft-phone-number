<?php

use craft\ecs\SetList;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function(ECSConfig $ecsConfig): void {
	$ecsConfig->paths([
		__FILE__,
		__DIR__ . '/src',
	]);

	$ecsConfig->indentation('tab');
	$ecsConfig->parallel();
	$ecsConfig->sets([SetList::CRAFT_CMS_4]);
};
