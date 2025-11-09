<?php

namespace App\Http\Controllers;

use App\Models\PushToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PushTokenController extends Controller
{
    /**
     * Save or update a push token.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'device_type' => 'nullable|in:ios,android',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }


        $userId = $request->user['id'] ?? Auth::id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }


        $pushToken = PushToken::where('user_id', $userId)
            ->where('token', $request->token)
            ->first();

        if ($pushToken) {

            $pushToken->update([
                'device_type' => $request->device_type ?? $pushToken->device_type,
                'device_name' => $request->device_name ?? $pushToken->device_name,
                'active' => true,
                'last_used_at' => now(),
            ]);
        } else {

            $pushToken = PushToken::create([
                'user_id' => $userId,
                'token' => $request->token,
                'device_type' => $request->device_type,
                'device_name' => $request->device_name,
                'active' => true,
                'last_used_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Push token saved successfully',
            'data' => $pushToken,
        ], 200);
    }

    /**
     * Get the list of push tokens of the connected user.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user['id'] ?? Auth::id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        $tokens = PushToken::where('user_id', $userId)->get();

        return response()->json([
            'success' => true,
            'data' => $tokens,
        ], 200);
    }

    /**
     * Deactivate a push token.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deactivate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = $request->user['id'] ?? Auth::id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        $pushToken = PushToken::where('user_id', $userId)
            ->where('token', $request->token)
            ->first();

        if (!$pushToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found',
            ], 404);
        }

        $pushToken->update(['active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Push token deactivated successfully',
        ], 200);
    }

    /**
     * Delete a push token.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = $request->user['id'] ?? Auth::id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        $pushToken = PushToken::where('user_id', $userId)
            ->where('token', $request->token)
            ->first();

        if (!$pushToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found',
            ], 404);
        }

        $pushToken->delete();

        return response()->json([
            'success' => true,
            'message' => 'Push token deleted successfully',
        ], 200);
    }
}
