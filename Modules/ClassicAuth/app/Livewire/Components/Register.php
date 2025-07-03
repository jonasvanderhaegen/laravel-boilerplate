<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Livewire\Components;

use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\ClassicAuth\Actions\RegisterUserAction;
use Modules\ClassicAuth\Livewire\Forms\RegisterForm;
use Modules\Core\Concerns\DispatchesAlerts;
use Modules\Core\Concerns\HasMobileDesktopViews;
use Modules\Core\Exceptions\TooManyRequestsException;
use Modules\Flowbite\Livewire\Layouts\General;

final class Register extends General
{
    use DispatchesAlerts, HasMobileDesktopViews;

    public RegisterForm $form;

    public bool $showPassword = false;

    public bool $showPasswordConfirmation = false;

    /**
     * Initialize component state.
     */
    public function mount(): void
    {
        // Initialize rate limit countdown
        $this->form->initRateLimitCountdown('execute', RegisterUserAction::class);

        // Pre-fill email if coming from login or other page
        if (session()->has('registration.email')) {
            $this->form->email = session()->pull('registration.email');
        }
    }

    /**
     * Handle the registration form submission.
     */
    public function submit(RegisterUserAction $action): void
    {
        try {
            // Validate form inputs
            $this->form->validate();

            // Execute registration action
            $registerResult = $action->execute($this->form->getCredentials());

            // Regenerate session for security
            session()->regenerate();

            // Store success message
            session()->flash('success', __('Welcome! Your account has been created successfully.'));

            // Use JavaScript redirect to avoid Livewire CSRF issues
            $this->js("window.location.href = '".e($registerResult->redirectUrl)."'");

        } catch (TooManyRequestsException $e) {
            // Handle rate limiting
            $this->form->secondsUntilReset = $e->secondsUntilAvailable;

            $this->addError('form.email', __('auth.throttle', [
                'seconds' => $e->secondsUntilAvailable,
                'minutes' => ceil($e->minutesUntilAvailable),
            ]));

            // Dispatch error event for rate limiting
            $this->alertError(__('Too many registration attempts. Please try again later.'));

        } catch (ValidationException $e) {
            // Reset password fields on validation failure
            $this->form->resetForm();

            // Handle the validation error
            if ($e->validator->errors()->has('email')) {
                $this->addError('form.email', $e->validator->errors()->first('email'));
                $this->alertError(__('This email is already registered. Please use a different email.'));
            } else {
                // Re-throw for other validation errors
                throw $e;
            }
        }
    }

    /**
     * Toggle password visibility.
     */
    public function togglePasswordVisibility(): void
    {
        $this->showPassword = ! $this->showPassword;
    }

    /**
     * Toggle password confirmation visibility.
     */
    public function togglePasswordConfirmationVisibility(): void
    {
        $this->showPasswordConfirmation = ! $this->showPasswordConfirmation;
    }

    /**
     * Determine if the form is ready for submission.
     */
    #[Computed]
    public function canSubmit(): bool
    {
        return filled($this->form->name)
            && filled($this->form->email)
            && filled($this->form->password)
            && filled($this->form->password_confirmation)
            && $this->form->terms
            && ! $this->getErrorBag()->any()
            && $this->form->secondsUntilReset === 0;
    }

    /**
     * Get the appropriate view path based on device type.
     */
    #[Computed]
    public function viewPath(): string
    {
        return 'classicauth::livewire.components.register';
    }

    /**
     * Handle real-time validation.
     */
    public function updated(string $propertyName): void
    {
        // Only validate specific fields on blur/change
        if (in_array($propertyName, ['form.name', 'form.email', 'form.password', 'form.password_confirmation'], true)) {
            $this->validateOnly($propertyName);
        }
    }

    /**
     * Navigate to login page.
     */
    public function redirectToLogin(): void
    {
        // Preserve email if entered
        if (filled($this->form->email)) {
            session()->flash('login.email', $this->form->email);
            $this->alertInfo(__('Please log in to your account.'));
        }

        $this->redirect(route('login'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        // Update the countdown on each render
        if ($this->form->secondsUntilReset > 0) {
            $this->form->initRateLimitCountdown('execute', RegisterUserAction::class);
        }

        return view($this->viewPath)
            ->title(__('Create an account'));
    }

    /**
     * Get default redirect route from config.
     */
    protected function getDefaultRedirect(): string
    {
        $redirect = config('classicauth.defaults.register_redirect', 'dashboard');

        // If it's a route name, convert to URL
        if (\Illuminate\Support\Facades\Route::has($redirect)) {
            return route($redirect);
        }

        // Otherwise return as-is (could be a path)
        return $redirect;
    }
}
