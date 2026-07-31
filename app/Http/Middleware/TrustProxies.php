<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class TrustProxies
{
    /**
     * The trusted proxy patterns.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var array<int, string>
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $request->setTrustedProxies(['0.0.0.0/0', '::/0'], $this->headers);

        $scheme = 'http';
        $host = $request->getHost();

        if ($request->headers->has('X-Forwarded-Proto')) {
            $scheme = $request->headers->get('X-Forwarded-Proto');
            $request->server->set('HTTPS', 'on');
            $request->server->set('REQUEST_SCHEME', $scheme);
            $request->server->set('HTTP_X_FORWARDED_PROTO', $scheme);
            $request->headers->set('X-Forwarded-Proto', $scheme);
        }

        if ($request->headers->has('X-Forwarded-Host')) {
            $host = $request->headers->get('X-Forwarded-Host');
            $request->server->set('HTTP_HOST', $host);
            $request->headers->set('Host', $host);
        }

        $request->server->set('HTTP_HOST', $host);
        $request->headers->set('Host', $host);

        URL::forceRootUrl($scheme . '://' . $host);
        URL::forceScheme($scheme);

        return $next($request);
    }
}
