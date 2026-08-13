<?php

return [

    'currency' => env('POS_CURRENCY', 'TZS'),

    'store' => [
        'name' => env('STORE_NAME', 'POS Store'),
        'address' => env('STORE_ADDRESS', 'Main Street'),
        'phone' => env('STORE_PHONE', '+255 700 000 000'),
        'tin' => env('STORE_TIN', 'TIN-000000000'),
        'footer' => env('STORE_FOOTER', 'Thank you for shopping with us!'),
    ],

    'loyalty' => [
        // 1 point for every X currency spent
        'points_per_currency' => (float) env('LOYALTY_POINTS_PER_CURRENCY', 1000),
    ],

    'invoice_prefix' => env('INVOICE_PREFIX', 'INV'),

];
