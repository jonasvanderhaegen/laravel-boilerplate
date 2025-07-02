<?php

declare(strict_types=1);

namespace Modules\Flowbite\Livewire\Pages;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\ClassicAuth\Actions\VerifyEmailAction;
use Modules\Core\Concerns\DispatchesAlerts;
use Modules\Core\Exceptions\TooManyRequestsException;
use Modules\Flowbite\Livewire\Layouts\General;

final class VerifyEmailConfirmation extends General
{
    use DispatchesAlerts;

    public ?int $id = null;
    public ?string $hash = null;
    public bool $verified = false;
    public bool $error = false;

    public function mount(int $id, string $hash, VerifyEmailAction $action): void
    {
        $this->id = $id;
        $this->hash = $hash;

        // Ensure user is authenticated
        if (!Auth::check()) {
            session()->flash('error', __('Please log in to verify your email address.'));
            $this->redirect(route('login'), navigate: true);
            return;
        }

        // Ensure the authenticated user matches the verification request
        if (Auth::id() !== $id) {
            session()->flash('error', __('This verification link is not for your account.'));
            $this->redirect(route('verification.notice'), navigate: true);
            return;
        }

        try {
            // Attempt to verify the email
            $result = $action->execute($id, $hash);

            if ($result) {
                $this->verified = true;
                session()->flash('success', __('Your email has been successfully verified!'));
                
                // Redirect after a short delay
                $this->dispatch('redirect-after-verification');
            } else {
                $this->error = true;
                $this->alertError(__('The verification link is invalid or has expired.'));
            }

        } catch (TooManyRequestsException $e) {
            $this->error = true;
            $this->alertError(__('Too many verification attempts. Please try again later.'));
        }
    }

    public function redirectToDashboard(): void
    {
        $redirect = config('classicauth.defaults.verified_redirect', 'dashboard');
        
        if (\Illuminate\Support\Facades\Route::has($redirect)) {
            $this->redirect(route($redirect), navigate: true);
        } else {
            $this->redirect($redirect, navigate: true);
        }
    }

    public function render(): View
    {
        return view('flowbite::livewire.pages.verify-email-confirmation')
            ->title(__('Email Verification'));
    }
}
