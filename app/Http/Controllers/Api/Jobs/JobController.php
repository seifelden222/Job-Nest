<?php

namespace App\Http\Controllers\Api\Jobs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Jobs\StoreJobRequest;
use App\Http\Requests\Api\Jobs\UpdateJobRequest;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Job::query()->with(['company:id,name', 'category:id,name,slug,type', 'skills:id,name'])->active();

        if ($request->filled('q')) {
            $term = (string) $request->query('q');
            $query->where(function ($inner) use ($term) {
                $inner->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%'.$request->query('location').'%');
        }

        if ($request->filled('employment_type')) {
            $query->where('employment_type', (string) $request->query('employment_type'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->query('category_id'));
        }

        if ($request->filled('skill_id')) {
            $skillId = (int) $request->query('skill_id');
            $query->whereHas('skills', function ($skills) use ($skillId) {
                $skills->where('skills.id', $skillId);
            });
        }

        $jobs = $query->latest()->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'message' => 'Jobs fetched successfully.',
            'data' => $jobs,
        ]);
    }

    public function store(StoreJobRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $skillIds = $payload['skill_ids'] ?? [];
        unset($payload['skill_ids']);

        $payload['company_id'] = $request->user()->id;
        $payload['status'] = $payload['status'] ?? 'draft';
        $payload['is_active'] = $payload['is_active'] ?? ($payload['status'] === 'active');

        $job = Job::create($payload);

        if (! empty($skillIds)) {
            $job->skills()->sync($skillIds);
        }

        $job->load(['company:id,name', 'category:id,name,slug,type', 'skills:id,name']);

        return response()->json([
            'message' => 'Job created successfully.',
            'data' => $job,
        ], 201);
    }

    public function show(Request $request, Job $job): JsonResponse
    {
        $isOwner = $request->user()?->id === $job->company_id;

        if (! $job->is_active || $job->status !== 'active') {
            if (! $isOwner) {
                return response()->json([
                    'message' => 'Job not found.',
                ], 404);
            }
        }

        $job->load(['company:id,name', 'category:id,name,slug,type', 'skills:id,name']);

        return response()->json([
            'message' => 'Job fetched successfully.',
            'data' => $job,
        ]);
    }

    public function update(UpdateJobRequest $request, Job $job): JsonResponse
    {
        $payload = $request->validated();
        $skillIds = $payload['skill_ids'] ?? null;
        unset($payload['skill_ids']);

        if (array_key_exists('status', $payload) && ! array_key_exists('is_active', $payload)) {
            $payload['is_active'] = $payload['status'] === 'active';
        }

        $job->update($payload);

        if (is_array($skillIds)) {
            $job->skills()->sync($skillIds);
        }

        $job->load(['company:id,name', 'category:id,name,slug,type', 'skills:id,name']);

        return response()->json([
            'message' => 'Job updated successfully.',
            'data' => $job,
        ]);
    }

    public function destroy(UpdateJobRequest $request, Job $job): JsonResponse
    {
        $job->delete();

        return response()->json([
            'message' => 'Job deleted successfully.',
        ]);
    }
}
