<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Client::with('user:id,full_name,phone,email');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('drug_license_no', 'like', "%{$search}%")
                  ->orWhere('gst_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('phone', 'like', "%{$search}%"));
            });
        }

        if ($request->has('kyc_verified')) {
            $query->where('kyc_verified', $request->boolean('kyc_verified'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $clients = $query->orderBy('business_name')->paginate($request->get('per_page', 25));

        return response()->json($clients);
    }

    public function show(Client $client): JsonResponse
    {
        $client->load('user:id,full_name,phone,email');

        return response()->json(['client' => $client]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // User account
            'full_name' => 'required|string|max:150',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|max:15|unique:users,phone',
            'password' => 'required|string|min:8',
            // Business details
            'business_name' => 'required|string|max:255',
            'proprietor_name' => 'nullable|string|max:150',
            'business_type' => 'nullable|string|max:50',
            'drug_license_no' => 'required|string|max:50|unique:clients,drug_license_no',
            'dl_expiry_date' => 'nullable|string|max:10',
            'gst_number' => 'nullable|string|max:15',
            'pan_number' => 'nullable|string|max:10',
            'fssai_number' => 'nullable|string|max:20',
            // Address
            'state_code' => 'required|string|size:2',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|size:6',
            // Contact
            'alt_phone' => 'nullable|string|max:15',
            'contact_person' => 'nullable|string|max:150',
            'contact_designation' => 'nullable|string|max:100',
            // Delivery
            'delivery_address' => 'nullable|string|max:500',
            'delivery_city' => 'nullable|string|max:100',
            'delivery_pincode' => 'nullable|string|max:6',
            'delivery_instructions' => 'nullable|string|max:500',
            'preferred_delivery_time' => 'nullable|string|max:50',
            // Banking
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:30',
            'bank_ifsc' => 'nullable|string|max:11',
            'bank_branch' => 'nullable|string|max:100',
            // Credit
            'credit_limit' => 'numeric|min:0',
            'credit_period_days' => 'integer|min:0',
            // Notes
            'notes' => 'nullable|string|max:5000',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = User::create([
                'role_id' => 3,
                'full_name' => $validated['full_name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'password' => $validated['password'], // User model's 'hashed' cast handles hashing
            ]);

            $clientData = collect($validated)->except(['full_name', 'email', 'phone', 'password'])->toArray();
            $clientData['user_id'] = $user->id;
            $clientData['credit_limit'] = $clientData['credit_limit'] ?? 0;
            $clientData['credit_period_days'] = $clientData['credit_period_days'] ?? 30;

            $client = Client::create($clientData);

            ActivityLog::log('client.created', 'client', $client->id);

            return response()->json(['client' => $client->load('user')], 201);
        });
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => 'string|max:255',
            'proprietor_name' => 'nullable|string|max:150',
            'business_type' => 'nullable|string|max:50',
            'drug_license_no' => 'string|max:50|unique:clients,drug_license_no,' . $client->id,
            'dl_expiry_date' => 'nullable|string|max:10',
            'gst_number' => 'nullable|string|max:15',
            'pan_number' => 'nullable|string|max:10',
            'fssai_number' => 'nullable|string|max:20',
            'state_code' => 'string|size:2',
            'address_line1' => 'string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'string|max:100',
            'district' => 'nullable|string|max:100',
            'state' => 'string|max:100',
            'pincode' => 'string|size:6',
            'alt_phone' => 'nullable|string|max:15',
            'contact_person' => 'nullable|string|max:150',
            'contact_designation' => 'nullable|string|max:100',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_city' => 'nullable|string|max:100',
            'delivery_pincode' => 'nullable|string|max:6',
            'delivery_instructions' => 'nullable|string|max:500',
            'preferred_delivery_time' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:30',
            'bank_ifsc' => 'nullable|string|max:11',
            'bank_branch' => 'nullable|string|max:100',
            'credit_limit' => 'numeric|min:0',
            'credit_period_days' => 'integer|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:5000',
        ]);

        $oldValues = $client->only(array_keys($validated));
        $client->update($validated);

        ActivityLog::log('client.updated', 'client', $client->id, $oldValues, $validated);

        return response()->json(['client' => $client->fresh()->load('user')]);
    }

    public function verifyKyc(Request $request, Client $client): JsonResponse
    {
        $client->update([
            'kyc_verified' => true,
            'kyc_verified_at' => now(),
            'kyc_verified_by' => $request->user()->id,
        ]);

        ActivityLog::log('client.kyc_verified', 'client', $client->id);

        return response()->json(['message' => 'KYC verified', 'client' => $client]);
    }

    public function ledger(Request $request, Client $client): JsonResponse
    {
        $entries = $client->ledgerEntries()
            ->with('createdBy:id,full_name')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate($request->get('per_page', 50));

        return response()->json($entries);
    }

    public function statement(Client $client): JsonResponse
    {
        $entries = $client->ledgerEntries()
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        return response()->json([
            'client' => $client->only('id', 'business_name', 'current_outstanding', 'credit_limit'),
            'entries' => $entries,
        ]);
    }
}
