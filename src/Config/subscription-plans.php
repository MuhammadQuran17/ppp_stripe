<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for subscription plans.
    | These plans are used for displaying on the frontend and duplicates products in Stripe.
    |
    */

    'plans' => [
        'lifetime' => [
            'price_in_USA' => 98,
            'stripe_product_id' => env('STRIPE_LIFETIME_PRODUCT_ID'),
        ],
    ],
];