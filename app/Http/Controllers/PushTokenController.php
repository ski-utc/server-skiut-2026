<?php

namespace App\Http\Controllers;

use App\Models\PushToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        try {
            $validated = $request->validate([
                'token' => 'required|string',
                'device_type' => 'nullable|in:ios,android',
                'device_name' => 'nullable|string|max:255',
            ]);

            $userId = $request->user['id'] ?? Auth::id();

            if (!$userId) {
                Log::error('Erreur lors de la sauvegarde du token de push: ' . 'Utilisateur non authentifié');
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié',
                ], 401);
            }

            $pushToken = PushToken::where('user_id', $userId)
                ->where('token', $validated['token'])
                ->first();

            if ($pushToken) {
                $pushToken->update([
                    'device_type' => $validated['device_type'] ?? $pushToken->device_type,
                    'device_name' => $validated['device_name'] ?? $pushToken->device_name,
                    'active' => true,
                    'last_used_at' => now(),
                ]);
            } else {
                $pushToken = PushToken::create([
                    'user_id' => $userId,
                    'token' => $validated['token'],
                    'device_type' => $validated['device_type'],
                    'device_name' => $validated['device_name'],
                    'active' => true,
                    'last_used_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Push token saved successfully',
                'data' => $pushToken,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la sauvegarde du token de push: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde du token de push: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the list of push tokens of the connected user.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = $request->user['id'] ?? Auth::id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié',
                ], 401);
            }

            $tokens = PushToken::where('user_id', $userId)->get();

            $data = $tokens->map(function ($token) {
                return [
                    'id' => $token->id,
                    'token' => $token->token,
                    'device_type' => $token->device_type,
                    'device_name' => $token->device_name,
                    'active' => $token->active,
                    'last_used_at' => $token->last_used_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des tokens de push: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des tokens de push: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deactivate a push token.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deactivate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
            ]);

            $userId = $request->user['id'] ?? Auth::id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié',
                ], 401);
            }

            $pushToken = PushToken::where('user_id', $userId)
                ->where('token', $validated['token'])
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
        } catch (\Exception $e) {
            Log::error('Erreur lors de la désactivation du token de push: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la désactivation du token de push: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a push token.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
            ]);

            $userId = $request->user['id'] ?? Auth::id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié',
                ], 401);
            }

            $pushToken = PushToken::where('user_id', $userId)
                ->where('token', $validated['token'])
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
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du token de push: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du token de push: ' . $e->getMessage(),
            ], 500);
        }
    }
}
