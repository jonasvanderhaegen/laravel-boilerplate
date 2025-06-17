<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use Modules\Core\Rules\StrictEmailDomain;

beforeEach(function () {
    // make sure your rule can pull from config('core.strict_email_domains')
    config()->set('core.strict_email_domains', [
        'example.com',
        'foo.bar',
    ]);
});

it('passes validation when the email uses an allowed domain', function () {
    $rule = new StrictEmailDomain();

    $validator = Validator::make(
        ['email' => 'user@example.com'],
        ['email' => ['required', 'email', $rule]]
    );

    expect($validator->passes())->toBeTrue();
});

it('passes validation for another allowed domain', function () {
    $rule = new StrictEmailDomain();

    $validator = Validator::make(
        ['email' => 'someone@foo.bar'],
        ['email' => ['required', 'email', $rule]]
    );

    expect($validator->passes())->toBeTrue();
});

it('fails if the value is not a valid email at all', function () {
    $rule = new StrictEmailDomain();

    $validator = Validator::make(
        ['email' => 'not-an-email'],
        ['email' => ['required', 'email', $rule]]
    );

    // the 'email' rule itself should catch this
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('email'))
        ->toContain('email');
});
