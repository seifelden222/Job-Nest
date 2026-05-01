<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\StoreMasterDataRequest;
use App\Http\Requests\Api\MasterData\UpdateMasterDataRequest;
use App\Models\Skill;
use App\Services\Translation\ContentTranslationService;
use App\Support\TranslatableJson;

class SkillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Skill::class);

        $skills = Skill::query()
            ->orderByRaw(TranslatableJson::extractExpression('name').' asc')
            ->get();

        return response()->json([
            'message' => 'Skills fetched successfully.',
            'data' => $skills,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMasterDataRequest $request, ContentTranslationService $translationService)
    {
        $this->authorize('create', Skill::class);

        $validatedData = $translationService->translatePayload(
            $request->validated(),
            ['name'],
            (string) $request->validated('source_language'),
        );

        $skill = Skill::create($validatedData);

        return response()->json([
            'message' => 'Skill created successfully.',
            'data' => $skill,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Skill $skill)
    {
        $this->authorize('view', $skill);

        return response()->json([
            'message' => 'Skill fetched successfully.',
            'data' => $skill,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMasterDataRequest $request, Skill $skill, ContentTranslationService $translationService)
    {
        $this->authorize('update', $skill);

        $validatedData = $translationService->translatePayload(
            $request->validated(),
            ['name'],
            (string) $request->validated('source_language'),
        );
        $skill->update($validatedData);

        return response()->json([
            'message' => 'Skill updated successfully.',
            'data' => $skill,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Skill $skill)
    {
        $this->authorize('delete', $skill);

        $skill->delete();

        return response()->json([
            'message' => 'Skill deleted successfully.',
        ], 200);
    }
}
