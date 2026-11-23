
<?php

return [
    'app_id' => env('META_APP_ID'),

    'access_token' => env('META_ACCESS_TOKEN'),

    'ad_account_id' => env('META_AD_ACCOUNT_ID'),

    'ad_account_ids' => array_values(array_filter(
        array_map(
            'trim',
            explode(
                ',',
                env('META_AD_ACCOUNT_IDS', env('META_AD_ACCOUNT_ID', ''))
            )
        )
    )),

    'api_version' => env('META_API_VERSION', 'v25.0'),
];
