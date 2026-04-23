<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\User;

class CourseReviewPolicy
{
    public function create(User $user, Course $course): bool
    {
        return $user->isActive();
    }

    public function update(User $user, CourseReview $courseReview): bool
    {
        return $user->isActive()
            && (int) $courseReview->user_id === (int) $user->id;
    }

    public function delete(User $user, CourseReview $courseReview): bool
    {
        return $this->update($user, $courseReview);
    }
}
