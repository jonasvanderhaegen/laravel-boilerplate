<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Livewire\Forms;

use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Modules\ClassicAuth\DataTransferObjects\LoginCredentials;
use Modules\Core\Concerns\RateLimitDurations;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Rules\StrictEmailDomain;

/**
 * Livewire form for login validation.
 *
 * This form handles only validation and data collection.
 * Business logic is delegated to the LoginUserAction.
 */
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
    public int $secondsUntilReset = 0;

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
     * Get credentials as a DTO.
     */
    public function getCredentials(): LoginCredentials
    {
        return new LoginCredentials(
            email: $this->email,
            password: $this->password,
            remember: $this->remember
        );
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
