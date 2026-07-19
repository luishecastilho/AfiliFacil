<?php

return [
    'free' => [
        'name' => 'Gratuito',
        'price' => 0,
        'nf_limit' => 5,
        'stripe_price_id' => env('STRIPE_PRICE_FREE', null),
    ],
    'basic' => [
        'name' => 'Básico',
        'price' => 39.90,
        'nf_limit' => 50,
        'stripe_price_id' => env('STRIPE_PRICE_BASIC'),
    ],
    'advanced' => [
        'name' => 'Avançado',
        'price' => 169.90,
        'nf_limit' => null, // unlimited
        'stripe_price_id' => env('STRIPE_PRICE_ADVANCED'),
    ],
];
