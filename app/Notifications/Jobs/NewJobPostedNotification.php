<?php

namespace App\Notifications\Jobs;

use App\Models\Job;
use Illuminate\Notifications\Notification;

class NewJobPostedNotification extends Notification
{
    public function __construct(public Job $job) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_job_posted',
            'title' => 'New Job Posted',
            'body' => sprintf('A new job "%s" may match your profile.', $this->job->title),
            'action_type' => 'job_posted',
            'related_id' => $this->job->id,
            'related_type' => 'job',
            'meta' => [
                'company_id' => $this->job->company_id,
                'category_id' => $this->job->category_id,
                'location' => $this->job->location,
                'employment_type' => $this->job->employment_type,
                'skill_ids' => $this->job->skills->pluck('id')->all(),
                'skill_names' => $this->job->skills->pluck('name')->all(),
            ],
        ];
    }
}
