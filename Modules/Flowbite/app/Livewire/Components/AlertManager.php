<?php

declare(strict_types=1);

namespace Modules\Flowbite\Livewire\Components;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

final class AlertManager extends Component
{
    public array $alerts = [];

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function mount(): void
    {
        // Check for any session flash messages on mount
        $this->checkSessionAlerts();
    }

    #[On('alert-added')]
    public function addAlert(string $type, string $message, ?string $id = null): void
    {
        $id ??= uniqid('alert-');

        $this->alerts[$id] = [
            'id' => $id,
            'type' => $type,
            'message' => $message,
            'dismissible' => true,
        ];
    }

    #[On('alert-success')]
    public function addSuccess(string $message): void
    {
        $this->addAlert('success', $message);
    }

    #[On('alert-error')]
    public function addErrorAlert(string $message): void
    {
        $this->addAlert('error', $message);
    }

    #[On('alert-warning')]
    public function addWarning(string $message): void
    {
        $this->addAlert('warning', $message);
    }

    #[On('alert-info')]
    public function addInfo(string $message): void
    {
        $this->addAlert('info', $message);
    }

    public function dismissAlert(string $id): void
    {
        unset($this->alerts[$id]);
    }

    public function dismissAll(): void
    {
        $this->alerts = [];
    }

    public function render(): View|Factory
    {
        return view('flowbite::livewire.components.alert-manager');
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function checkSessionAlerts(): void
    {
        // Check for session flash messages
        if (session()->has('success')) {
            $this->addSuccess(session()->get('success'));
        }

        if (session()->has('error')) {
            $this->addErrorAlert(session()->get('error'));
        }

        if (session()->has('warning')) {
            $this->addWarning(session()->get('warning'));
        }

        if (session()->has('info')) {
            $this->addInfo(session()->get('info'));
        }
    }
}
