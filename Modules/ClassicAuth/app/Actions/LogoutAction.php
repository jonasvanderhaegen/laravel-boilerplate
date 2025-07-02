<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Modules\ClassicAuth\Events\Login\UserLoggedOut;

final class LogoutAction
{
    public function __invoke(): RedirectResponse|Redirector
    {
        $user = Auth::user();
        $ipAddress = request()->ip() ?? 'unknown';
        $userAgent = request()->userAgent() ?? 'unknown';
        $sessionId = session()->getId();

        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        // Dispatch logout event
        event(new UserLoggedOut($user, $ipAddress, $userAgent, $sessionId));

        $redirect = config('classicauth.defaults.logout_redirect', '/');

        return redirect($redirect);
    }
}
