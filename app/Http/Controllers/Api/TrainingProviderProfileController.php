<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TrainingProviders\UpsertTrainingProviderProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class TrainingProviderProfileController extends Controller
{
    public function show(): JsonResponse
    {
        $profile = request()->user()->load('trainingProviderProfile')->trainingProviderProfile;

        return response()->json([
            'message' => 'Training provider profile fetched successfully.',
            'data' => $profile,
        ]);
    }

    public function update(UpsertTrainingProviderProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $payload = $request->validated();

        if ($request->hasFile('logo')) {
            if ($user->trainingProviderProfile?->logo && Storage::disk('public')->exists($user->trainingProviderProfile->logo)) {
                Storage::disk('public')->delete($user->trainingProviderProfile->logo);
            }

            $payload['logo'] = $request->file('logo')->store('training-provider-logos', 'public');
        }

        $payload['onboarding_step'] = 1;
        $payload['is_profile_completed'] = $payload['is_profile_completed'] ?? true;

        $profile = $user->trainingProviderProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $payload,
        );

        return response()->json([
            'message' => 'Training provider profile saved successfully.',
            'data' => $profile,
        ]);
    }
}
