<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Skill::class);

        $skills = Skill::all();

        return response()->json([
            'message' => 'Skills fetched successfully.',
            'data' => $skills,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Skill::class);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

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
    public function update(Request $request, Skill $skill)
    {
        $this->authorize('update', $skill);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);
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
