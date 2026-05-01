<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\StoreMasterDataRequest;
use App\Http\Requests\Api\MasterData\UpdateMasterDataRequest;
use App\Models\Language;
use App\Services\Translation\ContentTranslationService;
use App\Support\TranslatableJson;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Language::class);

        $languages = Language::query()
            ->orderByRaw(TranslatableJson::extractExpression('name').' asc')
            ->get();

        return response()->json([
            'message' => 'Languages fetched successfully.',
            'data' => $languages,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMasterDataRequest $request, ContentTranslationService $translationService)
    {
        $this->authorize('create', Language::class);

        $validatedData = $translationService->translatePayload(
            $request->validated(),
            ['name'],
            (string) $request->validated('source_language'),
        );

        $language = Language::create($validatedData);

        return response()->json([
            'message' => 'Language created successfully.',
            'data' => $language,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Language $language)
    {
        $this->authorize('view', $language);

        return response()->json([
            'message' => 'Language fetched successfully.',
            'data' => $language,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMasterDataRequest $request, Language $language, ContentTranslationService $translationService)
    {
        $this->authorize('update', $language);

        $validatedData = $translationService->translatePayload(
            $request->validated(),
            ['name'],
            (string) $request->validated('source_language'),
        );
        $language->update($validatedData);

        return response()->json([
            'message' => 'Language updated successfully.',
            'data' => $language,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Language $language)
    {
        $this->authorize('delete', $language);

        $language->delete();

        return response()->json([
            'message' => 'Language deleted successfully.',
        ], 200);
    }
}
