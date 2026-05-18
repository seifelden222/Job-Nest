<?php

use App\Models\Admin;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\Course;
use App\Models\Document;
use App\Models\Job;
use App\Models\RefreshToken;
use App\Models\SavedItem;
use App\Models\ServiceProposal;
use App\Models\ServiceRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;

test('jobnest database seeder builds a realistic linked demo dataset', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Admin::query()->count())->toBeGreaterThanOrEqual(3);
    expect(User::query()->where('account_type', 'company')->count())->toBeGreaterThanOrEqual(12);
    expect(User::query()->where('account_type', 'person')->count())->toBeGreaterThanOrEqual(30);

    expect(Job::query()->count())->toBeGreaterThanOrEqual(25);
    expect(Job::query()->where('status', 'active')->count())->toBeGreaterThan(0);
    expect(Application::query()->count())->toBeGreaterThanOrEqual(80);
    expect(Course::query()->count())->toBeGreaterThanOrEqual(12);
    expect(ServiceRequest::query()->count())->toBeGreaterThanOrEqual(10);
    expect(ServiceProposal::query()->count())->toBeGreaterThanOrEqual(20);
    expect(SavedItem::query()->count())->toBeGreaterThanOrEqual(60);
    expect(Conversation::query()->where('type', Conversation::TYPE_CHATBOT)->count())->toBeGreaterThanOrEqual(10);
    expect(RefreshToken::query()->count())->toBeGreaterThanOrEqual(12);
    expect(DB::table('notifications')->count())->toBeGreaterThanOrEqual(40);

    expect(User::query()->where('account_type', 'person')->whereDoesntHave('personProfile')->count())->toBe(0);
    expect(User::query()->where('account_type', 'company')->whereDoesntHave('companyProfile')->count())->toBe(0);
    expect(Job::query()->whereDoesntHave('skills')->count())->toBe(0);
    expect(Course::query()->whereDoesntHave('skills')->count())->toBe(0);
    expect(ServiceRequest::query()->whereDoesntHave('skills')->count())->toBe(0);
    expect(Application::query()->whereNull('cv_document_id')->count())->toBe(0);
    expect(Document::query()->where('type', 'cv')->where('is_primary', true)->count())->toBeGreaterThanOrEqual(30);
})->group('database', 'seeding');
