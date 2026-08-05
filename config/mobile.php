<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Мобільний застосунок — deep link / universal link
    |--------------------------------------------------------------------------
    */

    'universal_link_host' => env('MOBILE_UNIVERSAL_LINK_HOST', 'https://www.ttclub.com.ua'),

    'custom_scheme' => env('MOBILE_CUSTOM_SCHEME', 'ttclubua'),

    'ios' => [
        'team_id' => env('MOBILE_IOS_TEAM_ID', 'FLX2SK6ZJP'),
        'bundle_id' => env('MOBILE_IOS_BUNDLE_ID', 'iskaplus.ttClubUa'),
    ],

    'android' => [
        'package_name' => env('MOBILE_ANDROID_PACKAGE', 'ua.com.ttclub.app'),
        'sha256_cert_fingerprints' => array_filter(explode(',', env('MOBILE_ANDROID_SHA256_FINGERPRINTS', ''))),
    ],
];
