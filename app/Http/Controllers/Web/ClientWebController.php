<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;

class ClientWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with('user:id,full_name,phone,is_active');
        if ($search = $request->get('search')) {
            $query->where(fn($q) => $q->where('business_name', 'like', "%{$search}%")
                ->orWhere('gst_number', 'like', "%{$search}%")
                ->orWhere('proprietor_name', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%"));
        }
        if ($status = $request->get('status')) {
            $query->where('kyc_verified', $status === 'verified');
        }
        $clients = $query->latest()->paginate(25)->withQueryString();
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.form', ['client' => null]);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'business_name' => 'required|string|max:255',
            'proprietor_name' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'alt_phone' => 'nullable|string|max:15',
            'gst_number' => 'nullable|string|max:15',
            'drug_license_no' => 'nullable|string|max:100',
            'pan_number' => 'nullable|string|max:10',
            'address_line1' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'state_code' => 'nullable|string|max:5',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_period_days' => 'nullable|integer|min:0',
        ]);

        $client = Client::create($v);
        ActivityLog::log('client.created', 'client', $client->id, null, $v);
        return redirect()->route('clients.index')->with('success', 'Client created.');
    }

    public function edit(Client $client)
    {
        return view('clients.form', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $v = $request->validate([
            'business_name' => 'required|string|max:255',
            'proprietor_name' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'alt_phone' => 'nullable|string|max:15',
            'gst_number' => 'nullable|string|max:15',
            'drug_license_no' => 'nullable|string|max:100',
            'pan_number' => 'nullable|string|max:10',
            'address_line1' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'state_code' => 'nullable|string|max:5',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_period_days' => 'nullable|integer|min:0',
        ]);

        $old = $client->only(array_keys($v));
        $client->update($v);
        ActivityLog::log('client.updated', 'client', $client->id, $old, $v);
        return redirect()->route('clients.index')->with('success', 'Client updated.');
    }

    public function show(Client $client)
    {
        $client->load('user');
        $ledger = LedgerEntry::where('client_id', $client->id)->latest()->paginate(25);
        return view('clients.show', compact('client', 'ledger'));
    }
}
