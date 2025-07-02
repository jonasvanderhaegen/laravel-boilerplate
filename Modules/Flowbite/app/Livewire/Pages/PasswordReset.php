<?php

declare(strict_types=1);

namespace Modules\Flowbite\Livewire\Pages;

use Illuminate\View\View;
use Modules\Flowbite\Livewire\Layouts\General;

final class PasswordReset extends General
{
    public ?string $token = null;

    public ?string $email = null;

    public function mount(?string $token = null): void
    {
        $this->token = $token;
        $this->email = request()->query('email');
    }

    public function render(): View
    {
        return view('flowbite::livewire.pages.password-reset')
            ->title(__('Reset Password'));
    }
}
