<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Livewire\Forms;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Modules\Core\Concerns\RateLimitDurations;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Exceptions\TooManyRequestsException;
use Modules\Core\Rules\StrictEmailDomain;

final class LoginForm extends Form
{
    use RateLimitDurations, WithRateLimiting;

    #[Validate]
    public string $email = '';

    #[Validate]
    public string $password = '';

    #[Validate]
    public bool $remember = false;

    #[Locked]
    public int $maxAttempts = 5;

    #[Locked]
    public int $decaySeconds = 60;

    #[Locked]
    public int $maxEmailAttempts = 15;

    #[Locked]
    public int $emailDecaySeconds = 3600;

    /**
     * Define validation rules for the form.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'lowercase',
                'max:255',
                new StrictEmailDomain(),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
            ],
            'remember' => [
                'boolean',
            ],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => __('validation.required', ['attribute' => __('Email')]),
            'email.email' => __('validation.email', ['attribute' => __('Email')]),
            'password.required' => __('validation.required', ['attribute' => __('Password')]),
            'password.min' => __('validation.min.string', ['attribute' => __('Password'), 'min' => 8]),
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     * @throws TooManyRequestsException
     */
    public function attemptLogin(): void
    {
        // Validate inputs
        $this->validate();

        // IP-based rate limiting: max attempts per decay period
        try {
            $this->rateLimit($this->maxAttempts, $this->decaySeconds);
        } catch (TooManyRequestsException $e) {
            $this->reset('password');
            throw $e;
        }

        // Try authentication
        if (! Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            // On failure, apply email-based rate limiting
            try {
                $this->rateLimitByEmail(
                    $this->maxEmailAttempts,
                    $this->longDuration(90, $this->emailDecaySeconds),
                    $this->email,
                    'login'
                );
            } catch (TooManyRequestsException $e) {
                $this->reset('password');
                throw $e;
            }

            // Throw validation exception for invalid credentials
            throw ValidationException::withMessages([
                'form.email' => __('auth.failed'),
            ]);
        }

        // On success, clear rate limiters
        $this->handleSuccessfulLogin();
    }

    /**
     * Handle a successful authentication attempt.
     */
    public function handleSuccessfulLogin(): void
    {
        // Clear rate limiters
        $this->clearRateLimiter();
        $this->clearRateLimiter('attemptLogin');

        // Regenerate session for security
        request()->session()->regenerate();

        // Clear any lingering authentication data
        session()->forget(['login.email', 'login.attempts']);
    }

    /**
     * Reset the form to its initial state.
     */
    public function resetForm(): void
    {
        $this->reset('password');
        $this->resetValidation();
    }
}
