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
        $this->authorize('viewAny', Interest::class);

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
        $this->authorize('create', Interest::class);

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
    public function update(Request $request, Interest $interest)
    {
        $this->authorize('update', $interest);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);
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
