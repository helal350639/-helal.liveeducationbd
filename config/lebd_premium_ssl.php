<?php

return [
    // Uses the existing SSLCZ_* variables. Never commit real credentials.
    'store_id' => env('SSLCZ_STORE_ID', ''),
    'store_password' => env('SSLCZ_STORE_PASSWORD', ''),
    'sandbox' => filter_var(env('SSLCZ_TESTMODE', true), FILTER_VALIDATE_BOOLEAN),
];
