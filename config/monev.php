<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Initial application accounts
    |--------------------------------------------------------------------------
    |
    | DatabaseSeeder creates an account only when both its username and
    | password are configured. Passwords intentionally have no repository
    | default; set strong, environment-specific values before seeding.
    |
    */
    'seed_accounts' => [
        'admin' => [
            'name' => env('MONEV_SEED_ADMIN_NAME', 'Admin DP2KBP3A'),
            'username' => env('MONEV_SEED_ADMIN_USERNAME', 'admin_dp2kbp3a'),
            'password' => env('MONEV_SEED_ADMIN_PASSWORD'),
        ],
        'pkk' => [
            'name' => env('MONEV_SEED_PKK_NAME', 'PKK DP2KBP3A'),
            'username' => env('MONEV_SEED_PKK_USERNAME', 'pkk_dp2kbp3a'),
            'password' => env('MONEV_SEED_PKK_PASSWORD'),
        ],
    ],
];
