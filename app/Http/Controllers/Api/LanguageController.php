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
        $this->authorize('viewAny', Language::class);

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
        $this->authorize('create', Language::class);

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
    public function update(Request $request, Language $language)
    {
        $this->authorize('update', $language);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);
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
