<?php

namespace Modules\Core\Concerns;

trait DispatchesAlerts
{
    public function alertSuccess(string $message): void
    {
        $this->dispatch('alert-success', message: $message);
    }

    public function alertError(string $message): void
    {
        $this->dispatch('alert-error', message: $message);
    }

    public function alertWarning(string $message): void
    {
        $this->dispatch('alert-warning', message: $message);
    }

    public function alertInfo(string $message): void
    {
        $this->dispatch('alert-info', message: $message);
    }

    public function alert(string $type, string $message): void
    {
        $this->dispatch('alert-added', type: $type, message: $message);
    }
}
