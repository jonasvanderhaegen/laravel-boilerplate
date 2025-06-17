<?php

declare(strict_types=1);

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', '../Modules/*/tests/Feature', '../Modules/*/tests/Unit');

// pest()->project()->github('vendor/repository');
