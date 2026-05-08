<?php

namespace App\Observers;

use App\Models\Course;
use App\Services\Ai\CourseAiSyncService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class CourseObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly CourseAiSyncService $courseAiSyncService) {}

    public function created(Course $course): void
    {
        app()->terminating(function () use ($course): void {
            $freshCourse = Course::query()->find($course->id);

            if ($freshCourse !== null) {
                $this->courseAiSyncService->syncIfEligible($freshCourse);
            }
        });
    }

    public function updated(Course $course): void
    {
        app()->terminating(function () use ($course): void {
            $freshCourse = Course::query()->find($course->id);

            if ($freshCourse !== null) {
                $this->courseAiSyncService->syncIfEligible($freshCourse);
            }
        });
    }
}
