<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;

final class LogoutAction
{
    public function __invoke(): RedirectResponse|Redirector
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }
}
