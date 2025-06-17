<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit;

use Modules\Core\Exceptions\TooManyRequestsException;
use Tests\TestCase;

final class TooManyRequestsExceptionTest extends TestCase
{
    public function test_constructor_sets_message_and_minutes_correctly(): void
    {
        $component = 'MyComponent';
        $method = 'myMethod';
        $ip = '123.45.67.89';
        $secondsUntilAvailable = 90; // 1.5 minutes

        $exception = new TooManyRequestsException(
            $component,
            $method,
            $ip,
            $secondsUntilAvailable
        );

        // 1) Check that minutesUntilAvailable = ceil(90/60) = 2
        $this->assertSame(2, $exception->minutesUntilAvailable);

        // 2) Verify the exception message matches sprintf(...) exactly:
        $expectedMessage = sprintf(
            'Too many requests from [%s] to method [%s] on component: [%s]. Retry in %d seconds.',
            $ip,
            $method,
            $component,
            $secondsUntilAvailable
        );
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function test_constructor_with_exact_minute_calculation(): void
    {
        $component = 'AnotherComponent';
        $method = 'anotherMethod';
        $ip = '98.76.54.32';
        $secondsUntilAvailable = 120; // exactly 2 minutes

        $exception = new TooManyRequestsException(
            $component,
            $method,
            $ip,
            $secondsUntilAvailable
        );

        // minutesUntilAvailable = ceil(120/60) = 2
        $this->assertSame(2, $exception->minutesUntilAvailable);

        // Check the message again
        $expectedMessage = sprintf(
            'Too many requests from [%s] to method [%s] on component: [%s]. Retry in %d seconds.',
            $ip,
            $method,
            $component,
            $secondsUntilAvailable
        );
        $this->assertSame($expectedMessage, $exception->getMessage());
    }
}
