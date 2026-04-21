<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\Request;

class InterestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $interests = Interest::all();
        return response()->json([
            'message' => 'Interests fetched successfully.',
            'data' => $interests,
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
        $interest = Interest::create($validatedData);
        return response()->json([
            'message' => 'Interest created successfully.',
            'data' => $interest,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $interest = Interest::find($id);
        if (!$interest) {
            return response()->json(['message' => 'Interest not found'], 404);
        }
        return response()->json([
            'message' => 'Interest fetched successfully.',
            'data' => $interest,
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
        $interest = Interest::find($id);
        if (!$interest) {
            return response()->json(['message' => 'Interest not found'], 404);
        }
        $interest->update($validatedData);
        return response()->json([
            'message' => 'Interest updated successfully.',
            'data' => $interest,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $interest = Interest::find($id);
        if (!$interest) {
            return response()->json(['message' => 'Interest not found'], 404);
        }
        $interest->delete();
        return response()->json([
            'message' => 'Interest deleted successfully.',
        ], 200);
    }
}
