<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CompanySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanySettingController extends Controller
{
    public function show(): JsonResponse
    {
        $settings = CompanySetting::get();

        return response()->json(['settings' => $settings]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'string|max:255',
            'gst_number' => 'string|max:15',
            'drug_license_no' => 'string|max:50',
            'state_code' => 'string|size:2',
            'address_line1' => 'string|max:255',
            'city' => 'string|max:100',
            'state' => 'string|max:100',
            'pincode' => 'string|size:6',
            'phone' => 'string|max:15',
            'email' => 'nullable|email|max:255',
            'invoice_prefix' => 'string|max:10',
            'financial_year' => 'string|max:7',
        ]);

        $settings = CompanySetting::get();
        $oldValues = $settings->only(array_keys($validated));
        $settings->fill($validated);
        $settings->save();

        ActivityLog::log('company_settings.updated', 'company_setting', $settings->id, $oldValues, $validated);

        return response()->json(['settings' => $settings->fresh()]);
    }
}
