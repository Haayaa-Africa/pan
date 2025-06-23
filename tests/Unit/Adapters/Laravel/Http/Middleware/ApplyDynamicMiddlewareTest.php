<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Pan\Adapters\Laravel\Http\Middleware\ApplyDynamicMiddleware;
use Pan\PanConfiguration;

beforeEach(function (): void {
    PanConfiguration::reset();
});

it('applies no middleware when none configured', function (): void {
    $middleware = new ApplyDynamicMiddleware;
    $request = Request::create('/test');

    $response = $middleware->handle($request, fn($request): \Illuminate\Http\Response => new Response('success'));

    expect($response->getContent())->toBe('success');
});

it('applies configured middleware through pipeline', function (): void {
    // Create a test middleware class
    $testMiddleware = new class
    {
        public function handle(Request $request, \Closure $next)
        {
            $response = $next($request);
            $response->headers->set('X-Test-Header', 'middleware-applied');

            return $response;
        }
    };

    // Bind the middleware to the container
    app()->bind('test-middleware', fn(): object => $testMiddleware);

    PanConfiguration::middleware(['test-middleware']);

    $middleware = new ApplyDynamicMiddleware;
    $request = Request::create('/test');

    $response = $middleware->handle($request, fn($request): \Illuminate\Http\Response => new Response('success'));

    expect($response->getContent())->toBe('success');
    expect($response->headers->get('X-Test-Header'))->toBe('middleware-applied');
});

it('applies multiple middleware in order', function (): void {
    // Create test middleware classes
    $firstMiddleware = new class
    {
        public function handle(Request $request, \Closure $next)
        {
            $request->attributes->set('first', 'applied');

            return $next($request);
        }
    };

    $secondMiddleware = new class
    {
        public function handle(Request $request, \Closure $next)
        {
            $response = $next($request);
            $response->headers->set('X-Second', 'applied');

            return $response;
        }
    };

    // Bind the middleware to the container
    app()->bind('first-middleware', fn(): object => $firstMiddleware);

    app()->bind('second-middleware', fn(): object => $secondMiddleware);

    PanConfiguration::middleware(['first-middleware', 'second-middleware']);

    $middleware = new ApplyDynamicMiddleware;
    $request = Request::create('/test');

    $response = $middleware->handle($request, function ($request): \Illuminate\Http\Response {
        $content = 'first:'.$request->attributes->get('first', 'not-set');

        return new Response($content);
    });

    expect($response->getContent())->toBe('first:applied');
    expect($response->headers->get('X-Second'))->toBe('applied');
});
