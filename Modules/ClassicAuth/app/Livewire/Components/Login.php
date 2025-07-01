<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Modules\ClassicAuth\Actions\LoginUserAction;
use Modules\ClassicAuth\Livewire\Forms\LoginForm;
use Modules\Core\Concerns\DispatchesAlerts;
use Modules\Core\Concerns\HasMobileDesktopViews;
use Modules\Core\Exceptions\TooManyRequestsException;
use Modules\Flowbite\Livewire\Layouts\General;

final class Login extends General
{
    use DispatchesAlerts, HasMobileDesktopViews;

    public LoginForm $form;

    public bool $showPassword = false;

    /**
     * Initialize component state.
     */
    public function mount(): void
    {
        // Initialize rate limit countdown using the form's method
        $this->form->initRateLimitCountdown('attemptLogin', null, 'login');

        // Pre-fill email if coming from registration or remembered
        if (session()->has('login.email')) {
            $this->form->email = session()->get('login.email');
            session()->forget('login.email');
        }

        // Redirect if already authenticated
        if (Auth::check()) {
            $this->redirect($this->getIntendedRoute(), navigate: true);
        }
    }

    /**
     * Handle the login form submission.
     */
    public function submit(LoginUserAction $action): void
    {
        try {
            // Validate form inputs
            $this->form->validate();

            // Execute login action
            $action->execute($this->form->getCredentials());

            // Handle successful authentication
            $this->handleSuccessfulAuthentication();

        } catch (TooManyRequestsException $e) {
            // Handle rate limiting
            $this->form->secondsUntilReset = $e->secondsUntilAvailable;

            $this->addError('form.email', __('auth.throttle', [
                'seconds' => $e->secondsUntilAvailable,
                'minutes' => ceil($e->minutesUntilAvailable),
            ]));

            // Dispatch error event for rate limiting
            $this->alertError(__('Too many login attempts. Please try again later.'));

        } catch (ValidationException $e) {
            // Reset password field on validation failure
            $this->form->resetForm();

            // Handle the validation error
            if ($e->validator->errors()->has('email')) {
                $this->addError('form.email', $e->validator->errors()->first('email'));
                $this->alertError(__('Invalid credentials. Please try again.'));
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
     * Determine if the form is ready for submission.
     */
    #[Computed]
    public function canSubmit(): bool
    {
        return filled($this->form->email)
            && filled($this->form->password)
            && ! $this->getErrorBag()->any();
    }

    /**
     * Get the appropriate view path based on device type.
     */
    #[Computed]
    public function viewPath(): string
    {
        $base = 'classicauth::livewire.components.login';

        if ($this->isMobile()) {
            return "{$base}.mobile";
        }

        return $base;
    }

    /**
     * Handle real-time validation.
     */
    public function updated(string $propertyName): void
    {
        // Only validate specific fields on blur/change
        if (in_array($propertyName, ['form.email', 'form.password'], true)) {
            $this->validateOnly($propertyName);
        }
    }

    /**
     * Navigate to registration page.
     */
    public function redirectToRegister(): void
    {
        // Preserve email if entered
        if (filled($this->form->email)) {
            session()->flash('registration.email', $this->form->email);
            $this->alertInfo(__('Please complete your registration.'));
        }

        $this->redirect(route('register'), navigate: true);
    }

    /**
     * Navigate to password reset page.
     */
    public function redirectToPasswordReset(): void
    {
        // Preserve email if entered
        if (filled($this->form->email)) {
            session()->flash('password.email', $this->form->email);
            $this->alertInfo(__('Enter your email to reset your password.'));
        }

        $this->redirect(route('password.request'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view($this->viewPath)
            ->title(__('Sign in to your account'));
    }

    public function fillCorrectUser(bool $remember = false): void
    {
        $this->form->remember = $remember;
        $this->form->email = 'test@example.com';
        $this->form->password = 'password';
    }

    public function fillIncorrectUser(): void
    {
        $this->form->email = 'test@example.com';
        $this->form->password = 'wrong&password';
    }

    /**
     * Handle successful authentication redirect.
     */
    protected function handleSuccessfulAuthentication(): void
    {
        $intended = $this->getIntendedRoute();

        // Dispatch success event
        $this->alertSuccess(__('Welcome back! You have successfully logged in.'));

        // Redirect with navigation
        $this->redirect($intended, navigate: true);
    }

    /**
     * Get the intended redirect route.
     */
    protected function getIntendedRoute(): string
    {
        $intended = session()->pull('url.intended', route('flowbite.dashboard'));

        // Validate the intended URL is internal
        if (! $this->isInternalUrl($intended)) {
            return route('flowbite.dashboard');
        }

        return $intended;
    }

    /**
     * Check if a URL is internal to the application.
     */
    protected function isInternalUrl(string $url): bool
    {
        $appUrl = config('app.url');

        return str_starts_with($url, (string) $appUrl) || str_starts_with($url, '/');
    }
}
