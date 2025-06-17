<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Rules\StrictDob;

beforeAll(function () {
    // Freeze "now" to Jan 1, 2023 so age checks are predictable
    Carbon::setTestNow(Carbon::create(2023, 1, 1));
});

it('passes for a perfectly valid date (older than 18)', function () {
    $rule = new StrictDob;
    $validator = Validator::make(
        ['dob' => '01-01-2000'],
        ['dob' => [$rule]]
    );

    expect($validator->passes())->toBeTrue();
});

it('fails when format is wrong', function () {
    $rule = new StrictDob;
    $validator = Validator::make(
        ['dob' => '2000/01/01'],
        ['dob' => [$rule]]
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('dob'))
        ->toBe('Birthday must be in the format DD-MM-YYYY.');
});

it('fails if year is too small or too large', function () {
    $rule = new StrictDob;

    $v1 = Validator::make(['dob' => '01-01-1899'], ['dob' => [$rule]]);
    expect($v1->fails())->toBeTrue()
        ->and($v1->errors()->first('dob'))
        ->toBe('Birthday year is invalid.');

    $futureYear = Carbon::now()->year + 1;
    $v2 = Validator::make(['dob' => sprintf('01-01-%d', $futureYear)], ['dob' => [$rule]]);
    expect($v2->fails())->toBeTrue()
        ->and($v2->errors()->first('dob'))
        ->toBe('Birthday year is invalid.');
});

it('fails if month is out of range', function () {
    $rule = new StrictDob;
    $validator = Validator::make(
        ['dob' => '15-13-2000'],
        ['dob' => [$rule]]
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('dob'))
        ->toBe('Birth month must be between 1 and 12.');
});

it('fails if day is out of range for given month', function () {
    $rule = new StrictDob;

    // April has 30 days
    $v1 = Validator::make(['dob' => '31-04-2000'], ['dob' => [$rule]]);
    expect($v1->fails())->toBeTrue()
        ->and($v1->errors()->first('dob'))
        ->toBe('Birth day must be between 1 and 30 for month 4.');

    // February non-leap
    $v2 = Validator::make(['dob' => '29-02-2001'], ['dob' => [$rule]]);
    expect($v2->fails())->toBeTrue()
        ->and($v2->errors()->first('dob'))
        ->toBe('Birth day must be between 1 and 28 for month 2.');

    // February leap
    $v3 = Validator::make(['dob' => '29-02-2000'], ['dob' => [$rule]]);
    expect($v3->passes())->toBeTrue();
});

it('fails if under 18 years old', function () {
    // Born Jan 2, 2006 → still 16 on Jan 1 2023
    $validator = Validator::make(
        ['dob' => '02-01-2015'],
        ['dob' => [new StrictDob]]
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('dob'))
        ->toBe('You must be at least 18 years old.');
});

it('passes for exactly 18 years old', function () {
    // Born Jan 1, 2005 → turns 18 on Jan 1 2023
    $validator = Validator::make(
        ['dob' => '01-01-2005'],
        ['dob' => [new StrictDob]]
    );

    expect($validator->passes())->toBeTrue();
});

it('passes for older than 18', function () {
    $validator = Validator::make(
        ['dob' => '15-06-1990'],
        ['dob' => [new StrictDob]]
    );

    expect($validator->passes())->toBeTrue();
});

it('fails when the person is younger than 18', function () {
    // Fix “now” so the age calculation is deterministic
    Carbon::setTestNow(Carbon::create(2023, 1, 1));

    /*
     * Someone born on 2 Jan 2006 is
     *   16 y 364 d old on 1 Jan 2023 → **under 18**
     */
    $validator = Validator::make(
        ['dob' => '02-01-2006'],
        ['dob' => [new StrictDob]]
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('dob'))
        ->toBe('You must be at least 18 years old.');
});
