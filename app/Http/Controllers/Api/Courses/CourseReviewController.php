<?php

namespace App\Http\Controllers\Api\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Courses\StoreCourseReviewRequest;
use App\Http\Requests\Api\Courses\UpdateCourseReviewRequest;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\JsonResponse;

class CourseReviewController extends Controller
{
    public function index(Course $course): JsonResponse
    {
        $reviews = $course->reviews()->with('user:id,name')->latest()->paginate(15);

        return response()->json([
            'message' => 'Course reviews fetched successfully.',
            'data' => $reviews,
        ]);
    }

    public function store(StoreCourseReviewRequest $request, Course $course): JsonResponse
    {
        if (! $course->enrollments()->where('user_id', $request->user()->id)->exists()) {
            return response()->json([
                'message' => 'You must be enrolled before reviewing this course.',
            ], 422);
        }

        $review = CourseReview::query()->updateOrCreate(
            [
                'course_id' => $course->id,
                'user_id' => $request->user()->id,
            ],
            $request->validated(),
        );

        return response()->json([
            'message' => 'Course review saved successfully.',
            'data' => $review->load('user:id,name'),
        ], 201);
    }

    public function update(UpdateCourseReviewRequest $request, CourseReview $courseReview): JsonResponse
    {
        $this->authorize('update', $courseReview);

        $courseReview->update($request->validated());

        return response()->json([
            'message' => 'Course review updated successfully.',
            'data' => $courseReview->fresh('user:id,name'),
        ]);
    }

    public function destroy(UpdateCourseReviewRequest $request, CourseReview $courseReview): JsonResponse
    {
        $this->authorize('delete', $courseReview);

        $courseReview->delete();

        return response()->json([
            'message' => 'Course review deleted successfully.',
        ]);
    }
}
