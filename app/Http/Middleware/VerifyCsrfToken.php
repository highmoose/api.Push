<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * URIs that should be excluded from CSRF protection.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/debug-csrf',
    ];
}
