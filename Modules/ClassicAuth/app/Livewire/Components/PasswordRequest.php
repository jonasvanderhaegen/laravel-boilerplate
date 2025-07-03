<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Livewire\Components;

use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\ClassicAuth\Actions\RequestPasswordResetAction;
use Modules\ClassicAuth\Livewire\Forms\PasswordRequestForm;
use Modules\Core\Concerns\DispatchesAlerts;
use Modules\Core\Concerns\HasMobileDesktopViews;
use Modules\Core\Exceptions\TooManyRequestsException;
use Modules\Flowbite\Livewire\Layouts\General;

final class PasswordRequest extends General
{
    use DispatchesAlerts, HasMobileDesktopViews;

    public PasswordRequestForm $form;

    public bool $emailSent = false;

    /**
     * Initialize component state.
     */
    public function mount(): void
    {
        // Initialize rate limit countdown
        $this->form->initRateLimitCountdown('execute', RequestPasswordResetAction::class);

        // Pre-fill email if coming from login page
        if (session()->has('password.email')) {
            $this->form->email = session()->pull('password.email');
        }
    }

    /**
     * Handle the password reset request form submission.
     */
    public function submit(RequestPasswordResetAction $action): void
    {
        try {
            // Validate form inputs
            $this->form->validate();

            // Execute password reset request action
            $result = $action->execute($this->form->getCredentials());

            // Mark as sent
            $this->emailSent = true;

            // Store session data
            session()->put($result->getSessionData());

            // Show success message
            $this->alertSuccess($result->message);

        } catch (TooManyRequestsException $e) {
            // Handle rate limiting
            $this->form->secondsUntilReset = $e->secondsUntilAvailable;

            $this->addError('form.email', __('auth.throttle', [
                'seconds' => $e->secondsUntilAvailable,
                'minutes' => ceil($e->minutesUntilAvailable),
            ]));

            // Dispatch error event for rate limiting
            $this->alertError(__('Too many password reset attempts. Please try again later.'));
        }
    }

    /**
     * Determine if the form is ready for submission.
     */
    #[Computed]
    public function canSubmit(): bool
    {
        return filled($this->form->email)
            && ! $this->getErrorBag()->any()
            && $this->form->secondsUntilReset === 0
            && ! $this->emailSent;
    }

    /**
     * Get the appropriate view path based on device type.
     */
    #[Computed]
    public function viewPath(): string
    {
        return 'classicauth::livewire.components.password-request';
    }

    /**
     * Handle real-time validation.
     */
    public function updated(string $propertyName): void
    {
        // Only validate email field on blur/change
        if ($propertyName === 'form.email') {
            $this->validateOnly($propertyName);
        }
    }

    /**
     * Navigate to login page.
     */
    public function redirectToLogin(): void
    {
        $this->redirect(route('login'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        // Update the countdown on each render
        if ($this->form->secondsUntilReset > 0) {
            $this->form->initRateLimitCountdown('execute', RequestPasswordResetAction::class);
        }

        return view($this->viewPath)
            ->title(__('Reset your password'));
    }
}
