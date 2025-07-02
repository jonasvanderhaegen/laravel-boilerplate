<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\ClassicAuth\Actions\ResendVerificationEmailAction;
use Modules\Core\Concerns\DispatchesAlerts;
use Modules\Core\Concerns\HasMobileDesktopViews;
use Modules\Core\Exceptions\TooManyRequestsException;
use Modules\Flowbite\Livewire\Layouts\General;

final class VerifyEmail extends General
{
    use DispatchesAlerts, HasMobileDesktopViews;

    public int $secondsUntilReset = 0;

    public bool $emailSent = false;

    /**
     * Initialize component state.
     */
    public function mount(): void
    {
        // Redirect if not authenticated
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        // Redirect if already verified
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirect($this->getDefaultRedirect(), navigate: true);
        }

        // Check if email was just sent from session
        if (session()->has('verification.sent')) {
            $this->emailSent = true;
            session()->forget('verification.sent');
        }
    }

    /**
     * Handle resending verification email.
     */
    public function resendVerificationEmail(ResendVerificationEmailAction $action): void
    {
        try {
            $result = $action->execute();

            if ($result) {
                $this->emailSent = true;
                session()->flash('verification.sent', true);
                $this->alertSuccess(__('A new verification link has been sent to your email address.'));
            } else {
                $this->alertInfo(__('Your email is already verified or there was an issue.'));
            }

        } catch (TooManyRequestsException $e) {
            // Handle rate limiting
            $this->secondsUntilReset = $e->secondsUntilAvailable;

            $this->alertError(__('auth.throttle', [
                'seconds' => $e->secondsUntilAvailable,
                'minutes' => ceil($e->minutesUntilAvailable),
            ]));
        }
    }

    /**
     * Handle logout.
     */
    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(route('login'), navigate: true);
    }

    /**
     * Get the user's email address.
     */
    #[Computed]
    public function userEmail(): string
    {
        return Auth::user()?->email ?? '';
    }

    /**
     * Determine if resend is available.
     */
    #[Computed]
    public function canResend(): bool
    {
        return $this->secondsUntilReset === 0;
    }

    /**
     * Get the appropriate view path based on device type.
     */
    #[Computed]
    public function viewPath(): string
    {
        return 'classicauth::livewire.components.verify-email';
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        // Update countdown timer
        if ($this->secondsUntilReset > 0) {
            $this->dispatch('countdown-tick');
        }

        return view($this->viewPath)
            ->title(__('Verify Your Email Address'));
    }

    /**
     * Get default redirect route from config.
     */
    protected function getDefaultRedirect(): string
    {
        $redirect = config('classicauth.defaults.verified_redirect', 'dashboard');

        // If it's a route name, convert to URL
        if (\Illuminate\Support\Facades\Route::has($redirect)) {
            return route($redirect);
        }

        // Otherwise return as-is (could be a path)
        return $redirect;
    }
}
