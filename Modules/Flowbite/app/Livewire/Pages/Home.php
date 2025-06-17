<?php

declare(strict_types=1);

namespace Modules\Flowbite\Livewire\Pages;

use Illuminate\View\View;
use Modules\Flowbite\Livewire\Layouts\General;

final class Home extends General
{
    public function render(): View
    {
        return view('flowbite::livewire.pages.home')
            ->title(__('Flowbite Homepage'));
    }
}
