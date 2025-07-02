<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Listeners;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Modules\ClassicAuth\Events\Login\LoginFailed;
use Modules\ClassicAuth\Events\Login\LoginSucceeded;
use Modules\ClassicAuth\Events\Registration\UserRegistered;
use Modules\ClassicAuth\Events\Security\SuspiciousActivityDetected;

/**
 * Send authentication events to webhooks.
 */
final class SendAuthWebhooks implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 5;

    /**
     * Handle login success webhook.
     */
    public function handleLoginSuccess(LoginSucceeded $event): void
    {
        $webhookUrl = config('classicauth.webhooks.login_success');

        if (! $webhookUrl) {
            return;
        }

        $this->sendWebhook($webhookUrl, [
            'event' => 'login.succeeded',
            'data' => $event->toArray(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Handle login failure webhook.
     */
    public function handleLoginFailure(LoginFailed $event): void
    {
        $webhookUrl = config('classicauth.webhooks.login_failure');

        if (! $webhookUrl) {
            return;
        }

        $this->sendWebhook($webhookUrl, [
            'event' => 'login.failed',
            'data' => $event->toArray(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Handle registration webhook.
     */
    public function handleRegistration(UserRegistered $event): void
    {
        $webhookUrl = config('classicauth.webhooks.registration');

        if (! $webhookUrl) {
            return;
        }

        $this->sendWebhook($webhookUrl, [
            'event' => 'user.registered',
            'data' => $event->toArray(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Handle suspicious activity webhook.
     */
    public function handleSuspiciousActivity(SuspiciousActivityDetected $event): void
    {
        $webhookUrl = config('classicauth.webhooks.suspicious_activity');

        if (! $webhookUrl) {
            return;
        }

        $this->sendWebhook($webhookUrl, [
            'event' => 'security.suspicious_activity',
            'data' => $event->toArray(),
            'timestamp' => now()->toIso8601String(),
            'severity' => $this->getSeverity($event),
        ]);
    }

    /**
     * Subscribe to multiple events.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return array<string, string>
     */
    public function subscribe($events): array
    {
        return [
            LoginSucceeded::class => 'handleLoginSuccess',
            LoginFailed::class => 'handleLoginFailure',
            UserRegistered::class => 'handleRegistration',
            SuspiciousActivityDetected::class => 'handleSuspiciousActivity',
        ];
    }

    /**
     * Send webhook request.
     */
    protected function sendWebhook(string $url, array $payload): void
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Webhook-Event' => $payload['event'],
                    'X-Webhook-Timestamp' => $payload['timestamp'],
                    'X-Webhook-Signature' => $this->generateSignature($payload),
                ])
                ->post($url, $payload);

            if (! $response->successful()) {
                logger()->warning('Webhook delivery failed', [
                    'url' => $url,
                    'event' => $payload['event'],
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (Exception $e) {
            logger()->error('Webhook delivery error', [
                'url' => $url,
                'event' => $payload['event'],
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Generate webhook signature.
     */
    protected function generateSignature(array $payload): string
    {
        $secret = config('classicauth.webhooks.secret', '');

        if (! $secret) {
            return '';
        }

        return hash_hmac('sha256', json_encode($payload), (string) $secret);
    }

    /**
     * Get severity level for suspicious activity.
     */
    protected function getSeverity(SuspiciousActivityDetected $event): string
    {
        return match ($event->activityType) {
            'brute_force' => 'high',
            'credential_stuffing' => 'critical',
            'multiple_ips' => 'medium',
            default => 'low',
        };
    }
}
