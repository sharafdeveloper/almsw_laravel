<?php

namespace Tests\Feature;

use App\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TrustProxiesTest extends TestCase
{
    public function test_it_uses_forwarded_host_and_proto_for_url_generation(): void
    {
        URL::forceRootUrl(null);
        URL::forceScheme('http');

        $request = Request::create('https://example.test/login', 'GET', [], [], [], [
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'ramp-uniformed-repaint.ngrok-free.dev',
        ]);

        $this->app->instance('request', $request);

        $middleware = new TrustProxies();
        $response = $middleware->handle($request, function ($request) {
            return response()->noContent();
        });

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('https://ramp-uniformed-repaint.ngrok-free.dev/login', URL::to('/login'));
    }
}
