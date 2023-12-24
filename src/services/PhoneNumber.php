<?php

namespace rynpsc\phonenumber\services;

use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\helpers\Html;
use craft\helpers\Template;
use Illuminate\Support\Collection;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Locale;
use rynpsc\phonenumber\helpers\StringHelper;
use Twig\Markup;

class PhoneNumber extends Component
{
	public function getAllSupportedRegions(): Collection
	{
		$regions = [];
		$language = Craft::$app->request->getPreferredLanguage();
		$supportedRegions = PhoneNumberUtil::getInstance()->getSupportedRegions();

		foreach ($supportedRegions as $region) {
			$label = Locale::getDisplayRegion('-' . $region, $language);

			$countryCode = PhoneNumberUtil::getInstance()->getCountryCodeForRegion($region);

			$regions[$region] = [
				'callingCode' => $countryCode,
				'countryName' => $label,
				'countryCode' => $region,
			];
		}

		return Collection::make($regions)->sortBy('countryName');
	}

	/**
	 * Finds phone numbers in a given string and converts them to links.
	 *
	 * @param string|null $string The string to search
	 * @param string|null $region The default region to apply to numbers without a calling code.
	 * @param array $attributes The HTML attributes to apply to generated link tags.
	 * @return Markup|null
	 */
	public function convertNumbersToLinks(string $string = null, string $region = null, array $attributes = []): ?Markup
	{
		if ($string == null) {
			return null;
		}

		$offset = 0;
		$charset = Craft::$app->charset;

		$phoneNumberUtil = PhoneNumberUtil::getInstance();
		$phoneNumberMatches = $phoneNumberUtil->findNumbers($string, $region);

		foreach ($phoneNumberMatches as $phoneNumberMatch) {
			$attrs = $attributes;
			$textLength = mb_strlen($string, $charset);

			$start = $phoneNumberMatch->start();
			$length = mb_strlen($phoneNumberMatch->rawString(), $charset);

			$html = ArrayHelper::remove($attrs, 'html');
			$text = ArrayHelper::remove($attrs, 'text', $phoneNumberMatch->rawString());

			$content = $html ?? Html::encode($text);

			$attrs['href'] = $phoneNumberUtil->format($phoneNumberMatch->number(), PhoneNumberFormat::RFC3966);

			$link = Html::tag('a', $content, $attrs);

			// Replace number with anchor
			$string = StringHelper::substrReplace($string, $link, ($start + $offset), $length);

			// Account for new string length
			$offset += mb_strlen($string, $charset) - $textLength;
		}

		return Template::raw($string);
	}
}
