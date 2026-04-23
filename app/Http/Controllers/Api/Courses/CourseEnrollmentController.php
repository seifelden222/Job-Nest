<?php

namespace App\Http\Controllers\Api\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Courses\StoreCourseEnrollmentRequest;
use App\Http\Requests\Api\Courses\UpdateCourseEnrollmentRequest;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseEnrollmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CourseEnrollment::class);

        $enrollments = CourseEnrollment::query()
            ->with(['course.owner:id,name', 'course.category:id,name,slug,type'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'message' => 'User course enrollments fetched successfully.',
            'data' => $enrollments,
        ]);
    }

    public function store(StoreCourseEnrollmentRequest $request, Course $course): JsonResponse
    {
        if ($course->status !== 'published' || ! $course->is_active) {
            return response()->json([
                'message' => 'You can only enroll in published active courses.',
            ], 422);
        }

        $user = $request->user();

        if (CourseEnrollment::query()->where('course_id', $course->id)->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'You are already enrolled in this course.',
            ], 409);
        }

        $paymentMethod = $request->validated('payment_method');
        $price = (float) $course->price;

        if ($price <= 0) {
            $paymentMethod = 'free';
        }

        $enrollment = CourseEnrollment::create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'status' => $price <= 0 ? 'enrolled' : 'pending',
            'payment_status' => $price <= 0 ? 'paid' : 'unpaid',
            'payment_method' => $paymentMethod,
            'amount_paid' => $price <= 0 ? 0 : 0,
            'enrolled_at' => $price <= 0 ? now() : null,
        ]);

        return response()->json([
            'message' => 'Course enrollment created successfully.',
            'data' => $enrollment->load(['course.owner:id,name']),
        ], 201);
    }

    public function providerIndex(Request $request, Course $course): JsonResponse
    {
        $this->authorize('viewEnrollments', $course);

        $enrollments = $course->enrollments()
            ->with('user:id,name,email,phone')
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'message' => 'Course enrollments fetched successfully.',
            'data' => $enrollments,
        ]);
    }

    public function update(UpdateCourseEnrollmentRequest $request, CourseEnrollment $courseEnrollment): JsonResponse
    {
        $this->authorize('update', $courseEnrollment);

        $payload = $request->validated();

        if (($payload['status'] ?? null) === 'enrolled' && $courseEnrollment->enrolled_at === null) {
            $payload['enrolled_at'] = now();
        }

        if (($payload['status'] ?? null) === 'completed') {
            $payload['completed_at'] = now();
        }

        $courseEnrollment->update($payload);

        return response()->json([
            'message' => 'Course enrollment updated successfully.',
            'data' => $courseEnrollment->fresh(['course', 'user:id,name,email,phone']),
        ]);
    }
}
