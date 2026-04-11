<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Api\UserResource;

class AuthController extends Controller
{
    /**
     * Authenticate user and create token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang Anda berikan salah.'],
            ]);
        }

        return [
            'token' => $user->createToken($request->device_name ?? 'internal_api')->plainTextToken,
            'user' => new UserResource($user),
        ];
    }

    /**
     * Revoke the current user's token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil keluar.'
        ]);
    }

    /**
     * Get the authenticated user's profile.
     */
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }
}
