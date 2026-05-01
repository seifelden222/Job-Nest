<?php

namespace App\Http\Controllers\Api\Applications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Applications\StoreApplicationRequest;
use App\Http\Requests\Api\Applications\UpdateApplicationRequest;
use App\Models\Application;
use App\Models\Job;
use App\Notifications\Applications\ApplicationStatusUpdatedNotification;
use App\Services\Translation\ContentTranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    public function index(Request $request, Job $job): JsonResponse
    {
        $this->authorize('viewAny', [Application::class, $job]);

        $applications = $job->applications()
            ->with(['user:id,name,email,phone', 'cvDocument'])
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'message' => 'Applications fetched successfully.',
            'data' => $applications,
        ]);
    }

    public function store(StoreApplicationRequest $request, Job $job, ContentTranslationService $translationService): JsonResponse
    {
        $user = $request->user();

        if (! $job->is_active || $job->status !== 'active') {
            return response()->json([
                'message' => 'You can only apply to active jobs.',
            ], 422);
        }

        if ((int) $job->company_id === (int) $user->id) {
            return response()->json([
                'message' => 'Company owner cannot apply to own job.',
            ], 422);
        }

        $exists = Application::query()
            ->where('job_id', $job->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'You already applied to this job.',
            ], 409);
        }

        $applicationPayload = $request->validated();

        if ($request->filled('source_language')) {
            $applicationPayload = $translationService->translatePayload(
                $applicationPayload,
                ['cover_letter'],
                (string) $request->validated('source_language'),
            );
        }

        $application = DB::transaction(function () use ($applicationPayload, $job, $user) {
            $application = Application::create([
                'job_id' => $job->id,
                'user_id' => $user->id,
                'cv_document_id' => $applicationPayload['cv_document_id'] ?? null,
                'cover_letter' => $applicationPayload['cover_letter'] ?? null,
                'status' => 'submitted',
                'applied_at' => now(),
            ]);

            $job->increment('applications_count');

            return $application;
        });

        $application->load(['job', 'cvDocument']);

        return response()->json([
            'message' => 'Application submitted successfully.',
            'data' => $application,
        ], 201);
    }

    public function show(Request $request, Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        $application->load(['job', 'user:id,name,email,phone', 'cvDocument']);

        return response()->json([
            'message' => 'Application fetched successfully.',
            'data' => $application,
        ]);
    }

    public function update(UpdateApplicationRequest $request, Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $user = $request->user();
        $status = $request->validated('status');
        $notes = $request->validated('notes');

        $isOwnerCompany = $user->isCompany() && (int) $application->job->company_id === (int) $user->id;
        $isApplicant = (int) $application->user_id === (int) $user->id;

        if ($isApplicant) {
            if ($status !== 'withdrawn') {
                return response()->json([
                    'message' => 'Applicant can only withdraw application.',
                ], 422);
            }

            if ($application->reviewed_at !== null) {
                return response()->json([
                    'message' => 'Application can no longer be withdrawn after review.',
                ], 422);
            }

            $application->update([
                'status' => 'withdrawn',
                'withdrawn_at' => now(),
            ]);

            return response()->json([
                'message' => 'Application withdrawn successfully.',
                'data' => $application->fresh(['job', 'cvDocument']),
            ]);
        }

        if (! $isOwnerCompany) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $previousStatus = $application->status;
        $application->status = $status;
        $application->notes = $notes;

        if ($status !== 'submitted' && $application->reviewed_at === null) {
            $application->reviewed_at = Carbon::now();
        }

        $application->save();

        if ($previousStatus !== $application->status) {
            $application->loadMissing('job:id,title', 'user:id,name,email');
            $application->user?->notify(new ApplicationStatusUpdatedNotification($application, (string) $user->name));
        }

        return response()->json([
            'message' => 'Application updated successfully.',
            'data' => $application->fresh(['job', 'user:id,name,email,phone', 'cvDocument']),
        ]);
    }

    public function destroy(Request $request, Application $application): JsonResponse
    {
        $this->authorize('delete', $application);

        if ($application->reviewed_at !== null) {
            return response()->json([
                'message' => 'Application can no longer be withdrawn after review.',
            ], 422);
        }

        $application->update([
            'status' => 'withdrawn',
            'withdrawn_at' => now(),
        ]);

        return response()->json([
            'message' => 'Application withdrawn successfully.',
        ]);
    }
}
