<?php

declare(strict_types=1);

namespace Modules\Flowbite\Livewire\Pages\Events;

use Illuminate\View\View;
use Modules\Flowbite\Livewire\Layouts\General;

final class Index extends General
{
    public function render(): View
    {
        return view('flowbite::livewire.pages.events.index')
            ->title(__('Events Overview'));
    }
}
