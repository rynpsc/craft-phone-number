<?php

namespace rynpsc\phonenumber\services;

use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use libphonenumber\PhoneNumberUtil;
use Locale;

class PhoneNumber extends Component
{
    public function getAllSupportedRegions()
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

        ArrayHelper::multisort($regions, 'countryCode');

        return $regions;
    }
}
