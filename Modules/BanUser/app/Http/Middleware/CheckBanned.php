<?php

declare(strict_types=1);

namespace Modules\BanUser\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\BanUser\Services\BanCheckService;
use Modules\BanUser\Events\BannedUserAttempted;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware to check if authenticated user is banned.
 */
final class CheckBanned
{
    public function __construct(
        private readonly BanCheckService $banCheckService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($user = Auth::user()) {
            // Check if user is banned
            if ($this->banCheckService->isUserBanned($user->id)) {
                $ban = $this->banCheckService->getUserBanDetails($user);
                
                // Log the attempt
                event(new BannedUserAttempted(
                    'authenticated_access',
                    $user->email,
                    $request->ip() ?? 'unknown',
                    $request->userAgent() ?? 'unknown',
                    $ban
                ));

                // Log out the user
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect with error message
                return redirect()->route('login')
                    ->with('error', 'Your account has been suspended. Reason: ' . ($ban?->reason ?? 'Terms violation'));
            }
        }

        return $next($request);
    }
}
