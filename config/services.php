<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'yandex' => [
        'geocoder_key' => env('YANDEX_GEOCODER_KEY'),
        'suggest_key' => env('YANDEX_SUGGEST_KEY'),
        'js_key' => env('YANDEX_JS_KEY'),
    ],

    'osm' => [
        // Поиск организаций по названию («Дана Молл») для подсказок
        // адресов: бесплатный OpenStreetMap Nominatim вместо платного
        // Геопоиска Яндекса. Отключается флагом OSM_SEARCH_ENABLED=false.
        'enabled' => env('OSM_SEARCH_ENABLED', true),
    ],

];
