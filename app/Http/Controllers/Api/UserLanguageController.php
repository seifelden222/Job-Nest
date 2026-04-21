<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserLanguageController extends Controller
{
    /**
     * Display the authenticated user's languages.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->load('languages');

        return response()->json([
            'message' => 'User languages fetched successfully.',
            'data' => $user->languages,
        ]);
    }

    /**
     * Attach a language to the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'language_id' => [
                'required',
                'integer',
                Rule::exists('languages', 'id'),
            ],
        ]);

        $user = $request->user();

        $alreadyExists = $user->languages()
            ->where('languages.id', $validated['language_id'])
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'message' => 'Language already added.',
            ], 409);
        }

        $user->languages()->attach($validated['language_id']);
        $user->load('languages');

        return response()->json([
            'message' => 'Language added successfully.',
            'data' => $user->languages,
        ], 201);
    }

    /**
     * Remove a language from the authenticated user.
     */
    public function destroy(Request $request, int $languageId): JsonResponse
    {
        $user = $request->user();

        $exists = $user->languages()
            ->where('languages.id', $languageId)
            ->exists();

        if (! $exists) {
            return response()->json([
                'message' => 'Language not found for this user.',
            ], 404);
        }

        $user->languages()->detach($languageId);
        $user->load('languages');

        return response()->json([
            'message' => 'Language removed successfully.',
            'data' => $user->languages,
        ]);
    }

    /**
     * Optional placeholders if you insist on full apiResource shape.
     * You can delete these if you use only(['index', 'store', 'destroy']).
     */
    public function show(string $id): JsonResponse
    {
        return response()->json([
            'message' => 'Not implemented.',
        ], 405);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json([
            'message' => 'Not implemented.',
        ], 405);
    }
}
