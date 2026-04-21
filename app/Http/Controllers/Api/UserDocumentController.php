<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserDocumentRequest;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserDocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $documents = $user->documents()->get();

        return response()->json([
            'message' => 'User documents fetched successfully.',
            'data' => $documents,
        ], 200);
    }

    public function store(StoreUserDocumentRequest $request): JsonResponse
    {
        $user = $request->user();

        $file = $request->file('file');
        $type = $request->input('type');
        $title = $request->input('title');

        DB::beginTransaction();
        try {
            // store file on public disk under documents
            $path = $file->store('documents', 'public');

            // if CV, ensure only one primary
            if ($type === 'cv') {
                $user->documents()->where('type', 'cv')->where('is_primary', true)->update(['is_primary' => false]);
                $isPrimary = true;
            } else {
                $isPrimary = false;
            }

            $doc = Document::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'is_primary' => $isPrimary,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Document uploaded successfully.',
                'data' => $doc,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            // cleanup file if exists
            if (! empty($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return response()->json([
                'message' => 'Failed to upload document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $doc = $user->documents()->where('id', $id)->first();

        if (! $doc) {
            return response()->json([
                'message' => 'Document not found for this user.',
            ], 404);
        }

        // delete physical file if exists
        if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $doc->delete();

        return response()->json([
            'message' => 'Document deleted successfully.',
        ], 200);
    }
}
