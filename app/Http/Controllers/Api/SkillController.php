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
    public function show(string $id)
    {
        $skill = Skill::find($id);
        if (!$skill) {
            return response()->json(['message' => 'Skill not found'], 404);
        }
        return response()->json([
            'message' => 'Skill fetched successfully.',
            'data' => $skill,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $skill = Skill::find($id);
        if (!$skill) {
            return response()->json(['message' => 'Skill not found'], 404);
        }
        $skill->update($validatedData);
        return response()->json([
            'message' => 'Skill updated successfully.',
            'data' => $skill,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $skill = Skill::find($id);
        if (!$skill) {
            return response()->json(['message' => 'Skill not found'], 404);
        }
        $skill->delete();
        return response()->json([
            'message' => 'Skill deleted successfully.',
        ], 200);
    }
}
