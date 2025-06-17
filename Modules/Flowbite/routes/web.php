<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:info-pages'])->group(function () {

    Route::get('/', Modules\Flowbite\Livewire\Pages\Home::class)
        ->name('homepage');

    Route::get('company', Modules\Flowbite\Livewire\Pages\Company::class)
        ->name('company');

    Route::get('market-place', Modules\Flowbite\Livewire\Pages\Marketplace::class)
        ->name('marketplace');

    Route::get('features', Modules\Flowbite\Livewire\Pages\Features::class)
        ->name('features');

    Route::get('team', Modules\Flowbite\Livewire\Pages\Team::class)
        ->name('team');

    Route::get('blog', Modules\Flowbite\Livewire\Pages\Blog\Index::class)
        ->name('blog.index');

    Route::get('events', Modules\Flowbite\Livewire\Pages\Events\Index::class)
        ->name('events.index');

    Route::get('frequently-asked-questions', Modules\Flowbite\Livewire\Pages\Faq::class)
        ->name('faq');
});

Route::get('contact', Modules\Flowbite\Livewire\Pages\Contact::class)
    ->middleware('throttle:public-form')
    ->name('contact');
