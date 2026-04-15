<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $user = User::with('role')->where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Account is deactivated'], 403);
        }

        $token = JWTAuth::fromUser($user);

        $user->update(['last_login_at' => now()]);

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $this->userResponse($user),
        ]);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string']);

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update([
            'otp_code' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        // In production: send via SMS gateway
        // For development, return the OTP
        // OTP sent via SMS gateway in production
        // Never expose OTP in API response
        return response()->json([
            'message' => 'OTP sent successfully',
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::with('role')->where('phone', $request->phone)->first();

        if (!$user || !$user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return response()->json(['message' => 'OTP expired or invalid'], 401);
        }

        if (!Hash::check($request->otp, $user->otp_code)) {
            return response()->json(['message' => 'Invalid OTP'], 401);
        }

        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
            'last_login_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $this->userResponse($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('role', 'client');

        return response()->json(['user' => $this->userResponse($user)]);
    }

    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::parseToken()->refresh();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not refresh token'], 401);
        }

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ]);
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logged out successfully']);
    }

    private function userResponse(User $user): array
    {
        $data = [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => [
                'id' => $user->role->id,
                'name' => $user->role->name,
                'slug' => $user->role->slug,
            ],
            'is_active' => $user->is_active,
            'last_login_at' => $user->last_login_at,
        ];

        if ($user->client) {
            $data['client'] = [
                'id' => $user->client->id,
                'business_name' => $user->client->business_name,
                'credit_limit' => $user->client->credit_limit,
                'current_outstanding' => $user->client->current_outstanding,
                'kyc_verified' => $user->client->kyc_verified,
            ];
        }

        return $data;
    }
}
