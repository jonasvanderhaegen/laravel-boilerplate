<?php

declare(strict_types=1);

return [
    'channels' => [
        'auth' => [
            'driver' => 'daily',
            'path' => storage_path('logs/auth.log'),
            'level' => 'info',
            'days' => 14,
        ],
    ],
];
