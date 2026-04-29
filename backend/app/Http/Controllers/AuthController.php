<?php

namespace App\Http\Controllers;

use App\Mail\MagicLinkMail;
use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const TOKEN_TTL_MINUTES = 15;
    private const RATE_LIMIT_KEY_PREFIX = 'magic-link:';
    private const RATE_LIMIT_MAX = 3;
    private const RATE_LIMIT_DECAY_SECONDS = 600; // 10 minutes

    public function requestLink(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc,strict', 'max:255'],
        ]);

        $email = strtolower(trim($data['email']));
        $rateKey = self::RATE_LIMIT_KEY_PREFIX.$email;

        if (RateLimiter::tooManyAttempts($rateKey, self::RATE_LIMIT_MAX)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return response()->json([
                'message' => 'Too many requests. Try again shortly.',
                'retry_after_seconds' => $seconds,
            ], 429);
        }

        RateLimiter::hit($rateKey, self::RATE_LIMIT_DECAY_SECONDS);

        // Always perform the work and respond identically — never reveal
        // whether an email exists in our system.
        $plaintextToken = Str::random(64);

        MagicLink::create([
            'email' => $email,
            'token' => MagicLink::hashToken($plaintextToken),
            'expires_at' => now()->addMinutes(self::TOKEN_TTL_MINUTES),
            'created_at' => now(),
        ]);

        $verifyUrl = rtrim(config('app.frontend_url'), '/').'/auth/verify?token='.$plaintextToken;

        Mail::to($email)->send(new MagicLinkMail(
            email: $email,
            verifyUrl: $verifyUrl,
            expiresInMinutes: self::TOKEN_TTL_MINUTES,
        ));

        return response()->json([
            'message' => 'Check your email.',
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'min:32', 'max:128'],
        ]);

        $hashed = MagicLink::hashToken($data['token']);

        // Use a transaction with row-level lock to prevent token reuse races.
        $result = DB::transaction(function () use ($hashed) {
            $link = MagicLink::where('token', $hashed)->lockForUpdate()->first();

            if (! $link || $link->isUsed() || $link->isExpired()) {
                return null;
            }

            $link->update(['used_at' => now()]);

            $user = User::firstOrCreate(
                ['email' => $link->email],
                [
                    'name' => Str::before($link->email, '@'),
                    'email_verified_at' => now(),
                ],
            );

            if (! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            return $user;
        });

        if (! $result) {
            return response()->json([
                'message' => 'Invalid, expired, or already-used link.',
            ], 422);
        }

        $token = $result->createToken('magic-link')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $result->id,
                'name' => $result->name,
                'email' => $result->email,
                'is_admin' => $result->isAdmin(),
            ],
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->isAdmin(),
        ]);
    }
}
