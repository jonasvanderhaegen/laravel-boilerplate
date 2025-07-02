<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
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
        // Initialize rate limit countdown
        // This will check both email-based (if stored in session) and IP-based rate limiting
        $this->form->initRateLimitCountdown('execute', LoginUserAction::class, 'login_email');

        // Pre-fill email if coming from registration or remembered
        if (session()->has('login.email')) {
            $this->form->email = session()->pull('login.email');
        }
    }

    /**
     * Handle the login form submission.
     */
    public function submit(LoginUserAction $action): void
    {
        // Ray: Log form submission
        ray()
            ->label('[Login Component::submit] Login Form Submitted')
            ->table([
                'Email' => $this->form->email,
                'Remember Me' => $this->form->remember ? 'Yes' : 'No',
                'Has Password' => filled($this->form->password) ? 'Yes' : 'No',
            ])
            ->color('purple');

        try {
            // Validate form inputs
            $this->form->validate();

            // Execute login action
            $loginResult = $action->execute($this->form->getCredentials());

            // Ray: Log login result
            ray()
                ->label('[Login Component::submit -> after execute] Login Result Received')
                ->table([
                    'User ID' => $loginResult->user->id,
                    'User Email' => $loginResult->user->email,
                    'Intended URL' => $loginResult->intendedUrl,
                    'Was Remembered' => $loginResult->wasRemembered ? 'Yes' : 'No',
                ])
                ->color('green');

            // Validate intended URL for security
            $intendedUrl = $loginResult->intendedUrl;
            if (! $this->isInternalUrl($intendedUrl)) {
                // Ray: Log external URL prevention
                ray()
                    ->label('[Login Component::submit -> if (!isInternalUrl)] External URL Prevented')
                    ->table([
                        'Original URL' => $intendedUrl,
                        'Redirecting To' => $this->getDefaultRedirect(),
                    ])
                    ->color('orange');

                $intendedUrl = $this->getDefaultRedirect();
            }

            // Regenerate session for security
            session()->regenerate();

            // Store success message
            session()->flash('success', __('Welcome back! You have successfully logged in.'));

            // Use JavaScript redirect to avoid Livewire CSRF issues
            $this->js("window.location.href = '".e($intendedUrl)."'");

        } catch (TooManyRequestsException $e) {
            // Ray: Log rate limit exception
            ray()
                ->label('[Login Component::submit -> catch TooManyRequestsException] ⚠️ Rate Limit Exception')
                ->table([
                    'Email' => $this->form->email,
                    'Seconds Until Reset' => $e->secondsUntilAvailable,
                    'Minutes Until Reset' => ceil($e->minutesUntilAvailable),
                    'Message' => $e->getMessage(),
                ])
                ->color('red');

            // Handle rate limiting
            $this->form->secondsUntilReset = $e->secondsUntilAvailable;

            $this->addError('form.email', __('auth.throttle', [
                'seconds' => $e->secondsUntilAvailable,
                'minutes' => ceil($e->minutesUntilAvailable),
            ]));

            // Dispatch error event for rate limiting
            $this->alertError(__('Too many login attempts. Please try again later.'));

        } catch (ValidationException $e) {
            // Ray: Log validation exception
            ray()
                ->label('[Login Component::submit -> catch ValidationException] ❌ Validation Exception')
                ->table([
                    'Email' => $this->form->email,
                    'Errors' => $e->errors(),
                ])
                ->color('red');

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

        // Ray: Log password visibility toggle
        ray()
            ->label('[Login Component::togglePasswordVisibility] Password Visibility Toggled')
            ->table([
                'Show Password' => $this->showPassword ? 'Yes' : 'No',
            ])
            ->color('gray');
    }

    /**
     * Determine if the form is ready for submission.
     */
    #[Computed]
    public function canSubmit(): bool
    {
        return filled($this->form->email)
            && filled($this->form->password)
            && ! $this->getErrorBag()->any()
            && $this->form->secondsUntilReset === 0;
    }

    /**
     * Get the appropriate view path based on device type.
     */
    #[Computed]
    public function viewPath(): string
    {
        return 'classicauth::livewire.components.login';
    }

    /**
     * Navigate to registration page.
     */
    public function redirectToRegister(): void
    {
        // Ray: Log redirect to register
        ray()
            ->label('[Login Component::redirectToRegister] Redirecting to Register')
            ->table([
                'Email to Preserve' => $this->form->email ?: 'None',
            ])
            ->color('blue');

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
        // Ray: Log redirect to password reset
        ray()
            ->label('[Login Component::redirectToPasswordReset] Redirecting to Password Reset')
            ->table([
                'Email to Preserve' => $this->form->email ?: 'None',
            ])
            ->color('blue');

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
        // Update the countdown on each render
        if ($this->form->secondsUntilReset > 0) {
            $this->form->initRateLimitCountdown('execute', LoginUserAction::class, 'login_email');
        }

        return view($this->viewPath)
            ->title(__('Sign in to your account'));
    }

    public function fillCorrectUser(bool $remember = false): void
    {
        $this->form->remember = $remember;
        $this->form->email = 'jonasvanderh@gmail.com';
        $this->form->password = 'password';
    }

    public function fillIncorrectUser(): void
    {
        $this->form->email = 'jonasvanderh@gmail.com';
        $this->form->password = 'wrong&password';
    }

    /**
     * Get the intended redirect route.
     */
    protected function getIntendedRoute(): string
    {
        $defaultRedirect = $this->getDefaultRedirect();
        $intended = session()->pull('url.intended', $defaultRedirect);

        // Validate the intended URL is internal
        if (! $this->isInternalUrl($intended)) {
            return $defaultRedirect;
        }

        return $intended;
    }

    /**
     * Get default redirect route from config.
     */
    protected function getDefaultRedirect(): string
    {
        $redirect = config('classicauth.defaults.login_redirect', 'dashboard');

        // If it's a route name, convert to URL
        if (\Illuminate\Support\Facades\Route::has($redirect)) {
            return route($redirect);
        }

        // Otherwise return as-is (could be a path)
        return $redirect;
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
