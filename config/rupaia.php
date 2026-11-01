<?php

return [
    'seller_policy_version' => env(
        'SELLER_POLICY_VERSION',
        '1.0'
    ),

    'seed' => [
        'admin_name' => env('SEED_ADMIN_NAME', 'Administrator'),
        'admin_email' => env('SEED_ADMIN_EMAIL', 'admin@rupaia.test'),
        'admin_password' => env('SEED_ADMIN_PASSWORD'),
    ],
];
