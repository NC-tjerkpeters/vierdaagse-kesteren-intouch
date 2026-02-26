<?php

/**
 * Mollie transactiekosten (schatting) per betaalmethode.
 * Bron: mollie.com/pricing – controleer regelmatig op wijzigingen.
 * Formaat: ['percentage' => 0.0, 'fixed' => 0.00] of alleen fixed.
 */
return [
    'ideal' => ['percentage' => 0, 'fixed' => 0.29],
    'bancontact' => ['percentage' => 1.4, 'fixed' => 0.25],
    'creditcard' => ['percentage' => 1.8, 'fixed' => 0.25],
    'paypal' => ['percentage' => 3.4, 'fixed' => 0.35],
    'sofort' => ['percentage' => 1.8, 'fixed' => 0.25],
    'giropay' => ['percentage' => 1.8, 'fixed' => 0.25],
    'eps' => ['percentage' => 1.8, 'fixed' => 0.25],
    'belfius' => ['percentage' => 1.4, 'fixed' => 0.25],
    'klarnapaylater' => ['percentage' => 2.99, 'fixed' => 0.29],
    'klarnasliceit' => ['percentage' => 2.99, 'fixed' => 0.29],
    'przelewy24' => ['percentage' => 1.8, 'fixed' => 0.25],
    'applepay' => ['percentage' => 1.8, 'fixed' => 0.25],
    'directdebit' => ['percentage' => 0, 'fixed' => 0.35],
    'banktransfer' => ['percentage' => 0, 'fixed' => 0.25],
    'default' => ['percentage' => 1.8, 'fixed' => 0.25],
];
