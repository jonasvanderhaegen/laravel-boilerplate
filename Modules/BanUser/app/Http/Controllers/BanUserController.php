<?php

declare(strict_types=1);

namespace Modules\BanUser\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\BanUser\Services\BanCheckService;
use Modules\BanUser\Events\UserBanned;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

/**
 * API Controller for handling user reports and bans.
 */
final class BanUserController extends Controller
{
    public function __construct(
        private readonly BanCheckService $banCheckService
    ) {}

    /**
     * Report a user (which triggers a ban).
     */
    public function reportUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'reason' => 'required|string|max:255',
            'details' => 'nullable|string|max:1000',
            'duration_hours' => 'nullable|integer|min:1|max:8760', // Max 1 year
        ]);

        // Check if already banned
        if ($this->banCheckService->isEmailBanned($validated['email'])) {
            return response()->json([
                'message' => 'User is already banned.',
            ], 422);
        }

        // Calculate expiration if duration provided
        $expiresAt = null;
        if (isset($validated['duration_hours'])) {
            $expiresAt = now()->addHours($validated['duration_hours']);
        }

        // Create the ban
        $ban = $this->banCheckService->banUser([
            'email' => $validated['email'],
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'banned_by' => 'system_report',
            'expires_at' => $expiresAt,
        ]);

        // Dispatch event
        event(new UserBanned(
            $ban,
            $request->ip() ?? 'unknown',
            $request->userAgent() ?? 'unknown'
        ));

        return response()->json([
            'message' => 'User has been banned successfully.',
            'ban_id' => $ban->id,
            'expires_at' => $ban->expires_at?->toIso8601String(),
        ], 201);
    }

    /**
     * Check if an email is banned.
     */
    public function checkBan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $isBanned = $this->banCheckService->isEmailBanned($validated['email']);
        $ban = null;

        if ($isBanned) {
            $ban = $this->banCheckService->getBanDetails($validated['email']);
        }

        return response()->json([
            'is_banned' => $isBanned,
            'ban' => $ban ? [
                'reason' => $ban->reason,
                'banned_at' => $ban->banned_at->toIso8601String(),
                'expires_at' => $ban->expires_at?->toIso8601String(),
                'is_permanent' => $ban->isPermanent(),
            ] : null,
        ]);
    }

    /**
     * Lift a ban by email.
     */
    public function liftBan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $ban = $this->banCheckService->getBanDetails($validated['email']);

        if (!$ban) {
            return response()->json([
                'message' => 'No active ban found for this email.',
            ], 404);
        }

        $ban->lift();

        // Clear cache
        $this->banCheckService->clearEmailCache($validated['email']);
        if ($ban->user_id) {
            $this->banCheckService->clearUserCache($ban->user_id);
        }

        return response()->json([
            'message' => 'Ban has been lifted successfully.',
        ]);
    }
}
