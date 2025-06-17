<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;
use Modules\Core\Concerns\WithRateLimiting;

final class LoginForm extends Form
{
    use WithRateLimiting;

    #[Validate('required|string')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;
}
