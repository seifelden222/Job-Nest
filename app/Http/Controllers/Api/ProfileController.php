<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->isPerson()) {
            $user->load(['personProfile', 'skills', 'languages', 'interests', 'documents']);
        } elseif ($user->isCompany()) {
            $user->load(['companyProfile', 'documents']);
        } else {
            $user->load(['documents']);
        }

        return response()->json([
            'message' => 'Profile fetched successfully.',
            'data' => $user,
        ], 200);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $validated = $request->validated();

        // Update user-level fields
        $userFields = array_intersect_key($validated, array_flip(['name', 'phone']));
        if (! empty($userFields)) {
            $user->fill($userFields);
            $user->save();
        }

        if ($user->isPerson()) {
            $personFields = array_intersect_key($validated, array_flip([
                'about','university','major','employment_status','employment_type',
                'current_job_title','company_name','linkedin_url','portfolio_url',
                'preferred_work_location','expected_salary_min','expected_salary_max',
            ]));

            if (! empty($personFields)) {
                $user->personProfile()->updateOrCreate(['user_id' => $user->id], $personFields);
            }
        }

        if ($user->isCompany()) {
            $companyFields = array_intersect_key($validated, array_flip([
                'company_name','website','company_size','industry','location','about'
            ]));

            if (! empty($companyFields)) {
                $user->companyProfile()->updateOrCreate(['user_id' => $user->id], $companyFields);
            }
        }

        // reload relations
        if ($user->isPerson()) {
            $user->load(['personProfile', 'skills', 'languages', 'interests', 'documents']);
        } else {
            $user->load(['companyProfile', 'documents']);
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => $user,
        ], 200);
    }
}
