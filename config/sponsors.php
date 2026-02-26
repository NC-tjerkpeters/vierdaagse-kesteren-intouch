<?php

return [
    'doelbedrag' => (float) env('SPONSORS_DOELBEDRAG', 1850.00),

    'redirect_url' => env(
        'SPONSORS_REDIRECT_URL',
        'https://vierdaagsekesteren.nl/vrienden-van-de-vierdaagse-kesteren/aanmelden/bedankt-voor-uw-bijdrage/'
    ),

    'webhook_path' => env('SPONSORS_WEBHOOK_PATH', '/webhooks/mollie/sponsors'),

    'webhook_url' => env('SPONSORS_WEBHOOK_URL'),

    'receipt_bcc' => env('SPONSORS_RECEIPT_BCC', 'mail@vierdaagsekesteren.nl'),
];
