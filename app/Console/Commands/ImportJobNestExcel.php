<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportJobNestExcel extends Command
{
    protected $signature = 'jobnest:import-excel {file=storage/app/imports/JobNest_EG_Enriched.xlsx} {--fresh : truncate import tables first}';

    protected $description = 'Import JobNest enriched Excel data into the existing JobNest database schema';

    private array $skillIdsByName = [];
    private array $languageIdsByName = [];
    private array $categoryIdsByTypeAndName = [];
    private array $conversationIdsByKey = [];
    private array $conversationLastMessages = [];
    private int $nextLanguageId = 1;
    private int $nextGeneratedSkillId = 10000;
    private int $nextGeneratedCategoryId = 10000;

    public function handle(): int
    {
        $path = base_path($this->argument('file'));

        if (! file_exists($path)) {
            $this->error("Excel file not found: {$path}");
            return self::FAILURE;
        }

        $spreadsheet = IOFactory::load($path);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
                if ($this->option('fresh')) {
                    $this->truncateImportTables();
                }

                $this->importSkillsAndCategories($spreadsheet);
                $this->importUsersCompaniesProfilesLanguagesAndDocuments($spreadsheet);
                $this->importJobsAndApplications($spreadsheet);
                $this->importCoursesEnrollmentsAndReviews($spreadsheet);
                $this->importServiceRequestsAndProposals($spreadsheet);
                $this->importConversationsAndMessages($spreadsheet);
                $this->importNotificationsAndSavedItems($spreadsheet);
                $this->syncAutoIncrementValues();
         } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->info('JobNest Excel import completed.');
        return self::SUCCESS;
    }

    private function truncateImportTables(): void
    {
        $tables = [
            'notifications', 'saved_items',
            'messages', 'conversation_participants', 'conversations',
            'service_proposals', 'service_request_skills', 'service_requests',
            'course_reviews', 'course_enrollments', 'course_skills', 'courses',
            'applications', 'job_skills', 'jobs',
            'documents', 'user_languages', 'languages', 'user_skills',
            'categories', 'company_profiles', 'person_profiles', 'admins', 'users',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
    }

    private function rows($spreadsheet, string $sheetName): array
    {
        $sheet = $spreadsheet->getSheetByName($sheetName);

        if (! $sheet) {
            $this->warn("Sheet not found: {$sheetName}");
            return [];
        }

        $raw = $sheet->toArray(null, true, true, true);
        $headerRow = array_shift($raw);

        $headers = [];
        foreach ($headerRow as $column => $name) {
            if ($name !== null && trim((string) $name) !== '') {
                $headers[$column] = trim((string) $name);
            }
        }

        $rows = [];
        foreach ($raw as $line) {
            $row = [];
            $hasValue = false;

            foreach ($headers as $column => $name) {
                $value = $line[$column] ?? null;
                $row[$name] = $value;

                if ($value !== null && $value !== '') {
                    $hasValue = true;
                }
            }

            if ($hasValue) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function upsert(string $table, array $where, array $values): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $cleanWhere = [];
        foreach ($where as $column => $value) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }

            $cleanWhere[$column] = $value;
        }

        $cleanValues = [];
        foreach ($values as $column => $value) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            $cleanValues[$column] = $this->normalizeForColumn($table, $column, $value);
        }

        DB::table($table)->updateOrInsert($cleanWhere, $cleanValues);
    }

    private function insertOrIgnore(string $table, array $values): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $cleanValues = [];
        foreach ($values as $column => $value) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            $cleanValues[$column] = $this->normalizeForColumn($table, $column, $value);
        }

        if ($cleanValues) {
            DB::table($table)->insertOrIgnore($cleanValues);
        }
    }

    private function onlyExistingColumns(string $table, array $values): array
    {
        $cleanValues = [];

        foreach ($values as $column => $value) {
            if (Schema::hasColumn($table, $column)) {
                $cleanValues[$column] = $this->normalizeForColumn($table, $column, $value);
            }
        }

        return $cleanValues;
    }

    private function normalizeForColumn(string $table, string $column, mixed $value): mixed
    {
        $columnName = strtolower($column);

        try {
            $columnType = Schema::getColumnType($table, $column);
        } catch (\Throwable) {
            $columnType = null;
        }

        if (in_array($columnName, ['created_at', 'updated_at'], true)) {
            return DB::raw('CURRENT_TIMESTAMP');
        }

        if ($this->isBooleanLikeColumn($columnName, $columnType)) {
            return $this->bool($value) ? 1 : 0;
        }

        if ($value === null || $value === '') {
            if ($this->isNumericColumnType($columnType)) {
                return null;
            }

            return null;
        }

        if ($columnName === 'delivery_mode') {
            return $this->deliveryMode($value);
        }

        if ($columnName === 'employment_type') {
            return $this->employmentType($value);
        }

        if ($columnName === 'experience_level') {
            return $this->experienceLevel($value);
        }

        if (in_array($columnName, ['preferred_work_location', 'work_mode'], true)) {
            return $this->workLocationMode($value);
        }

        if ($columnName === 'message_type') {
            return $this->messageType($value);
        }

        if (str_ends_with($columnName, '_at') || in_array($columnType, ['datetime', 'timestamp'], true)) {
            return now();
        }

        if (
            str_ends_with($columnName, '_date') ||
            in_array($columnName, ['deadline', 'start_date', 'end_date', 'published_date', 'posted_date'], true) ||
            $columnType === 'date'
        ) {
            return $this->date($value) ?? now()->toDateString();
        }

        if (in_array($columnType, ['decimal', 'float', 'double', 'real'], true)) {
            return $this->num($value);
        }

        if (in_array($columnType, ['integer', 'bigint', 'smallint', 'mediumint'], true)) {
            $number = $this->num($value);
            return $number === null ? null : (int) $number;
        }

        if ($columnType === 'tinyint') {
            $number = $this->num($value);
            return $number === null ? 0 : (int) $number;
        }

        return $value;
    }

    private function isNumericColumnType(?string $columnType): bool
    {
        return in_array($columnType, [
            'decimal', 'float', 'double', 'real',
            'integer', 'bigint', 'smallint', 'mediumint', 'tinyint',
        ], true);
    }

    private function isBooleanLikeColumn(string $columnName, ?string $columnType = null): bool
    {
        return $columnType === 'boolean' ||
            str_starts_with($columnName, 'is_') ||
            str_starts_with($columnName, 'has_') ||
            str_ends_with($columnName, '_completed') ||
            str_ends_with($columnName, '_active') ||
            str_ends_with($columnName, '_enabled') ||
            str_ends_with($columnName, '_primary') ||
            str_ends_with($columnName, '_muted') ||
            in_array($columnName, ['certificate_issued'], true);
    }

    private function txt(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function bool($value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['yes', 'true', '1', 'active', 'published'], true);
    }


    private function num($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = str_replace([',', ' '], '', (string) $value);
        $value = preg_replace('/[^0-9.\-]/', '', $value);

        if ($value === '' || $value === '-' || $value === '.' || $value === '-.') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function normalizedToken($value): string
    {
        $value = strtolower(trim((string) $value));
        $value = str_replace(['-', ' '], '_', $value);
        $value = preg_replace('/_+/', '_', $value);

        return trim((string) $value, '_');
    }

    private function deliveryMode($value): string
    {
        $mode = $this->normalizedToken($value);

        return match ($mode) {
            'onsite', 'on_site', 'in_person', 'physical' => 'offline',
            'remote' => 'online',
            'online', 'offline', 'hybrid' => $mode,
            default => 'online',
        };
    }

    private function workLocationMode($value): string
    {
        $mode = $this->normalizedToken($value);

        return match ($mode) {
            'on_site', 'in_person', 'physical' => 'onsite',
            'onsite', 'remote', 'hybrid' => $mode,
            default => 'remote',
        };
    }

    private function employmentType($value): string
    {
        $type = $this->normalizedToken($value);

        return match ($type) {
            'full_time', 'part_time', 'contract', 'freelance', 'internship' => $type,
            default => 'full_time',
        };
    }

    private function experienceLevel($value): string
    {
        $level = $this->normalizedToken($value);

        return match ($level) {
            'entry', 'entry_level', 'junior' => 'entry',
            'mid', 'middle', 'mid_level' => 'mid',
            'senior' => 'senior',
            'lead' => 'lead',
            default => 'entry',
        };
    }

    private function messageType($value): string
    {
        $type = $this->normalizedToken($value);

        return match ($type) {
            'file', 'attachment' => 'file',
            'system' => 'system',
            'text' => 'text',
            default => 'text',
        };
    }
    private function date($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->toDateString();
            }

            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function datetime($value): ?string
    {
        // Excel datetimes caused MySQL strict-mode errors in this project.
        // Timestamp-like DB columns are normalized in normalizeForColumn().
        return null;
    }

    private function jsonText($value): ?string
    {
        $value = $this->txt($value);
        if ($value === null) {
            return null;
        }

        return json_encode(['en' => $value, 'ar' => $value], JSON_UNESCAPED_UNICODE);
    }

    private function slug($value): string
    {
        return Str::slug($this->txt($value) ?? Str::uuid()->toString());
    }

    private function enum($value): ?string
    {
        $value = $this->txt($value);
        return $value ? $this->normalizedToken($value) : null;
    }

    private function jobStatus($value): string
    {
        $status = $this->enum($value) ?: 'active';
        return match ($status) {
            'active', 'draft', 'closed', 'archived' => $status,
            default => 'active',
        };
    }

    private function courseStatus($value): string
    {
        $status = $this->enum($value) ?: 'published';
        return match ($status) {
            'draft', 'published', 'closed', 'archived' => $status,
            'active' => 'published',
            default => 'published',
        };
    }

    private function serviceRequestStatus($value): string
    {
        $status = $this->enum($value) ?: 'open';
        return match ($status) {
            'open', 'in_progress', 'closed', 'cancelled' => $status,
            'active' => 'open',
            default => 'open',
        };
    }

    private function applicationStatus($value): string
    {
        $status = $this->enum($value) ?: 'submitted';

        return match ($status) {
            'under_review', 'reviewed' => 'under_review',
            'interview_scheduled' => 'under_review',
            'accepted', 'rejected', 'withdrawn', 'submitted' => $status,
            default => 'submitted',
        };
    }

    private function proposalStatus($value): string
    {
        $status = $this->enum($value) ?: 'submitted';
        return match ($status) {
            'submitted', 'accepted', 'rejected', 'withdrawn' => $status,
            default => 'submitted',
        };
    }

    private function enrollmentStatus($value): string
    {
        $status = $this->enum($value) ?: 'enrolled';
        return match ($status) {
            'pending', 'enrolled', 'completed', 'cancelled' => $status,
            default => 'enrolled',
        };
    }

    private function paymentStatus($value): string
    {
        $status = $this->enum($value) ?: 'unpaid';
        return match ($status) {
            'unpaid', 'paid', 'failed', 'refunded' => $status,
            default => 'unpaid',
        };
    }

    private function paymentMethod($value): ?string
    {
        $method = $this->enum($value);
        return match ($method) {
            'credit_card', 'card' => 'card',
            'cash' => 'cash',
            'free' => 'free',
            null => null,
            default => $method,
        };
    }

    private function splitList($value): array
    {
        $value = $this->txt($value);
        if (! $value) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function importSkillsAndCategories($spreadsheet): void
    {
        foreach ($this->rows($spreadsheet, 'Skills_Categories') as $row) {
            $type = $this->txt($row['Record_Type'] ?? null);
            $name = $this->txt($row['Name'] ?? null);

            if (! $name) {
                continue;
            }

            if ($type === 'Skill') {
                $id = (int) $row['Record_ID'];
                $this->upsert('skills', ['id' => $id], [
                    'id' => $id,
                    'name' => $this->jsonText($name),
                    'category' => $this->txt($row['Category'] ?? null),
                    'sub_category' => $this->txt($row['Sub_Category'] ?? null),
                    'description' => $this->jsonText($row['Description'] ?? null),
                    'is_active' => $this->bool($row['Is_Active'] ?? 'Yes'),
                    'usage_count' => $row['Usage_Count'] ?? null,
                    'avg_salary_egp' => $row['Avg_Salary_EGP'] ?? null,
                    'market_demand' => $this->txt($row['Market_Demand'] ?? null),
                    'created_at' => $this->datetime($row['Created_Date'] ?? null) ?? now(),
                    'updated_at' => now(),
                ]);

                $this->skillIdsByName[strtolower($name)] = $id;
                continue;
            }

            $categoryType = match ($type) {
                'Job Category' => 'job',
                'Course Category' => 'course',
                'Service Category' => 'service',
                default => null,
            };

            if ($categoryType) {
                $id = (int) $row['Record_ID'];
                $this->upsert('categories', ['id' => $id], [
                    'id' => $id,
                    'name' => $this->jsonText($name),
                    'slug' => $this->slug($name),
                    'type' => $categoryType,
                    'description' => $this->jsonText($row['Description'] ?? null),
                    'is_active' => $this->bool($row['Is_Active'] ?? 'Yes'),
                    'created_at' => $this->datetime($row['Created_Date'] ?? null) ?? now(),
                    'updated_at' => now(),
                ]);

                $this->categoryIdsByTypeAndName[$categoryType][strtolower($name)] = $id;
            }
        }

        $this->info('Imported skills and categories.');
    }

    private function importUsersCompaniesProfilesLanguagesAndDocuments($spreadsheet): void
    {
        foreach ($this->rows($spreadsheet, 'Users_Companies') as $row) {
            $userId = (int) $row['User_ID'];
            $rawAccountType = strtolower((string) ($row['Account_Type'] ?? 'person'));
            $accountType = $rawAccountType === 'company' ? 'company' : 'person';
            $createdAt = $this->datetime($row['Joined_Date'] ?? null) ?? now();
            $updatedAt = $this->datetime($row['Last_Updated'] ?? null) ?? now();

            $this->upsert('users', ['id' => $userId], [
                'id' => $userId,
                'name' => $this->txt($row['Full_Name'] ?? null),
                'email' => $this->txt($row['Email'] ?? null),
                'phone' => $this->txt($row['Phone'] ?? null),
                'account_type' => $accountType,
                'status' => strtolower((string) ($row['Status'] ?? 'active')),
                'email_verified_at' => $this->bool($row['Email_Verified'] ?? null) ? $createdAt : null,
                'password' => Hash::make('Password123!'),
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
                'last_login_at' => $this->datetime($row['Last_Login'] ?? null),
            ]);

            if ($rawAccountType === 'admin') {
                $email = $this->txt($row['Email'] ?? null);

                if ($email) {
                    $adminData = [
                        'name' => $this->txt($row['Full_Name'] ?? null),
                        'email' => $email,
                        'phone' => $this->txt($row['Phone'] ?? null),
                        'status' => strtolower((string) ($row['Status'] ?? 'active')),
                        'last_login_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (Schema::hasColumn('admins', 'password')) {
                        $adminData['password'] = Hash::make('Password123!');
                    }

                    $this->upsert('admins', ['email' => $email], $adminData);
                }
            }

            if ($accountType === 'person' && $rawAccountType !== 'admin') {
                $this->upsert('person_profiles', ['user_id' => $userId], [
                    'user_id' => $userId,
                    'university' => $this->txt($row['University'] ?? null),
                    'major' => $this->txt($row['Major'] ?? null),
                    'employment_status' => $this->txt($row['Employment_Status'] ?? null),
                    'employment_type' => $this->enum($row['Employment_Type'] ?? null),
                    'current_job_title' => $this->txt($row['Current_Job_Title'] ?? null),
                    'company_name' => $this->txt($row['Current_Company'] ?? null),
                    'linkedin_url' => $this->txt($row['LinkedIn_URL'] ?? null),
                    'portfolio_url' => $this->txt($row['Portfolio_URL'] ?? null),
                    'preferred_work_location' => $this->enum($row['Preferred_Work_Location'] ?? null),
                    'expected_salary_min' => $row['Expected_Salary_Min_EGP'] ?? null,
                    'expected_salary_max' => $row['Expected_Salary_Max_EGP'] ?? null,
                    'onboarding_step' => $row['Onboarding_Step'] ?? 5,
                    'is_profile_completed' => $this->bool($row['Profile_Completed'] ?? null),
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);
            }

            if ($accountType === 'company') {
                $this->upsert('company_profiles', ['user_id' => $userId], [
                    'user_id' => $userId,
                    'company_name' => $this->txt($row['Company_Name'] ?? $row['Full_Name'] ?? null),
                    'website' => $this->txt($row['Website'] ?? null),
                    'company_size' => $this->txt($row['Company_Size'] ?? null),
                    'industry' => $this->txt($row['Industry'] ?? null),
                    'location' => $this->txt($row['Company_Location'] ?? null),
                    'about' => $this->txt($row['Company_About'] ?? null),
                    'onboarding_step' => $row['Onboarding_Step'] ?? 5,
                    'is_profile_completed' => $this->bool($row['Profile_Completed'] ?? null),
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);
            }

            foreach ($this->splitList($row['Skills'] ?? null) as $skillName) {
                $skillId = $this->findOrCreateSkill($skillName);
                $this->insertOrIgnore('user_skills', [
                    'user_id' => $userId,
                    'skill_id' => $skillId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($this->splitList($row['Languages'] ?? null) as $languageName) {
                $languageId = $this->findOrCreateLanguage($languageName);
                $this->insertOrIgnore('user_languages', [
                    'user_id' => $userId,
                    'language_id' => $languageId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($this->bool($row['Has_CV'] ?? null)) {
                $this->ensureCvDocument($userId);
            }
        }

        $this->info('Imported users, profiles, languages, user skills, user languages, and CV documents.');
    }

    private function findOrCreateSkill(string $name): int
    {
        $key = strtolower($name);

        if (isset($this->skillIdsByName[$key])) {
            return $this->skillIdsByName[$key];
        }

        $id = $this->nextGeneratedSkillId++;
        $this->upsert('skills', ['id' => $id], [
            'id' => $id,
            'name' => $this->jsonText($name),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->skillIdsByName[$key] = $id;
    }

    private function findOrCreateLanguage(string $name): int
    {
        $key = strtolower($name);

        if (isset($this->languageIdsByName[$key])) {
            return $this->languageIdsByName[$key];
        }

        $id = $this->nextLanguageId++;
        $this->upsert('languages', ['id' => $id], [
            'id' => $id,
            'name' => $this->jsonText($name),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->languageIdsByName[$key] = $id;
    }

    private function findOrCreateCategory(string $type, string $name): int
    {
        $key = strtolower($name);

        if (isset($this->categoryIdsByTypeAndName[$type][$key])) {
            return $this->categoryIdsByTypeAndName[$type][$key];
        }

        $id = $this->nextGeneratedCategoryId++;
        $this->upsert('categories', ['id' => $id], [
            'id' => $id,
            'name' => $this->jsonText($name),
            'slug' => $this->slug($type . '-' . $name),
            'type' => $type,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->categoryIdsByTypeAndName[$type][$key] = $id;
    }

    private function ensureCvDocument(int $userId): int
    {
        $documentId = 100000 + $userId;

        $this->upsert('documents', ['id' => $documentId], [
            'id' => $documentId,
            'user_id' => $userId,
            'type' => 'cv',
            'title' => 'Imported CV',
            'file_path' => "imports/cvs/user_{$userId}.pdf",
            'file_name' => "user_{$userId}_cv.pdf",
            'mime_type' => 'application/pdf',
            'file_size' => 0,
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $documentId;
    }

    private function importJobsAndApplications($spreadsheet): void
    {
        foreach ($this->rows($spreadsheet, 'Jobs_Applications') as $row) {
            if (($row['Record_Type'] ?? null) === 'Job') {
                $jobId = (int) $row['Job_ID'];
                $categoryId = $this->findOrCreateCategory('job', $this->txt($row['Category'] ?? 'General') ?? 'General');
                $location = collect([$row['Location_City'] ?? null, $row['Location_District'] ?? null])->filter()->implode(', ');

                $this->upsert('jobs', ['id' => $jobId], [
                    'id' => $jobId,
                    'company_id' => (int) $row['Company_ID'],
                    'category_id' => $categoryId,
                    'industry' => $this->txt($row['Industry'] ?? null),
                    'title' => $this->jsonText($row['Job_Title'] ?? null),
                    'description' => $this->jsonText($row['Job_Description'] ?? null),
                    'location' => $location ?: null,
                    'location_city' => $this->txt($row['Location_City'] ?? null),
                    'location_district' => $this->txt($row['Location_District'] ?? null),
                    'employment_type' => $this->enum($row['Employment_Type'] ?? null),
                    'experience_level' => $this->enum($row['Experience_Level'] ?? null),
                    'work_mode' => $this->enum($row['Work_Mode'] ?? null),
                    'salary_min' => $row['Salary_Min_EGP'] ?? null,
                    'salary_max' => $row['Salary_Max_EGP'] ?? null,
                    'salary_min_egp' => $row['Salary_Min_EGP'] ?? null,
                    'salary_max_egp' => $row['Salary_Max_EGP'] ?? null,
                    'currency' => $this->txt($row['Currency'] ?? 'EGP'),
                    'requirements' => $this->jsonText($row['Required_Skills'] ?? null),
                    'deadline' => $this->date($row['Deadline_Date'] ?? null),
                    'deadline_date' => $this->date($row['Deadline_Date'] ?? null),
                    'status' => $this->jobStatus($row['Job_Status'] ?? null),
                    'is_active' => $this->bool($row['Is_Active'] ?? null),
                    'ai_matching_enabled' => $this->bool($row['AI_Matching_Enabled'] ?? null),
                    'applications_count' => $row['Applications_Count'] ?? 0,
                    'posted_date' => $this->date($row['Posted_Date'] ?? null),
                    'created_at' => $this->datetime($row['Posted_Date'] ?? null) ?? now(),
                    'updated_at' => now(),
                ]);

                foreach ($this->splitList($row['Required_Skills'] ?? null) as $skillName) {
                    $this->insertOrIgnore('job_skills', [
                        'job_id' => $jobId,
                        'skill_id' => $this->findOrCreateSkill($skillName),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        foreach ($this->rows($spreadsheet, 'Jobs_Applications') as $row) {
            if (($row['Record_Type'] ?? null) !== 'Application') {
                continue;
            }

            $applicantId = (int) $row['Applicant_ID'];

            $this->upsert('applications', ['id' => (int) $row['Application_ID']], [
                'id' => (int) $row['Application_ID'],
                'job_id' => (int) $row['Job_ID'],
                'user_id' => $applicantId,
                'cv_document_id' => $this->bool($row['Has_CV'] ?? null) ? $this->ensureCvDocument($applicantId) : null,
                'cover_letter' => $this->jsonText($row['Cover_Letter'] ?? null),
                'status' => $this->applicationStatus($row['Application_Status'] ?? null),
                'match_percentage' => $row['Match_Score_Pct'] ?? null,
                'match_score_pct' => $row['Match_Score_Pct'] ?? null,
                'applied_at' => $this->datetime($row['Applied_Date'] ?? null),
                'reviewed_at' => $this->datetime($row['Reviewed_Date'] ?? null),
                'withdrawn_at' => $this->datetime($row['Withdrawn_Date'] ?? null),
                'notes' => $this->txt($row['Reviewer_Notes'] ?? null),
                'created_at' => $this->datetime($row['Applied_Date'] ?? null) ?? now(),
                'updated_at' => now(),
            ]);
        }

        $this->info('Imported jobs, job skills, and applications.');
    }

    private function importCoursesEnrollmentsAndReviews($spreadsheet): void
    {
        foreach ($this->rows($spreadsheet, 'Courses_Enrollments') as $row) {
            if (($row['Record_Type'] ?? null) !== 'Course') {
                continue;
            }

            $courseId = (int) $row['Course_ID'];
            $categoryId = $this->findOrCreateCategory('course', $this->txt($row['Category'] ?? 'General') ?? 'General');

            $this->upsert('courses', ['id' => $courseId], [
                'id' => $courseId,
                'user_id' => (int) $row['Publisher_Company_ID'],
                'category_id' => $categoryId,
                'title' => $this->jsonText($row['Title'] ?? null),
                'slug' => $this->slug(($row['Title'] ?? 'course') . '-' . $courseId),
                'description' => $this->jsonText($row['Description'] ?? null),
                'short_description' => $this->jsonText($row['Description'] ?? null),
                'level' => $this->enum($row['Level'] ?? null),
                'delivery_mode' => $this->enum($row['Delivery_Mode'] ?? null),
                'language' => $this->txt($row['Language'] ?? null),
                'price' => $row['Price_EGP'] ?? null,
                'price_egp' => $row['Price_EGP'] ?? null,
                'currency' => $this->txt($row['Currency'] ?? 'EGP'),
                'duration_hours' => $row['Duration_Hours'] ?? null,
                'seats_count' => $row['Seats_Available'] ?? null,
                'seats_available' => $row['Seats_Available'] ?? null,
                'average_rating' => $row['Average_Rating'] ?? null,
                'total_reviews' => $row['Total_Reviews'] ?? null,
                'total_enrollments' => $row['Total_Enrollments'] ?? null,
                'start_date' => $this->date($row['Start_Date'] ?? null),
                'end_date' => $this->date($row['End_Date'] ?? null),
                'status' => $this->courseStatus($row['Course_Status'] ?? null),
                'is_active' => $this->bool($row['Is_Active'] ?? null),
                'published_date' => $this->date($row['Published_Date'] ?? null),
                'created_at' => $this->datetime($row['Published_Date'] ?? null) ?? now(),
                'updated_at' => now(),
            ]);

            foreach ($this->splitList($row['Skills_Covered'] ?? null) as $skillName) {
                $this->insertOrIgnore('course_skills', [
                    'course_id' => $courseId,
                    'skill_id' => $this->findOrCreateSkill($skillName),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($this->rows($spreadsheet, 'Courses_Enrollments') as $row) {
            if (($row['Record_Type'] ?? null) !== 'Enrollment') {
                continue;
            }

            $courseId = (int) $row['Course_ID'];
            $learnerId = (int) $row['Learner_ID'];

            $this->upsert('course_enrollments', ['id' => (int) $row['Enrollment_ID']], [
                'id' => (int) $row['Enrollment_ID'],
                'course_id' => $courseId,
                'user_id' => $learnerId,
                'status' => $this->enrollmentStatus($row['Enrollment_Status'] ?? null),
                'payment_status' => $this->paymentStatus($row['Payment_Status'] ?? null),
                'payment_method' => $this->paymentMethod($row['Payment_Method'] ?? null),
                'amount_paid' => $row['Amount_Paid_EGP'] ?? null,
                'amount_paid_egp' => $row['Amount_Paid_EGP'] ?? null,
                'progress_pct' => $row['Progress_Pct'] ?? null,
                'certificate_issued' => $this->bool($row['Certificate_Issued'] ?? null),
                'enrolled_at' => $this->datetime($row['Enrolled_Date'] ?? null),
                'completed_at' => $this->datetime($row['Completed_Date'] ?? null),
                'created_at' => $this->datetime($row['Enrolled_Date'] ?? null) ?? now(),
                'updated_at' => now(),
            ]);

            if ($this->bool($row['Has_Review'] ?? null) && ! empty($row['Review_Rating'])) {
                $this->upsert('course_reviews', ['course_id' => $courseId, 'user_id' => $learnerId], [
                    'course_id' => $courseId,
                    'user_id' => $learnerId,
                    'rating' => $row['Review_Rating'],
                    'comment' => $this->jsonText($row['Review_Comment'] ?? null),
                    'created_at' => $this->datetime($row['Review_Date'] ?? null) ?? now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->info('Imported courses, course skills, enrollments, and reviews.');
    }

    private function importServiceRequestsAndProposals($spreadsheet): void
    {
        foreach ($this->rows($spreadsheet, 'Service_Requests_Proposals') as $row) {
            if (($row['Record_Type'] ?? null) !== 'Service Request') {
                continue;
            }

            $requestId = (int) $row['Request_ID'];
            $categoryId = $this->findOrCreateCategory('service', $this->txt($row['Category'] ?? 'General') ?? 'General');

            $this->upsert('service_requests', ['id' => $requestId], [
                'id' => $requestId,
                'user_id' => (int) $row['Owner_ID'],
                'category_id' => $categoryId,
                'title' => $this->jsonText($row['Title'] ?? null),
                'description' => $this->jsonText($row['Description'] ?? null),
                'budget_min' => $row['Budget_Min_EGP'] ?? null,
                'budget_max' => $row['Budget_Max_EGP'] ?? null,
                'budget_min_egp' => $row['Budget_Min_EGP'] ?? null,
                'budget_max_egp' => $row['Budget_Max_EGP'] ?? null,
                'currency' => $this->txt($row['Currency'] ?? 'EGP'),
                'delivery_mode' => $this->enum($row['Delivery_Mode'] ?? null),
                'deadline' => $this->date($row['Deadline_Date'] ?? null),
                'deadline_date' => $this->date($row['Deadline_Date'] ?? null),
                'status' => $this->serviceRequestStatus($row['Request_Status'] ?? null),
                'total_proposals' => $row['Total_Proposals'] ?? null,
                'created_at' => $this->datetime($row['Posted_Date'] ?? null) ?? now(),
                'updated_at' => $this->datetime($row['Last_Updated'] ?? null) ?? now(),
            ]);

            foreach ($this->splitList($row['Required_Skills'] ?? null) as $skillName) {
                $this->insertOrIgnore('service_request_skills', [
                    'service_request_id' => $requestId,
                    'skill_id' => $this->findOrCreateSkill($skillName),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($this->rows($spreadsheet, 'Service_Requests_Proposals') as $row) {
            if (($row['Record_Type'] ?? null) !== 'Proposal') {
                continue;
            }

            $this->upsert('service_proposals', ['id' => (int) $row['Proposal_ID']], [
                'id' => (int) $row['Proposal_ID'],
                'service_request_id' => (int) $row['Request_ID'],
                'user_id' => (int) $row['Proposer_ID'],
                'message' => $this->jsonText($row['Proposal_Message'] ?? null),
                'proposed_budget' => $row['Proposed_Budget_EGP'] ?? null,
                'proposed_budget_egp' => $row['Proposed_Budget_EGP'] ?? null,
                'delivery_days' => $row['Delivery_Days'] ?? null,
                'status' => $this->proposalStatus($row['Proposal_Status'] ?? null),
                'is_hired' => $this->bool($row['Is_Hired'] ?? null),
                'created_at' => $this->datetime($row['Submitted_Date'] ?? null) ?? now(),
                'updated_at' => $this->datetime($row['Response_Date'] ?? null) ?? now(),
            ]);
        }

        $this->info('Imported service requests, request skills, and proposals.');
    }

    private function importConversationsAndMessages($spreadsheet): void
    {
        foreach ($this->rows($spreadsheet, 'Conversations_Messages') as $row) {
            $conversationId = $this->conversationIdFor($row);
            $type = $this->conversationType($row['Conversation_Type'] ?? null);
            $referenceId = (int) ($row['Reference_ID'] ?? 0);
            $senderId = (int) $row['Sender_ID'];
            $recipientId = (int) $row['Recipient_ID'];
            $sentAt = $this->datetime($row['Sent_At'] ?? null) ?? now();

            $conversationValues = [
                'id' => $conversationId,
                'type' => $type,
                'created_by' => $senderId,
                'application_id' => $type === 'application' ? $referenceId : null,
                'job_id' => null,
                'service_request_id' => $type === 'service' ? $referenceId : null,
                'service_proposal_id' => null,
                'last_message_at' => $sentAt,
                'created_at' => $sentAt,
                'updated_at' => $sentAt,
            ];

            $this->upsert('conversations', ['id' => $conversationId], $conversationValues);

            foreach ([$senderId, $recipientId] as $participantId) {
                $this->insertOrIgnore('conversation_participants', [
                    'conversation_id' => $conversationId,
                    'user_id' => $participantId,
                    'joined_at' => $sentAt,
                    'last_read_at' => ($participantId === $recipientId && $this->bool($row['Is_Read'] ?? null)) ? $sentAt : null,
                    'is_muted' => false,
                    'created_at' => $sentAt,
                    'updated_at' => $sentAt,
                ]);
            }

            $messageId = (int) $row['Message_ID'];
            $this->upsert('messages', ['id' => $messageId], [
                'id' => $messageId,
                'conversation_id' => $conversationId,
                'sender_id' => $senderId,
                'message_role' => 'user',
                'message_type' => $this->enum($row['Message_Type'] ?? 'Text') ?: 'text',
                'body' => $this->jsonText($row['Message_Body'] ?? null),
                'is_edited' => $this->bool($row['Is_Edited'] ?? null),
                'created_at' => $sentAt,
                'updated_at' => $sentAt,
            ]);

            if (! isset($this->conversationLastMessages[$conversationId]) || $sentAt >= $this->conversationLastMessages[$conversationId]['at']) {
                $this->conversationLastMessages[$conversationId] = ['id' => $messageId, 'at' => $sentAt];
            }
        }

        foreach ($this->conversationLastMessages as $conversationId => $message) {
            $this->upsert('conversations', ['id' => $conversationId], [
                'last_message_id' => $message['id'],
                'last_message_at' => $message['at'],
                'updated_at' => $message['at'],
            ]);
        }

        $this->info('Imported conversations, participants, and messages.');
    }

    private function conversationType($value): string
    {
        return match (strtolower((string) $value)) {
            'application' => 'application',
            'service' => 'service',
            'direct' => 'direct',
            default => 'direct',
        };
    }

    private function conversationIdFor(array $row): int
    {
        $type = $this->conversationType($row['Conversation_Type'] ?? null);
        $referenceId = (int) ($row['Reference_ID'] ?? 0);
        $senderId = (int) ($row['Sender_ID'] ?? 0);
        $recipientId = (int) ($row['Recipient_ID'] ?? 0);

        if ($type === 'application') {
            return 100000 + $referenceId;
        }

        if ($type === 'service') {
            return 200000 + $referenceId;
        }

        $a = min($senderId, $recipientId);
        $b = max($senderId, $recipientId);
        return 300000 + ($a * 10000) + $b;
    }

    private function importNotificationsAndSavedItems($spreadsheet): void
    {
        foreach ($this->rows($spreadsheet, 'Notifications_Saved_Items') as $row) {
            if (($row['Record_Type'] ?? null) === 'Notification') {
                $id = $this->notificationUuid((int) $row['Notification_ID']);

                $this->upsert('notifications', ['id' => $id], [
                    'id' => $id,
                    'type' => $this->txt($row['Notification_Type'] ?? 'imported_notification'),
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => (int) $row['User_ID'],
                    'data' => json_encode([
                        'notification_type' => $this->txt($row['Notification_Type'] ?? null),
                        'title' => $this->txt($row['Title'] ?? null),
                        'body' => $this->txt($row['Body'] ?? null),
                        'channel' => $this->txt($row['Channel'] ?? null),
                    ], JSON_UNESCAPED_UNICODE),
                    'read_at' => $this->bool($row['Is_Read'] ?? null) ? ($this->datetime($row['Read_At'] ?? null) ?? now()) : null,
                    'created_at' => $this->datetime($row['Created_At'] ?? null) ?? now(),
                    'updated_at' => now(),
                ]);
            }

            if (($row['Record_Type'] ?? null) === 'Saved Item') {
                $type = $this->savedType($row['Item_Type'] ?? null);
                if (! $type) {
                    continue;
                }

                $this->upsert('saved_items', ['id' => (int) $row['Saved_Item_ID']], [
                    'id' => (int) $row['Saved_Item_ID'],
                    'user_id' => (int) $row['User_ID'],
                    'type' => $type,
                    'target_id' => (int) $row['Saved_Target_ID'],
                    'created_at' => $this->datetime($row['Created_At'] ?? null) ?? now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->info('Imported notifications and saved items.');
    }

    private function savedType($value): ?string
    {
        $value = strtolower(trim((string) $value));

        return match ($value) {
            'job' => 'job',
            'course' => 'course',
            'service request', 'service_request', 'service' => 'service_request',
            default => null,
        };
    }

    private function notificationUuid(int $id): string
    {
        return '00000000-0000-0000-0000-' . str_pad((string) $id, 12, '0', STR_PAD_LEFT);
    }

    private function syncAutoIncrementValues(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $tables = [
            'users', 'skills', 'languages', 'categories', 'documents', 'jobs', 'applications',
            'courses', 'course_enrollments', 'service_requests', 'service_proposals',
            'conversations', 'messages', 'saved_items',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'id')) {
                continue;
            }

            $max = (int) DB::table($table)->max('id');
            if ($max > 0) {
                DB::statement('ALTER TABLE `' . $table . '` AUTO_INCREMENT = ' . ($max + 1));
            }
        }
    }
}
