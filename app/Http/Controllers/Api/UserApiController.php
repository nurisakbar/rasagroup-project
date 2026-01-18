<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class UserApiController extends Controller
{
    /**
     * Get user by email or user_id
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|required|email',
            'user_id' => 'sometimes|required|string',
        ], [
            'email.required' => 'Email atau user_id harus diisi',
            'email.email' => 'Format email tidak valid',
            'user_id.required' => 'Email atau user_id harus diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if both email and user_id are provided
        if ($request->has('email') && $request->has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya boleh mengisi salah satu: email atau user_id',
            ], 422);
        }

        // Check if neither email nor user_id is provided
        if (!$request->has('email') && !$request->has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau user_id harus diisi',
            ], 422);
        }

        // Build cache key
        $cacheKey = 'user_api_';
        if ($request->has('email')) {
            $cacheKey .= 'email_' . md5($request->email);
        } else {
            $cacheKey .= 'id_' . $request->user_id;
        }

        try {
            $user = Cache::remember($cacheKey, 3600, function () use ($request) {
                $query = User::query();

                if ($request->has('email')) {
                    $query->where('email', $request->email);
                } else {
                    $query->where('id', $request->user_id);
                }

                return $query->with([
                    'priceLevel',
                    'warehouse',
                    'distributorProvince',
                    'distributorRegency',
                    'driippreneurProvince',
                    'driippreneurRegency',
                ])->first();
            });

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditemukan',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'points' => $user->points,
                    'warehouse_id' => $user->warehouse_id,
                    'warehouse' => $user->warehouse ? [
                        'id' => $user->warehouse->id,
                        'name' => $user->warehouse->name,
                    ] : null,
                    'price_level_id' => $user->price_level_id,
                    'price_level' => $user->priceLevel ? [
                        'id' => $user->priceLevel->id,
                        'name' => $user->priceLevel->name,
                    ] : null,
                    'distributor_status' => $user->distributor_status,
                    'distributor_province' => $user->distributorProvince ? [
                        'id' => $user->distributorProvince->id,
                        'name' => $user->distributorProvince->name,
                    ] : null,
                    'distributor_regency' => $user->distributorRegency ? [
                        'id' => $user->distributorRegency->id,
                        'name' => $user->distributorRegency->name,
                    ] : null,
                    'distributor_address' => $user->distributor_address,
                    'driippreneur_status' => $user->driippreneur_status,
                    'driippreneur_province' => $user->driippreneurProvince ? [
                        'id' => $user->driippreneurProvince->id,
                        'name' => $user->driippreneurProvince->name,
                    ] : null,
                    'driippreneur_regency' => $user->driippreneurRegency ? [
                        'id' => $user->driippreneurRegency->id,
                        'name' => $user->driippreneurRegency->name,
                    ] : null,
                    'driippreneur_address' => $user->driippreneur_address,
                    'created_at' => $user->created_at?->toISOString(),
                    'updated_at' => $user->updated_at?->toISOString(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data user',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     * Note: This endpoint is public but will return user data if authenticated
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCurrentUser(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi. Silakan login terlebih dahulu atau gunakan endpoint /api/users/search dengan email/user_id',
                    'data' => null,
                ], 200);
            }

            // Load relationships
            $user->load([
                'priceLevel',
                'warehouse',
                'distributorProvince',
                'distributorRegency',
                'driippreneurProvince',
                'driippreneurRegency',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditemukan',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'points' => $user->points,
                    'warehouse_id' => $user->warehouse_id,
                    'warehouse' => $user->warehouse ? [
                        'id' => $user->warehouse->id,
                        'name' => $user->warehouse->name,
                    ] : null,
                    'price_level_id' => $user->price_level_id,
                    'price_level' => $user->priceLevel ? [
                        'id' => $user->priceLevel->id,
                        'name' => $user->priceLevel->name,
                    ] : null,
                    'distributor_status' => $user->distributor_status,
                    'distributor_province' => $user->distributorProvince ? [
                        'id' => $user->distributorProvince->id,
                        'name' => $user->distributorProvince->name,
                    ] : null,
                    'distributor_regency' => $user->distributorRegency ? [
                        'id' => $user->distributorRegency->id,
                        'name' => $user->distributorRegency->name,
                    ] : null,
                    'distributor_address' => $user->distributor_address,
                    'driippreneur_status' => $user->driippreneur_status,
                    'driippreneur_province' => $user->driippreneurProvince ? [
                        'id' => $user->driippreneurProvince->id,
                        'name' => $user->driippreneurProvince->name,
                    ] : null,
                    'driippreneur_regency' => $user->driippreneurRegency ? [
                        'id' => $user->driippreneurRegency->id,
                        'name' => $user->driippreneurRegency->name,
                    ] : null,
                    'driippreneur_address' => $user->driippreneur_address,
                    'created_at' => $user->created_at?->toISOString(),
                    'updated_at' => $user->updated_at?->toISOString(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data user',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}

