<?php

declare(strict_types=1);

namespace Modules\Core\Rules;

use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;

final class StrictDob implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parts = explode('-', (string) $value);
        if (count($parts) !== 3) {
            $fail('Birthday must be in the format DD-MM-YYYY.');

            return;
        }

        [$day, $month, $year] = array_map('intval', $parts);

        // Year reasonable check
        if ($year < 1900 || $year > Carbon::now()->year) {
            $fail('Birthday year is invalid.');

            return;
        }

        // Month between 1 and 12
        if ($month < 1 || $month > 12) {
            $fail('Birth month must be between 1 and 12.');

            return;
        }

        // Determine max days in month
        if ($month === 2) {
            // Leap year?
            $isLeap = ($year % 400 === 0) || ($year % 4 === 0 && $year % 100 !== 0);
            $maxDay = $isLeap ? 29 : 28;
        } elseif (in_array($month, [1, 3, 5, 7, 8, 10, 12], true)) {
            $maxDay = 31;
        } else {
            $maxDay = 30;
        }

        // Day between 1 and maxDay
        if ($day < 1 || $day > $maxDay) {
            $fail("Birth day must be between 1 and {$maxDay} for month {$month}.");

            return;
        }

        // Build a Carbon date, then check age >= 18
        try {
            $dob = Carbon::createFromDate($year, $month, $day)->startOfDay();

            // @codeCoverageIgnoreStart
        } catch (Exception) {
            $fail('Birthdate is not a valid date.');

            return;
        }
        // @codeCoverageIgnoreEnd

        $age = $dob->diffInYears(Carbon::now());

        if ($age < 18) {
            $fail('You must be at least 18 years old.');
        }
    }
}
