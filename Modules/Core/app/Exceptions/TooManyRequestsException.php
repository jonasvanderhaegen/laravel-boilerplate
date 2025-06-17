<?php

declare(strict_types=1);

namespace Modules\Core\Exceptions;

use Exception;

final class TooManyRequestsException extends Exception
{
    public int $minutesUntilAvailable;

    public function __construct(
        public string $component,
        public string $method,
        public string $ip,
        public int $secondsUntilAvailable,
    ) {
        // ceil(...) returns float, so cast to int:
        $this->minutesUntilAvailable = (int) ceil($this->secondsUntilAvailable / 60);

        parent::__construct(sprintf(
            'Too many requests from [%s] to method [%s] on component: [%s]. Retry in %d seconds.',
            $this->ip,
            $this->method,
            $this->component,
            $this->secondsUntilAvailable,
        ));
    }
}
