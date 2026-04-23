<?php

namespace App\Notifications\Applications;

use App\Models\Application;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdatedNotification extends Notification
{
    public function __construct(
        public Application $application,
        public string $updatedByName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_status_updated',
            'title' => 'Application Status Updated',
            'body' => sprintf(
                'Your application for "%s" is now "%s".',
                $this->application->job->title ?? 'a job',
                str_replace('_', ' ', $this->application->status),
            ),
            'action_type' => 'application_status_updated',
            'related_id' => $this->application->id,
            'related_type' => 'application',
            'meta' => [
                'job_id' => $this->application->job_id,
                'job_title' => $this->application->job->title ?? null,
                'status' => $this->application->status,
                'notes' => $this->application->notes,
                'updated_by' => $this->updatedByName,
            ],
        ];
    }
}
