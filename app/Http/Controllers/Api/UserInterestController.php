<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserInterestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $interests = $request->user()->interests()->get();
        return response()->json([
            'message' => 'User interests fetched successfully.',
            'data' => $interests,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'interests' => 'required|array',
            'interests.*' => 'integer|exists:interests,id',
        ]);

        $user = $request->user();
        $user->interests()->sync($request->input('interests'));

        $updated = $user->interests()->get();

        return response()->json([
            'message' => 'User interests updated successfully.',
            'data' => $updated,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $interest = $request->user()->interests()->find($id);
        if (!$interest) {
            return response()->json(['message' => 'Interest not found'], 404);
        }
        $request->user()->interests()->detach($id);

        $remaining = $request->user()->interests()->get();

        return response()->json([
            'message' => 'User interest removed successfully.',
            'data' => $remaining,
        ], 200);
    }
}
