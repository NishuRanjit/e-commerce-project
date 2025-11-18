<?php
// Stripe API configuration
$stripe_keys = [
    'secret' => '',
    'publishable' => ''
];

// Load Stripe SDK (if not already included)
require_once('vendor/autoload.php');

// Initialize Stripe with your secret key
\Stripe\Stripe::setApiKey($stripe_keys['secret']);
