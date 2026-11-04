<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/students/pay-via-ajax', '/students/success','/students/cancel','/students/fail','/students/ipn',

        // LEBD-05: Third-party SSLCommerz POST callbacks only.
        // Checkout/pay/pay-referral remain CSRF-protected.
        'payments/mcq/success',
        'payments/mcq/fail',
        'payments/mcq/cancel',
        'payments/mcq/ipn',
        'payments/mcq/ssl/success',
        'payments/mcq/ssl/fail',
        'payments/mcq/ssl/cancel',
        'payments/mcq/ssl/ipn',
    ];
}
