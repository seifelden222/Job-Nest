<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\StoreMasterDataRequest;
use App\Http\Requests\Api\MasterData\UpdateMasterDataRequest;
use App\Models\Interest;
use App\Services\Translation\ContentTranslationService;
use App\Support\TranslatableJson;

class InterestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Interest::class);

        $interests = Interest::query()
            ->orderByRaw(TranslatableJson::extractExpression('name').' asc')
            ->get();

        return response()->json([
            'message' => 'Interests fetched successfully.',
            'data' => $interests,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMasterDataRequest $request, ContentTranslationService $translationService)
    {
        $this->authorize('create', Interest::class);

        $validatedData = $translationService->translatePayload(
            $request->validated(),
            ['name'],
            (string) $request->validated('source_language'),
        );
        $interest = Interest::create($validatedData);

        return response()->json([
            'message' => 'Interest created successfully.',
            'data' => $interest,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Interest $interest)
    {
        $this->authorize('view', $interest);

        return response()->json([
            'message' => 'Interest fetched successfully.',
            'data' => $interest,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMasterDataRequest $request, Interest $interest, ContentTranslationService $translationService)
    {
        $this->authorize('update', $interest);

        $validatedData = $translationService->translatePayload(
            $request->validated(),
            ['name'],
            (string) $request->validated('source_language'),
        );
        $interest->update($validatedData);

        return response()->json([
            'message' => 'Interest updated successfully.',
            'data' => $interest,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Interest $interest)
    {
        $this->authorize('delete', $interest);

        $interest->delete();

        return response()->json([
            'message' => 'Interest deleted successfully.',
        ], 200);
    }
}
