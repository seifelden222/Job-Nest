<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Courses\StoreCourseRequest;
use App\Http\Requests\Api\Courses\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Course::query()
            ->with(['trainingProvider.user:id,name', 'category:id,name,slug,type', 'skills:id,name'])
            ->published();

        if ($request->filled('q')) {
            $term = (string) $request->query('q');
            $query->where(function ($inner) use ($term) {
                $inner->where('title', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->query('category_id'));
        }

        if ($request->filled('skill_id')) {
            $skillId = (int) $request->query('skill_id');
            $query->whereHas('skills', fn ($skills) => $skills->where('skills.id', $skillId));
        }

        if ($request->filled('delivery_mode')) {
            $query->where('delivery_mode', (string) $request->query('delivery_mode'));
        }

        if ($request->filled('level')) {
            $query->where('level', (string) $request->query('level'));
        }

        $courses = $query->latest()->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'message' => 'Courses fetched successfully.',
            'data' => $courses,
        ]);
    }

    public function myCourses(Request $request): JsonResponse
    {
        $providerProfile = $request->user()->trainingProviderProfile;

        if (! $providerProfile) {
            return response()->json([
                'message' => 'Training provider profile is required.',
            ], 403);
        }

        $courses = Course::query()
            ->with(['category:id,name,slug,type', 'skills:id,name'])
            ->where('training_provider_id', $providerProfile->id)
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'message' => 'Provider courses fetched successfully.',
            'data' => $courses,
        ]);
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $providerProfile = $request->user()->trainingProviderProfile;
        $payload = $request->validated();
        $skillIds = $payload['skill_ids'] ?? [];
        unset($payload['skill_ids']);

        if ($request->hasFile('thumbnail')) {
            $payload['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $payload['training_provider_id'] = $providerProfile->id;
        $payload['slug'] = $this->buildSlug($payload['slug'] ?? $payload['title']);
        $payload['status'] = $payload['status'] ?? 'draft';
        $payload['is_active'] = $payload['is_active'] ?? ($payload['status'] === 'published');

        $course = Course::create($payload);

        if (! empty($skillIds)) {
            $course->skills()->sync($skillIds);
        }

        return response()->json([
            'message' => 'Course created successfully.',
            'data' => $course->load(['trainingProvider.user:id,name', 'category:id,name,slug,type', 'skills:id,name']),
        ], 201);
    }

    public function show(Request $request, Course $course): JsonResponse
    {
        $isOwner = $request->user()?->trainingProviderProfile?->id === $course->training_provider_id;

        if (($course->status !== 'published' || ! $course->is_active) && ! $isOwner) {
            return response()->json([
                'message' => 'Course not found.',
            ], 404);
        }

        $course->load([
            'trainingProvider.user:id,name,email',
            'category:id,name,slug,type',
            'skills:id,name',
            'reviews.user:id,name',
        ]);

        return response()->json([
            'message' => 'Course fetched successfully.',
            'data' => $course,
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $payload = $request->validated();
        $skillIds = $payload['skill_ids'] ?? null;
        unset($payload['skill_ids']);

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail && Storage::disk('public')->exists($course->thumbnail)) {
                Storage::disk('public')->delete($course->thumbnail);
            }

            $payload['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        if (array_key_exists('slug', $payload) || array_key_exists('title', $payload)) {
            $payload['slug'] = $this->buildSlug($payload['slug'] ?? $payload['title'] ?? $course->title, $course->id);
        }

        if (array_key_exists('status', $payload) && ! array_key_exists('is_active', $payload)) {
            $payload['is_active'] = $payload['status'] === 'published';
        }

        $course->update($payload);

        if (is_array($skillIds)) {
            $course->skills()->sync($skillIds);
        }

        return response()->json([
            'message' => 'Course updated successfully.',
            'data' => $course->fresh(['trainingProvider.user:id,name', 'category:id,name,slug,type', 'skills:id,name']),
        ]);
    }

    public function destroy(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        if ($course->thumbnail && Storage::disk('public')->exists($course->thumbnail)) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully.',
        ]);
    }

    private function buildSlug(string $value, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug !== '' ? $baseSlug : 'course';
        $counter = 1;

        while (
            Course::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
