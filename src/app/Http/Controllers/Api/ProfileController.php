<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfileController extends Controller
{
    /**
     * Получить профиль текущего пользователя
     */
    public function show()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не найден'
                ], 404);
            }

            $profile = $user->getOrCreateProfile();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'profile' => $profile
                ]
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Токен недействителен'
            ], 401);
        }
    }

    /**
     * Обновить профиль пользователя
     */
    public function update(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не найден'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'birth_date' => 'nullable|date|before:today',
                'gender' => 'nullable|in:male,female,other',
                'phone' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'postal_code' => 'nullable|string|max:20',
                'is_public' => 'boolean',
                'show_email' => 'boolean',
                'show_phone' => 'boolean',
                'preferences' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $profile = $user->getOrCreateProfile();
            $profile->update($request->only([
                'first_name', 'last_name', 'middle_name', 'birth_date', 'gender',
                'phone', 'country', 'city', 'address', 'postal_code',
                'is_public', 'show_email', 'show_phone', 'preferences'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Профиль успешно обновлен',
                'data' => [
                    'user' => $user->fresh(),
                    'profile' => $profile->fresh()
                ]
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Токен недействителен'
            ], 401);
        }
    }

    /**
     * Получить публичный профиль пользователя
     */
    public function publicProfile($userId)
    {
        try {
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не найден'
                ], 404);
            }

            $profile = $user->profile;
            
            if (!$profile || !$profile->is_public) {
                return response()->json([
                    'success' => false,
                    'message' => 'Профиль не найден или недоступен'
                ], 404);
            }

            // Фильтруем данные в зависимости от настроек приватности
            $publicData = [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $profile->first_name,
                'last_name' => $profile->last_name,
                'full_name' => $profile->full_name,
                'avatar_url' => $profile->avatar_url,
                'bio' => $profile->bio,
                'job_title' => $profile->job_title,
                'company' => $profile->company,
                'city' => $profile->city,
                'country' => $profile->country,
                'website' => $profile->website,
                'linkedin' => $profile->linkedin,
                'twitter' => $profile->twitter,
                'facebook' => $profile->facebook,
                'instagram' => $profile->instagram,
                'skills' => $profile->skills,
                'created_at' => $user->created_at,
            ];

            // Добавляем email и телефон только если разрешено
            if ($profile->show_email) {
                $publicData['email'] = $user->email;
            }
            
            if ($profile->show_phone) {
                $publicData['phone'] = $profile->phone;
            }

            return response()->json([
                'success' => true,
                'data' => $publicData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении профиля'
            ], 500);
        }
    }

    /**
     * Загрузить аватар пользователя
     */
    public function uploadAvatar(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не найден'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $profile = $user->getOrCreateProfile();

            // Удаляем старый аватар если есть
            if ($profile->avatar && Storage::disk('public')->exists($profile->avatar)) {
                Storage::disk('public')->delete($profile->avatar);
            }

            // Сохраняем новый аватар
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $profile->update(['avatar' => $avatarPath]);

            return response()->json([
                'success' => true,
                'message' => 'Аватар успешно загружен',
                'data' => [
                    'avatar_url' => asset('storage/' . $avatarPath),
                    'profile' => $profile->fresh()
                ]
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Токен недействителен'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке аватара'
            ], 500);
        }
    }
}