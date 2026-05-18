<?php

namespace Database\Seeders;

use App\Enums\SavedItemType;
use App\Models\Application;
use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\Conversation;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseReview;
use App\Models\Document;
use App\Models\Interest;
use App\Models\Job;
use App\Models\Language;
use App\Models\Message;
use App\Models\OtpCode;
use App\Models\PersonProfile;
use App\Models\RefreshToken;
use App\Models\SavedItem;
use App\Models\ServiceProposal;
use App\Models\ServiceRequest;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JobNestDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        fake()->seed(20260518);
        Date::setTestNow('2025-05-18 12:00:00');

        try {
            ($this->withoutModelEvents(function (): void {
                DB::transaction(function (): void {
                    $skills = Skill::query()->get();
                    $languages = Language::query()->get();
                    $interests = Interest::query()->get();
                    $jobCategories = Category::query()->where('type', 'job')->get();
                    $courseCategories = Category::query()->where('type', 'course')->get();
                    $serviceCategories = Category::query()->where('type', 'service')->get();

                    $companyUsers = $this->seedCompanies($skills);
                    $personUsers = $this->seedPeople($skills, $languages, $interests);
                    $documents = $this->seedDocuments($personUsers);

                    $jobs = $this->seedJobs($companyUsers, $jobCategories, $skills);
                    $applications = $this->seedApplications($personUsers, $jobs, $documents);
                    $courses = $this->seedCourses($companyUsers, $personUsers, $courseCategories, $skills);
                    $enrollments = $this->seedCourseEnrollments($courses, $personUsers);
                    $serviceRequests = $this->seedServiceRequests($companyUsers, $personUsers, $serviceCategories, $skills);
                    $proposals = $this->seedServiceProposals($serviceRequests, $companyUsers, $personUsers);

                    $this->seedApplicationConversations($applications);
                    $this->seedServiceConversations($proposals);
                    $this->seedChatbotConversations($personUsers);
                    $this->seedSavedItems($personUsers, $jobs, $courses, $serviceRequests);
                    $this->seedNotifications($personUsers, $companyUsers, $applications, $enrollments, $proposals);
                    $this->seedTokensAndOtps($personUsers, $companyUsers);
                }, 3);
            }))();
        } finally {
            Date::setTestNow();
        }
    }

    protected function seedCompanies(Collection $skills): Collection
    {
        $featuredCompanies = collect([
            [
                'user' => [
                    'name' => 'Injaz Labs',
                    'email' => 'hello@injazlabs.test',
                    'phone' => '01041000001',
                ],
                'profile' => [
                    'company_name' => 'Injaz Labs',
                    'website' => 'https://injazlabs.com',
                    'company_size' => '51-200',
                    'industry' => 'Technology',
                    'location' => 'New Cairo, Egypt',
                    'about' => 'Digital product studio building SaaS platforms, internal tools, and scalable engineering systems for growth-stage businesses across the region.',
                ],
                'skills' => ['Laravel', 'PHP', 'MySQL', 'REST API', 'Project Management'],
            ],
            [
                'user' => [
                    'name' => 'Nile Commerce',
                    'email' => 'careers@nilecommerce.test',
                    'phone' => '01041000002',
                ],
                'profile' => [
                    'company_name' => 'Nile Commerce',
                    'website' => 'https://nilecommerce.co',
                    'company_size' => '201-500',
                    'industry' => 'E-commerce',
                    'location' => 'Cairo, Egypt',
                    'about' => 'Omnichannel commerce company serving Egyptian and GCC brands with marketplace operations, growth marketing, and data-led merchandising.',
                ],
                'skills' => ['JavaScript', 'SEO', 'Paid Media', 'Content Strategy', 'Business Analysis'],
            ],
            [
                'user' => [
                    'name' => 'Cedar Health',
                    'email' => 'talent@cedarhealth.test',
                    'phone' => '01041000003',
                ],
                'profile' => [
                    'company_name' => 'Cedar Health',
                    'website' => 'https://cedarhealth.io',
                    'company_size' => '51-200',
                    'industry' => 'Healthcare',
                    'location' => 'Riyadh, Saudi Arabia',
                    'about' => 'Health-tech operator improving patient booking, provider engagement, and operational visibility for clinics and care networks.',
                ],
                'skills' => ['Data Visualization', 'SQL', 'Customer Success', 'Business Analysis'],
            ],
            [
                'user' => [
                    'name' => 'Atlas Logistics',
                    'email' => 'people@atlaslogistics.test',
                    'phone' => '01041000004',
                ],
                'profile' => [
                    'company_name' => 'Atlas Logistics',
                    'website' => 'https://atlaslogistics.co',
                    'company_size' => '500+',
                    'industry' => 'Logistics',
                    'location' => 'Alexandria, Egypt',
                    'about' => 'Logistics and operations group focused on fulfillment, route efficiency, and customer delivery experiences across multiple markets.',
                ],
                'skills' => ['Project Management', 'Customer Success', 'CRM', 'Leadership'],
            ],
            [
                'user' => [
                    'name' => 'Riwaq Education',
                    'email' => 'hiring@riwaqeducation.test',
                    'phone' => '01041000005',
                ],
                'profile' => [
                    'company_name' => 'Riwaq Education',
                    'website' => 'https://riwaqeducation.com',
                    'company_size' => '11-50',
                    'industry' => 'Education',
                    'location' => 'Dubai, UAE',
                    'about' => 'Career education provider combining cohort-based learning, mentor support, and market-ready training tracks for emerging talent.',
                ],
                'skills' => ['Project Management', 'Power BI', 'Flutter', 'Communication'],
            ],
            [
                'user' => [
                    'name' => 'Sprints Studio',
                    'email' => 'team@sprintsstudio.test',
                    'phone' => '01041000006',
                ],
                'profile' => [
                    'company_name' => 'Sprints Studio',
                    'website' => 'https://sprintsstudio.design',
                    'company_size' => '11-50',
                    'industry' => 'Creative Services',
                    'location' => 'Remote - MENA',
                    'about' => 'Creative partner for startups and SMEs delivering design systems, product interfaces, and brand-led user experiences.',
                ],
                'skills' => ['UI/UX', 'Figma', 'Brand Design', 'Motion Graphics'],
            ],
        ]);

        $companyUsers = $featuredCompanies->map(function (array $company) use ($skills): User {
            $user = User::factory()->company()->create(array_merge($company['user'], [
                'status' => 'active',
            ]));

            CompanyProfile::factory()->completed()->create(array_merge($company['profile'], [
                'user_id' => $user->id,
            ]));

            $user->skills()->sync($this->skillIds($skills, $company['skills']));

            return $user->fresh();
        });

        $additionalCompanies = User::factory()
            ->count(6)
            ->company()
            ->create()
            ->each(function (User $user) use ($skills): void {
                CompanyProfile::factory()->completed()->create([
                    'user_id' => $user->id,
                ]);

                $user->skills()->sync($skills->random(fake()->numberBetween(3, 5))->pluck('id')->all());
            });

        return $companyUsers->merge($additionalCompanies)->values();
    }

    protected function seedPeople(Collection $skills, Collection $languages, Collection $interests): Collection
    {
        $featuredPeople = collect([
            [
                'user' => ['name' => 'Ahmed Hassan', 'email' => 'ahmed.hassan@jobnest.test', 'phone' => '01051000001'],
                'profile' => [
                    'current_job_title' => 'Laravel Backend Engineer',
                    'company_name' => 'Freelance Projects',
                    'major' => 'Computer Science',
                    'university' => 'Cairo University',
                    'employment_status' => 'seeking_opportunities',
                    'employment_type' => 'full_time',
                    'preferred_work_location' => 'hybrid',
                    'expected_salary_min' => 18000,
                    'expected_salary_max' => 26000,
                    'about' => 'Backend engineer focused on APIs, integrations, and maintainable delivery for product teams serving real users.',
                ],
                'skills' => ['PHP', 'Laravel', 'MySQL', 'REST API', 'Git'],
                'languages' => ['Arabic', 'English'],
                'interests' => ['Web Development', 'Career Growth', 'DevOps'],
            ],
            [
                'user' => ['name' => 'Mariam Adel', 'email' => 'mariam.adel@jobnest.test', 'phone' => '01051000002'],
                'profile' => [
                    'current_job_title' => 'Flutter Developer',
                    'company_name' => 'Independent Consultant',
                    'major' => 'Software Engineering',
                    'university' => 'Ain Shams University',
                    'employment_status' => 'freelancer',
                    'employment_type' => 'contract',
                    'preferred_work_location' => 'remote',
                    'expected_salary_min' => 16000,
                    'expected_salary_max' => 24000,
                    'about' => 'Mobile engineer building practical Flutter apps with strong product thinking, clean integration work, and attention to user flows.',
                ],
                'skills' => ['Flutter', 'Dart', 'API Integration', 'UI/UX'],
                'languages' => ['Arabic', 'English'],
                'interests' => ['Mobile Development', 'Entrepreneurship'],
            ],
            [
                'user' => ['name' => 'Omar Samir', 'email' => 'omar.samir@jobnest.test', 'phone' => '01051000003'],
                'profile' => [
                    'current_job_title' => 'Data Analyst',
                    'company_name' => 'Cedar Health',
                    'major' => 'Information Systems',
                    'university' => 'Alexandria University',
                    'employment_status' => 'employed',
                    'employment_type' => 'full_time',
                    'preferred_work_location' => 'onsite',
                    'expected_salary_min' => 14000,
                    'expected_salary_max' => 22000,
                    'about' => 'Analyst with a practical approach to dashboards, reporting hygiene, and translating raw business questions into usable insights.',
                ],
                'skills' => ['SQL', 'Power BI', 'Data Visualization', 'Business Analysis'],
                'languages' => ['Arabic', 'English'],
                'interests' => ['Data Science', 'Career Growth'],
            ],
            [
                'user' => ['name' => 'Nour Khaled', 'email' => 'nour.khaled@jobnest.test', 'phone' => '01051000004'],
                'profile' => [
                    'current_job_title' => 'UI/UX Designer',
                    'company_name' => 'Sprints Studio',
                    'major' => 'Graphic Design',
                    'university' => 'German University in Cairo',
                    'employment_status' => 'employed',
                    'employment_type' => 'full_time',
                    'preferred_work_location' => 'remote',
                    'expected_salary_min' => 15000,
                    'expected_salary_max' => 23000,
                    'about' => 'Designer who enjoys turning rough requirements into coherent flows, scalable design systems, and polished handoff-ready interfaces.',
                ],
                'skills' => ['UI/UX', 'Figma', 'Brand Design', 'Communication'],
                'languages' => ['Arabic', 'English', 'French'],
                'interests' => ['UI/UX', 'Graphic Design'],
            ],
            [
                'user' => ['name' => 'Hoda Sherif', 'email' => 'hoda.sherif@jobnest.test', 'phone' => '01051000005'],
                'profile' => [
                    'current_job_title' => 'Digital Marketing Specialist',
                    'company_name' => 'Growth Hub Egypt',
                    'major' => 'Marketing',
                    'university' => 'Mansoura University',
                    'employment_status' => 'seeking_opportunities',
                    'employment_type' => 'part_time',
                    'preferred_work_location' => 'hybrid',
                    'expected_salary_min' => 12000,
                    'expected_salary_max' => 18000,
                    'about' => 'Growth marketer experienced in paid acquisition, content planning, and performance tracking for service and commerce brands.',
                ],
                'skills' => ['SEO', 'Content Strategy', 'Paid Media', 'Copywriting'],
                'languages' => ['Arabic', 'English'],
                'interests' => ['Digital Marketing', 'Customer Experience'],
            ],
            [
                'user' => ['name' => 'Karim Fathy', 'email' => 'karim.fathy@jobnest.test', 'phone' => '01051000006'],
                'profile' => [
                    'current_job_title' => 'Technical Recruiter',
                    'company_name' => 'Talent Bridge',
                    'major' => 'Business Administration',
                    'university' => 'Helwan University',
                    'employment_status' => 'freelancer',
                    'employment_type' => 'contract',
                    'preferred_work_location' => 'hybrid',
                    'expected_salary_min' => 10000,
                    'expected_salary_max' => 17000,
                    'about' => 'Recruitment specialist supporting startups and SMEs with sourcing, screening, and smoother candidate experience across hiring funnels.',
                ],
                'skills' => ['Recruitment', 'Communication', 'CRM', 'Customer Success'],
                'languages' => ['Arabic', 'English'],
                'interests' => ['Human Resources', 'Career Growth'],
            ],
        ]);

        $personUsers = $featuredPeople->map(function (array $person) use ($skills, $languages, $interests): User {
            $user = User::factory()->person()->create(array_merge($person['user'], [
                'status' => 'active',
            ]));

            PersonProfile::factory()->completed()->create(array_merge($person['profile'], [
                'user_id' => $user->id,
            ]));

            $user->skills()->sync($this->skillIds($skills, $person['skills']));
            $user->languages()->sync($this->nameIds($languages, $person['languages']));
            $user->interests()->sync($this->nameIds($interests, $person['interests']));

            return $user->fresh();
        });

        $additionalPeople = User::factory()
            ->count(26)
            ->person()
            ->create()
            ->each(function (User $user) use ($skills, $languages, $interests): void {
                PersonProfile::factory()->completed()->create([
                    'user_id' => $user->id,
                ]);

                $user->skills()->sync($skills->random(fake()->numberBetween(4, 7))->pluck('id')->all());
                $user->languages()->sync($languages->random(fake()->numberBetween(1, 3))->pluck('id')->all());
                $user->interests()->sync($interests->random(fake()->numberBetween(2, 4))->pluck('id')->all());
            });

        return $personUsers->merge($additionalPeople)->values();
    }

    protected function seedDocuments(Collection $personUsers): Collection
    {
        return $personUsers->mapWithKeys(function (User $user): array {
            $primaryCv = Document::factory()->cv()->create([
                'user_id' => $user->id,
                'title' => $user->name.' Resume',
            ]);

            Document::factory()
                ->count(fake()->numberBetween(0, 2))
                ->certificate()
                ->create([
                    'user_id' => $user->id,
                ]);

            return [$user->id => $primaryCv];
        });
    }

    protected function seedJobs(Collection $companyUsers, Collection $jobCategories, Collection $skills): Collection
    {
        $jobs = collect();

        foreach ($companyUsers as $companyUser) {
            $count = $companyUser->companyProfile?->company_size === '500+' ? 5 : fake()->numberBetween(2, 4);

            for ($index = 0; $index < $count; $index++) {
                $job = Job::factory()->create([
                    'company_id' => $companyUser->id,
                    'category_id' => $jobCategories->random()->id,
                ]);

                $job->skills()->sync($skills->random(fake()->numberBetween(3, 5))->pluck('id')->all());

                $jobs->push($job->fresh());
            }
        }

        return $jobs->values();
    }

    protected function seedApplications(Collection $personUsers, Collection $jobs, Collection $documents): Collection
    {
        $applications = collect();

        foreach ($jobs->where('status', 'active') as $job) {
            $applicants = $personUsers
                ->where('id', '!=', $job->company_id)
                ->shuffle()
                ->take(fake()->numberBetween(4, 8));

            foreach ($applicants as $applicant) {
                $applications->push(Application::factory()->create([
                    'job_id' => $job->id,
                    'user_id' => $applicant->id,
                    'cv_document_id' => $documents->get($applicant->id)?->id,
                ]));
            }

            $job->update([
                'applications_count' => $applications->where('job_id', $job->id)->count(),
            ]);
        }

        return $applications->values();
    }

    protected function seedCourses(Collection $companyUsers, Collection $personUsers, Collection $courseCategories, Collection $skills): Collection
    {
        $owners = $companyUsers->take(4)->merge($personUsers->take(6))->shuffle();
        $courses = collect();

        foreach ($owners as $owner) {
            $count = $owner->isCompany() ? fake()->numberBetween(2, 3) : fake()->numberBetween(1, 2);

            for ($index = 0; $index < $count; $index++) {
                $course = Course::factory()->create([
                    'user_id' => $owner->id,
                    'category_id' => $courseCategories->random()->id,
                ]);

                $course->skills()->sync($skills->random(fake()->numberBetween(2, 5))->pluck('id')->all());

                $courses->push($course->fresh());
            }
        }

        return $courses->values();
    }

    protected function seedCourseEnrollments(Collection $courses, Collection $personUsers): Collection
    {
        $enrollments = collect();

        foreach ($courses->where('status', 'published') as $course) {
            $learners = $personUsers
                ->where('id', '!=', $course->user_id)
                ->shuffle()
                ->take(fake()->numberBetween(5, 10));

            foreach ($learners as $learner) {
                $coursePrice = (float) $course->price;
                $shouldBePaid = $coursePrice > 0 && fake()->boolean(75);

                $enrollment = CourseEnrollment::factory()->create([
                    'course_id' => $course->id,
                    'user_id' => $learner->id,
                    'amount_paid' => $shouldBePaid ? $coursePrice : 0,
                    'payment_method' => $coursePrice === 0.0 ? 'free' : fake()->randomElement(['card', 'cash']),
                ]);

                if ($coursePrice === 0.0 && $enrollment->payment_status !== 'failed') {
                    $enrollment->update([
                        'payment_status' => 'paid',
                        'payment_method' => 'free',
                        'amount_paid' => 0,
                    ]);
                }

                if (($enrollment->status === 'completed' || fake()->boolean(35))
                    && ! CourseReview::query()->where('course_id', $course->id)->where('user_id', $learner->id)->exists()) {
                    CourseReview::factory()->create([
                        'course_id' => $course->id,
                        'user_id' => $learner->id,
                    ]);
                }

                $enrollments->push($enrollment->fresh());
            }
        }

        return $enrollments->values();
    }

    protected function seedServiceRequests(Collection $companyUsers, Collection $personUsers, Collection $serviceCategories, Collection $skills): Collection
    {
        $owners = $companyUsers->take(5)->merge($personUsers->take(7))->shuffle();
        $serviceRequests = collect();

        foreach ($owners as $owner) {
            $serviceRequest = ServiceRequest::factory()->create([
                'user_id' => $owner->id,
                'category_id' => $serviceCategories->random()->id,
            ]);

            $serviceRequest->skills()->sync($skills->random(fake()->numberBetween(2, 4))->pluck('id')->all());

            $serviceRequests->push($serviceRequest->fresh());
        }

        return $serviceRequests->values();
    }

    protected function seedServiceProposals(Collection $serviceRequests, Collection $companyUsers, Collection $personUsers): Collection
    {
        $providers = $companyUsers->merge($personUsers)->values();
        $proposals = collect();

        foreach ($serviceRequests as $serviceRequest) {
            $candidates = $providers
                ->where('id', '!=', $serviceRequest->user_id)
                ->shuffle()
                ->take(fake()->numberBetween(2, 4));

            foreach ($candidates as $candidate) {
                $proposals->push(ServiceProposal::factory()->create([
                    'service_request_id' => $serviceRequest->id,
                    'user_id' => $candidate->id,
                ]));
            }
        }

        return $proposals->values();
    }

    protected function seedApplicationConversations(Collection $applications): void
    {
        $applications
            ->shuffle()
            ->take(min(24, $applications->count()))
            ->each(function (Application $application): void {
                $conversation = Conversation::factory()
                    ->applicationType($application, $application->job, $application->user)
                    ->create();

                $this->attachParticipants($conversation, collect([$application->user, $application->job->company]));

                $messages = collect([
                    Message::factory()->create([
                        'conversation_id' => $conversation->id,
                        'sender_id' => $application->user_id,
                    ]),
                    Message::factory()->create([
                        'conversation_id' => $conversation->id,
                        'sender_id' => $application->job->company_id,
                    ]),
                    Message::factory()->system()->create([
                        'conversation_id' => $conversation->id,
                    ]),
                ]);

                $conversation->update([
                    'last_message_id' => $messages->last()->id,
                    'last_message_at' => $messages->last()->created_at,
                ]);
            });
    }

    protected function seedServiceConversations(Collection $proposals): void
    {
        $proposals
            ->shuffle()
            ->take(min(18, $proposals->count()))
            ->each(function (ServiceProposal $proposal): void {
                $conversation = Conversation::factory()
                    ->serviceType($proposal->serviceRequest, $proposal, $proposal->user)
                    ->create();

                $this->attachParticipants($conversation, collect([$proposal->user, $proposal->serviceRequest->owner]));

                $messages = collect([
                    Message::factory()->create([
                        'conversation_id' => $conversation->id,
                        'sender_id' => $proposal->user_id,
                    ]),
                    Message::factory()->create([
                        'conversation_id' => $conversation->id,
                        'sender_id' => $proposal->serviceRequest->user_id,
                    ]),
                ]);

                $conversation->update([
                    'last_message_id' => $messages->last()->id,
                    'last_message_at' => $messages->last()->created_at,
                ]);
            });
    }

    protected function seedChatbotConversations(Collection $personUsers): void
    {
        $personUsers->take(10)->each(function (User $user): void {
            $conversation = Conversation::factory()->chatbot($user)->create();

            $this->attachParticipants($conversation, collect([$user]));

            $messages = collect([
                Message::factory()->create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $user->id,
                    'body' => [
                        'en' => 'Can you suggest jobs that fit my Laravel and API background?',
                        'ar' => 'هل يمكنك اقتراح وظائف تناسب خبرتي في Laravel وواجهات البرمجة؟',
                    ],
                ]),
                Message::factory()->assistant()->create([
                    'conversation_id' => $conversation->id,
                ]),
            ]);

            $conversation->update([
                'last_message_id' => $messages->last()->id,
                'last_message_at' => $messages->last()->created_at,
            ]);
        });
    }

    protected function seedSavedItems(Collection $personUsers, Collection $jobs, Collection $courses, Collection $serviceRequests): void
    {
        $personUsers->each(function (User $user) use ($jobs, $courses, $serviceRequests): void {
            foreach ($jobs->where('status', 'active')->shuffle()->take(2)->pluck('id') as $jobId) {
                SavedItem::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'type' => SavedItemType::Job,
                    'target_id' => $jobId,
                ]);
            }

            foreach ($courses->where('status', 'published')->shuffle()->take(2)->pluck('id') as $courseId) {
                SavedItem::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'type' => SavedItemType::Course,
                    'target_id' => $courseId,
                ]);
            }

            foreach ($serviceRequests->where('status', 'open')->shuffle()->take(1)->pluck('id') as $serviceRequestId) {
                SavedItem::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'type' => SavedItemType::ServiceRequest,
                    'target_id' => $serviceRequestId,
                ]);
            }
        });
    }

    protected function seedNotifications(
        Collection $personUsers,
        Collection $companyUsers,
        Collection $applications,
        Collection $enrollments,
        Collection $proposals
    ): void {
        $rows = [];

        foreach ($applications->take(18) as $application) {
            $rows[] = $this->notificationRow(
                $application->user,
                'job.application.submitted',
                'Application update',
                'Your application is moving through the hiring pipeline.',
                'application',
                $application->id,
                Application::class
            );
        }

        foreach ($enrollments->take(12) as $enrollment) {
            $rows[] = $this->notificationRow(
                $enrollment->user,
                'course.enrollment.confirmed',
                'Course enrollment update',
                'Your course enrollment and payment details have been updated.',
                'course_enrollment',
                $enrollment->id,
                CourseEnrollment::class
            );
        }

        foreach ($proposals->take(12) as $proposal) {
            $rows[] = $this->notificationRow(
                $proposal->serviceRequest->owner,
                'service.proposal.received',
                'New proposal received',
                'A provider submitted a proposal for one of your active service requests.',
                'service_proposal',
                $proposal->id,
                ServiceProposal::class
            );
        }

        foreach ($companyUsers->take(4) as $companyUser) {
            $rows[] = $this->notificationRow(
                $companyUser,
                'talent.digest',
                'Talent activity digest',
                'Applications, saved jobs, and hiring conversations have new activity this week.',
                'digest',
                $companyUser->id,
                User::class
            );
        }

        foreach ($personUsers->take(4) as $personUser) {
            $rows[] = $this->notificationRow(
                $personUser,
                'recommendation.digest',
                'New opportunities for you',
                'We found new jobs, courses, and services matching your skills and recent activity.',
                'digest',
                $personUser->id,
                User::class
            );
        }

        DB::table('notifications')->insert($rows);
    }

    protected function seedTokensAndOtps(Collection $personUsers, Collection $companyUsers): void
    {
        $users = $personUsers->take(8)->merge($companyUsers->take(4));

        foreach ($users as $user) {
            $accessToken = $user->createToken(fake()->randomElement(['iPhone 15 Pro', 'Chrome Browser', 'Flutter Test Device']))->accessToken;

            RefreshToken::factory()->create([
                'user_id' => $user->id,
                'access_token_id' => $accessToken->id,
            ]);

            if (fake()->boolean(60)) {
                RefreshToken::factory()->revoked()->create([
                    'user_id' => $user->id,
                    'access_token_id' => $accessToken->id,
                ]);
            }

            OtpCode::factory()->create([
                'user_id' => $user->id,
                'email' => $user->email,
                'phone' => $user->phone,
            ]);
        }
    }

    protected function attachParticipants(Conversation $conversation, Collection $users): void
    {
        $timestamp = now();
        $pivot = $users
            ->unique('id')
            ->mapWithKeys(fn (User $user): array => [
                $user->id => [
                    'joined_at' => $timestamp->copy()->subHours(fake()->numberBetween(1, 96)),
                    'last_read_at' => fake()->boolean(70) ? $timestamp->copy()->subHours(fake()->numberBetween(0, 36)) : null,
                    'is_muted' => false,
                ],
            ])
            ->all();

        $conversation->participants()->syncWithoutDetaching($pivot);
    }

    protected function skillIds(Collection $skills, array $names): array
    {
        return $skills
            ->filter(fn (Skill $skill): bool => in_array(data_get($skill->name, 'en'), $names, true))
            ->pluck('id')
            ->all();
    }

    protected function nameIds(Collection $records, array $names): array
    {
        return $records
            ->filter(fn ($record): bool => in_array(data_get($record->name, 'en'), $names, true))
            ->pluck('id')
            ->all();
    }

    protected function notificationRow(
        User $user,
        string $type,
        string $title,
        string $body,
        string $actionType,
        int $relatedId,
        string $relatedType
    ): array {
        return [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'action_type' => $actionType,
                'related_id' => $relatedId,
                'related_type' => $relatedType,
                'meta' => [
                    'channel' => 'database',
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'read_at' => fake()->boolean(45) ? now()->subDays(fake()->numberBetween(0, 10)) : null,
            'created_at' => now()->subDays(fake()->numberBetween(0, 12)),
            'updated_at' => now(),
        ];
    }
}
