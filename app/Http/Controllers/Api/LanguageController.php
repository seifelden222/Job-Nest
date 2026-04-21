<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $languages = Language::all();
        return response()->json([
            'message' => 'Languages fetched successfully.',
            'data' => $languages,
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

        $language = Language::create($validatedData);

        return response()->json([
            'message' => 'Language created successfully.',
            'data' => $language,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $language = Language::find($id);
        if (!$language) {
            return response()->json(['message' => 'Language not found'], 404);
        }
        return response()->json([
            'message' => 'Language fetched successfully.',
            'data' => $language,
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
        $language = Language::find($id);
        if (!$language) {
            return response()->json(['message' => 'Language not found'], 404);
        }
        $language->update($validatedData);
        return response()->json([
            'message' => 'Language updated successfully.',
            'data' => $language,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $language = Language::find($id);
        if (!$language) {
            return response()->json(['message' => 'Language not found'], 404);
        }
        $language->delete();
        return response()->json([
            'message' => 'Language deleted successfully.',
        ], 200);
    }
}
