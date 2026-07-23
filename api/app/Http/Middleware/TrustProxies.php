<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Les proxies de confiance pour votre application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * Les en-têtes à définir comme proxies de confiance.
     *
     * @var int
     */
    protected $headers = 15; // HEADER_X_FORWARDED_ALL (X-Forwarded-For, Proto, Port, Host)
}