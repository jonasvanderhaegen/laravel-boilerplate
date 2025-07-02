<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Livewire\Forms;

use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Modules\ClassicAuth\DataTransferObjects\RegisterCredentials;
use Modules\Core\Concerns\RateLimitDurations;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Rules\StrictEmailDomain;

/**
 * Livewire form for registration validation.
 *
 * This form handles only validation and data collection.
 * Business logic is delegated to the RegisterUserAction.
 */
final class RegisterForm extends Form
{
    use RateLimitDurations, WithRateLimiting;

    #[Validate]
    public string $name = '';

    #[Validate]
    public string $email = '';

    #[Validate]
    public string $password = '';

    #[Validate]
    public string $password_confirmation = '';

    #[Validate]
    public bool $terms = false;

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
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\pL\s\-\.]+$/u', // Letters, spaces, hyphens, dots
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'lowercase',
                'max:255',
                'unique:users,email',
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
            'terms' => [
                'required',
                'accepted',
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
            'name.required' => __('validation.required', ['attribute' => __('Name')]),
            'name.min' => __('validation.min.string', ['attribute' => __('Name'), 'min' => 2]),
            'name.regex' => __('validation.regex', ['attribute' => __('Name')]),
            'email.required' => __('validation.required', ['attribute' => __('Email')]),
            'email.email' => __('validation.email', ['attribute' => __('Email')]),
            'email.unique' => __('validation.unique', ['attribute' => __('Email')]),
            'password.required' => __('validation.required', ['attribute' => __('Password')]),
            'password.min' => __('validation.min.string', ['attribute' => __('Password'), 'min' => 8]),
            'password.confirmed' => __('validation.confirmed', ['attribute' => __('Password')]),
            'password.regex' => __('auth.password_requirements'),
            'password_confirmation.required' => __('validation.required', ['attribute' => __('Password confirmation')]),
            'terms.required' => __('validation.required', ['attribute' => __('Terms')]),
            'terms.accepted' => __('validation.accepted', ['attribute' => __('Terms')]),
        ];
    }

    /**
     * Get credentials as a DTO.
     */
    public function getCredentials(): RegisterCredentials
    {
        return new RegisterCredentials(
            name: $this->name,
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
        $this->reset(['password', 'password_confirmation']);
        $this->resetValidation();
    }
}
