<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseReview;
use App\Models\Interest;
use App\Models\Job;
use App\Models\Language;
use App\Models\ServiceProposal;
use App\Models\ServiceRequest;
use App\Models\Skill;
use App\Policies\ApplicationPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\CourseEnrollmentPolicy;
use App\Policies\CoursePolicy;
use App\Policies\CourseReviewPolicy;
use App\Policies\InterestPolicy;
use App\Policies\JobPolicy;
use App\Policies\LanguagePolicy;
use App\Policies\ServiceProposalPolicy;
use App\Policies\ServiceRequestPolicy;
use App\Policies\SkillPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Job::class, JobPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(CourseEnrollment::class, CourseEnrollmentPolicy::class);
        Gate::policy(CourseReview::class, CourseReviewPolicy::class);
        Gate::policy(ServiceRequest::class, ServiceRequestPolicy::class);
        Gate::policy(ServiceProposal::class, ServiceProposalPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Skill::class, SkillPolicy::class);
        Gate::policy(Language::class, LanguagePolicy::class);
        Gate::policy(Interest::class, InterestPolicy::class);

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(mb_strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('forgot-password', function (Request $request): Limit {
            return Limit::perMinute(5)->by(mb_strtolower((string) $request->input('email_or_phone')).'|'.$request->ip());
        });

        RateLimiter::for('verify-reset-otp', function (Request $request): Limit {
            return Limit::perMinute(6)->by(mb_strtolower((string) $request->input('email_or_phone')).'|'.$request->ip());
        });

        RateLimiter::for('resend-reset-otp', function (Request $request): Limit {
            return Limit::perMinute(4)->by(mb_strtolower((string) $request->input('email_or_phone')).'|'.$request->ip());
        });

        RateLimiter::for('resend-verification', function (Request $request): Limit {
            return Limit::perMinute(3)->by((string) $request->user()?->id.'|'.$request->ip());
        });

        RateLimiter::for('refresh-token', function (Request $request): Limit {
            return Limit::perMinute(10)->by(hash('sha256', (string) $request->input('refresh_token')).'|'.$request->ip());
        });
    }
}
