<?php

namespace App\Http\Requests\Api\Courses;

use App\Models\CourseEnrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var CourseEnrollment|null $courseEnrollment */
        $courseEnrollment = $this->route('courseEnrollment');

        return $courseEnrollment instanceof CourseEnrollment
            && (int) $this->user()?->id === (int) $courseEnrollment->course->user_id;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['pending', 'enrolled', 'completed', 'cancelled'])],
            'payment_status' => ['nullable', Rule::in(['unpaid', 'paid', 'failed', 'refunded'])],
            'payment_method' => ['nullable', Rule::in(['card', 'cash', 'free'])],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
