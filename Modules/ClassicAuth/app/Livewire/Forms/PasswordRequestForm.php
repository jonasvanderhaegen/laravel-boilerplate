<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Livewire\Forms;

use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Modules\ClassicAuth\DataTransferObjects\PasswordResetRequestCredentials;
use Modules\Core\Concerns\RateLimitDurations;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Rules\StrictEmailDomain;

/**
 * Livewire form for password reset request validation.
 *
 * This form handles only validation and data collection.
 * Business logic is delegated to the RequestPasswordResetAction.
 */
final class PasswordRequestForm extends Form
{
    use RateLimitDurations, WithRateLimiting;

    #[Validate]
    public string $email = '';

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
        ];
    }

    /**
     * Get credentials as a DTO.
     */
    public function getCredentials(): PasswordResetRequestCredentials
    {
        return new PasswordResetRequestCredentials(
            email: $this->email
        );
    }

    /**
     * Reset the form to its initial state.
     */
    public function resetForm(): void
    {
        $this->reset('email');
        $this->resetValidation();
    }
}
