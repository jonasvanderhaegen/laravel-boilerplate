<?php

declare(strict_types=1);

namespace Modules\Flowbite\Livewire\Pages;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Modules\Flowbite\Livewire\Layouts\Dashboard as Layout;

final class Dashboard extends Layout
{
    public function render(): \Illuminate\Contracts\View\View|Factory|View
    {
        return view('flowbite::livewire.pages.dashboard');
    }
}
