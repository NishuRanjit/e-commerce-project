<?php
// Stripe API configuration
$stripe_keys = [
    'secret' => 'sk_test_51QtsOJFA89whLkDzGm4kN1OAzgmhGkyvq5QhKpYkg6v0uuDffojUBl6ai4whoQ6ehEp1y1iKB8uAgvfpzfyM75ql00rEqqX93S',
    'publishable' => 'pk_test_51QtsOJFA89whLkDzl2wQYtWNVymWFcOAlQ9MVYM8y2fob5tN14mwxQBHKrZkzlY8BDGP0mdGBQjS4mk9p9MZT97F00G3GyV6ra'
];

// Load Stripe SDK (if not already included)
require_once('vendor/autoload.php');

// Initialize Stripe with your secret key
\Stripe\Stripe::setApiKey($stripe_keys['secret']);
