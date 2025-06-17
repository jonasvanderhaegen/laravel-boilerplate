<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Livewire\Components;

use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\ClassicAuth\Livewire\Forms\LoginForm;
use Modules\Core\Concerns\HasMobileDesktopViews;

final class Login extends Component
{
    use HasMobileDesktopViews;

    public LoginForm $form;

    public bool $showPassword = false;

    #[Computed]
    public function isFormValid(): bool
    {
        return $this->isMobile() || ! $this->getErrorBag()->any()
            && $this->form->email !== ''
            && $this->form->password !== '';
    }

    public function updated(string $field): void
    {
        $this->form->validateOnly($field);
    }

    public function submit(): void
    {
        ray($this->form->email, $this->form->getErrorBag()->all());
    }

    public function render(): View
    {
        return view('classicauth::livewire.components.login');
    }
}
