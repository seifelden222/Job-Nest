<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserSkillsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $skills = $request->user()->skills()->get();
        return response()->json([
            'message' => 'User skills fetched successfully.',
            'data' => $skills,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'skills' => 'required|array',
            'skills.*' => 'integer|exists:skills,id',
        ]);

        $user = $request->user();
        $user->skills()->sync($request->input('skills'));

        $updated = $user->skills()->get();

        return response()->json([
            'message' => 'User skills updated successfully.',
            'data' => $updated,
        ], 200);
    }
    public function show(string $id)
    {
        return response()->json([
            'message' => 'Not implemented.',
        ], 405);
    }

    public function update(Request $request, string $id)
    {
        return response()->json([
            'message' => 'Not implemented.',
        ], 405);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $exists = $user->skills()
            ->where('skills.id', $id)
            ->exists();

        if (! $exists) {
            return response()->json([
                'message' => 'Skill not found for this user.',
            ], 404);
        }

        $user->skills()->detach($id);
        $remaining = $user->skills()->get();

        return response()->json([
            'message' => 'User skill removed successfully.',
            'data' => $remaining,
        ], 200);
    }
}
