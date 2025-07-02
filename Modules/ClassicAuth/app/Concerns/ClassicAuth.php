<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Concerns;

trait ClassicAuth
{
    public function initializeClassicAuth(): void
    {
        $this->mergeCasts([
            'last_login_at' => 'datetime',
        ]);

        $this->mergeFillable([
            'last_login_at',
            'last_login_ip',
        ]);
    }
}
