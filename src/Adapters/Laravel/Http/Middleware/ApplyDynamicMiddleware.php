<?php

declare(strict_types=1);

namespace Pan\Adapters\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Pan\PanConfiguration;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class ApplyDynamicMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredMiddleware = PanConfiguration::instance()->toArray()['middleware'];

        if (empty($configuredMiddleware)) {
            return $next($request);
        }

        // Create a pipeline to run the configured middleware
        /** @var Response $response */
        $response = app(Pipeline::class)
            ->send($request)
            ->through($configuredMiddleware)
            ->then($next);

        return $response;
    }
}
