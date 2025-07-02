<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Livewire\Forms;

use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Modules\ClassicAuth\DataTransferObjects\PasswordResetCredentials;
use Modules\Core\Concerns\RateLimitDurations;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Rules\StrictEmailDomain;

/**
 * Livewire form for password reset validation.
 *
 * This form handles only validation and data collection.
 * Business logic is delegated to the ResetPasswordAction.
 */
final class PasswordResetForm extends Form
{
    use RateLimitDurations, WithRateLimiting;

    #[Validate]
    public string $token = '';

    #[Validate]
    public string $email = '';

    #[Validate]
    public string $password = '';

    #[Validate]
    public string $password_confirmation = '';

    #[Locked]
    public int $secondsUntilReset = 0;

    /**
     * Define validation rules for the form.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'token' => [
                'required',
                'string',
            ],
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
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', // At least one lowercase, uppercase, and number
            ],
            'password_confirmation' => [
                'required',
                'string',
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
            'token.required' => __('validation.required', ['attribute' => __('Token')]),
            'email.required' => __('validation.required', ['attribute' => __('Email')]),
            'email.email' => __('validation.email', ['attribute' => __('Email')]),
            'password.required' => __('validation.required', ['attribute' => __('Password')]),
            'password.min' => __('validation.min.string', ['attribute' => __('Password'), 'min' => 8]),
            'password.confirmed' => __('validation.confirmed', ['attribute' => __('Password')]),
            'password.regex' => __('auth.password_requirements'),
            'password_confirmation.required' => __('validation.required', ['attribute' => __('Password confirmation')]),
        ];
    }

    /**
     * Get credentials as a DTO.
     */
    public function getCredentials(): PasswordResetCredentials
    {
        return new PasswordResetCredentials(
            email: $this->email,
            token: $this->token,
            password: $this->password
        );
    }

    /**
     * Reset the form to its initial state.
     */
    public function resetForm(): void
    {
        $this->reset(['password', 'password_confirmation']);
        $this->resetValidation();
    }
}
