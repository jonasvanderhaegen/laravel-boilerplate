<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ClassicAuth\Models\LoginAttempt;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\ClassicAuth\Models\LoginAttempt>
 */
class LoginAttemptFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LoginAttempt::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $successful = $this->faker->boolean(70); // 70% success rate
        
        return [
            'user_id' => $successful ? User::factory() : null,
            'email' => $this->faker->safeEmail(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'successful' => $successful,
            'failure_reason' => $successful ? null : $this->faker->randomElement([
                LoginAttempt::FAILURE_INVALID_CREDENTIALS,
                LoginAttempt::FAILURE_RATE_LIMITED,
                LoginAttempt::FAILURE_ACCOUNT_DISABLED,
                LoginAttempt::FAILURE_EMAIL_NOT_VERIFIED,
            ]),
            'attempted_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the login attempt was successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'successful' => true,
            'failure_reason' => null,
            'user_id' => User::factory(),
        ]);
    }

    /**
     * Indicate that the login attempt failed.
     */
    public function failed(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'successful' => false,
            'failure_reason' => $reason ?? LoginAttempt::FAILURE_INVALID_CREDENTIALS,
        ]);
    }

    /**
     * Indicate that the login attempt is from a specific IP.
     */
    public function fromIp(string $ipAddress): static
    {
        return $this->state(fn (array $attributes) => [
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Indicate that the login attempt is for a specific email.
     */
    public function forEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }

    /**
     * Indicate that the login attempt is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Indicate that the login attempt happened at a specific time.
     */
    // @codeCoverageIgnoreStart
    public function attemptedAt($dateTime): static
    {
        return $this->state(fn (array $attributes) => [
            'attempted_at' => $dateTime,
        ]);
    }
    // @codeCoverageIgnoreEnd
}
