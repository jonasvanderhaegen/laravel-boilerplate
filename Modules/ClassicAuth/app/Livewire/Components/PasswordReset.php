<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\ClassicAuth\Actions\ResetPasswordAction;
use Modules\ClassicAuth\Livewire\Forms\PasswordResetForm;
use Modules\Core\Concerns\DispatchesAlerts;
use Modules\Core\Concerns\HasMobileDesktopViews;
use Modules\Core\Exceptions\TooManyRequestsException;
use Modules\Flowbite\Livewire\Layouts\General;

final class PasswordReset extends General
{
    use DispatchesAlerts, HasMobileDesktopViews;

    public PasswordResetForm $form;

    public bool $showPassword = false;

    public bool $showPasswordConfirmation = false;

    /**
     * Initialize component state.
     */
    public function mount(?string $token = null, ?string $email = null): void
    {
        // Initialize rate limit countdown
        $this->form->initRateLimitCountdown('execute', ResetPasswordAction::class);

        // Set token and email from URL parameters
        if ($token) {
            $this->form->token = $token;
        }
        if ($email) {
            $this->form->email = $email;
        }
    }

    /**
     * Handle the password reset form submission.
     */
    public function submit(ResetPasswordAction $action): void
    {
        try {
            // Validate form inputs
            $this->form->validate();

            // Execute password reset action
            $result = $action->execute($this->form->getCredentials());

            // Store session data
            session()->put($result->getSessionData());

            // Show success message
            session()->flash('success', $result->message);

            // Use JavaScript redirect to avoid Livewire CSRF issues
            $this->js("window.location.href = '".e($result->redirectUrl)."'");

        } catch (TooManyRequestsException $e) {
            // Handle rate limiting
            $this->form->secondsUntilReset = $e->secondsUntilAvailable;

            $this->addError('form.password', __('auth.throttle', [
                'seconds' => $e->secondsUntilAvailable,
                'minutes' => ceil($e->minutesUntilAvailable),
            ]));

            // Dispatch error event for rate limiting
            $this->alertError(__('Too many password reset attempts. Please try again later.'));

        } catch (ValidationException $e) {
            // Reset password fields on validation failure
            $this->form->resetForm();

            // Handle the validation error
            if ($e->validator->errors()->has('email')) {
                $this->addError('form.email', $e->validator->errors()->first('email'));
                $this->alertError(__('Invalid password reset link. Please request a new one.'));
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
        return filled($this->form->token)
            && filled($this->form->email)
            && filled($this->form->password)
            && filled($this->form->password_confirmation)
            && ! $this->getErrorBag()->any()
            && $this->form->secondsUntilReset === 0;
    }

    /**
     * Get the appropriate view path based on device type.
     */
    #[Computed]
    public function viewPath(): string
    {
        return 'classicauth::livewire.components.password-reset';
    }

    /**
     * Handle real-time validation.
     */
    public function updated(string $propertyName): void
    {
        // Only validate specific fields on blur/change
        if (in_array($propertyName, ['form.email', 'form.password', 'form.password_confirmation'], true)) {
            $this->validateOnly($propertyName);
        }
    }

    /**
     * Navigate to password request page.
     */
    public function requestNewLink(): void
    {
        $this->redirect(route('password.request'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        // Update the countdown on each render
        if ($this->form->secondsUntilReset > 0) {
            $this->form->initRateLimitCountdown('execute', ResetPasswordAction::class);
        }

        return view($this->viewPath)
            ->title(__('Reset Password'));
    }
}
