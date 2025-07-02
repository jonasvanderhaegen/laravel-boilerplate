<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to enforce minimum execution time for requests.
 *
 * This prevents timing attacks on sensitive endpoints by ensuring
 * consistent response times regardless of the operation outcome.
 */
final class EnforceMinimumExecutionTime
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?int $minimumMs = null): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        // Use provided minimum or fall back to config
        $minimumMicroseconds = ($minimumMs ?? config('classicauth.security.auth_min_time_ms', 300)) * 1000;

        $elapsedMicroseconds = (microtime(true) - $startTime) * 1000000;

        if ($elapsedMicroseconds < $minimumMicroseconds) {
            usleep((int) ($minimumMicroseconds - $elapsedMicroseconds));
        }

        return $response;
    }
}
