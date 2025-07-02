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

    #[Validate(['required', 'email:rfc,dns', 'lowercase', 'max:255', new StrictEmailDomain()])]
    public string $email = '';

    #[Validate(['required', 'string', 'min:8'])]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    #[Locked]
    public int $secondsUntilReset = 0;

    /**
     * Override validate to add Ray logging
     */
    public function validate($rules = null, $messages = [], $attributes = [])
    {
        // Ray: Log form validation attempt
        ray()
            ->label('[LoginForm::validate] Login Form Validation')
            ->table([
                'Email' => $this->email,
                'Has Password' => filled($this->password) ? 'Yes' : 'No',
                'Remember' => $this->remember ? 'Yes' : 'No',
            ])
            ->color('gray');

        try {
            $result = parent::validate($rules, $messages, $attributes);

            // Ray: Log validation success
            ray()
                ->label('[LoginForm::validate -> success] ✅ Login Form Validation Passed')
                ->color('green');

            return $result;
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ray: Log validation failure
            ray()
                ->label('[LoginForm::validate -> catch ValidationException] ❌ Login Form Validation Failed')
                ->table([
                    'Errors' => $e->errors(),
                ])
                ->color('red');

            throw $e;
        }
    }

    /**
     * Define validation rules for the form.
     *
     * @return array<string, mixed>
     */
    /*
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
    */

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
        // Ray: Log credential creation
        ray()
            ->label('[LoginForm::getCredentials] Creating Login Credentials DTO')
            ->table([
                'Email' => $this->email,
                'Remember' => $this->remember ? 'Yes' : 'No',
            ])
            ->color('gray');

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
        // Ray: Log form reset
        ray()
            ->label('[LoginForm::resetForm] Login Form Reset')
            ->table([
                'Email Preserved' => $this->email,
            ])
            ->color('gray');

        $this->reset('password');
        $this->resetValidation();
    }
}
