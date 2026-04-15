<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showAdminLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isClient()) {
                Auth::logout();
                return redirect()->route('admin.login')->withErrors(['phone' => 'This portal is for admin and staff only.']);
            }
            return redirect()->route('dashboard');
        }
        return view('auth.admin-login');
    }

    public function showVendorLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (!$user->isClient()) {
                Auth::logout();
                return redirect()->route('vendor.login')->withErrors(['phone' => 'This portal is for pharmacy vendors only.']);
            }
            return redirect()->route('vendor.dashboard');
        }
        return view('auth.vendor-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string|min:6',
            'portal' => 'required|in:admin,vendor',
        ]);

        if (!Auth::attempt(['phone' => $request->phone, 'password' => $request->password, 'is_active' => true])) {
            return back()->withErrors(['phone' => 'Invalid credentials or account deactivated.'])->withInput();
        }

        $user = Auth::user();

        // Portal-role enforcement
        if ($request->portal === 'admin' && $user->isClient()) {
            Auth::logout();
            return back()->withErrors(['phone' => 'This portal is for admin and staff only. Please use the Vendor Portal.'])->withInput();
        }
        if ($request->portal === 'vendor' && !$user->isClient()) {
            Auth::logout();
            return back()->withErrors(['phone' => 'This portal is for pharmacy vendors only. Please use the Admin Portal.'])->withInput();
        }

        $request->session()->regenerate();
        session(['portal' => $request->portal]);
        $user->update(['last_login_at' => now()]);

        return redirect()->intended($request->portal === 'vendor' ? route('vendor.dashboard') : route('dashboard'));
    }

    public function showSignup()
    {
        if (Auth::check()) {
            return redirect()->route('vendor.dashboard');
        }
        return view('auth.signup');
    }

    public function signup(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => 'required|string|max:15|unique:users,phone',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'business_name' => 'required|string|max:255',
            'drug_license_no' => 'required|string|max:50|unique:clients,drug_license_no',
            'gst_number' => 'nullable|string|max:15',
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:6',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'role_id' => 3, // Client role
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password,
                'is_active' => true,
            ]);

            Client::create([
                'user_id' => $user->id,
                'business_name' => $request->business_name,
                'proprietor_name' => $request->full_name,
                'drug_license_no' => $request->drug_license_no,
                'gst_number' => $request->gst_number,
                'state_code' => '36',
                'address_line1' => $request->address_line1,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'credit_limit' => 0,
                'current_outstanding' => 0,
                'kyc_verified' => false,
                'is_active' => true,
            ]);

            Auth::login($user);
            session(['portal' => 'vendor']);
        });

        return redirect()->route('vendor.dashboard');
    }

    public function logout(Request $request)
    {
        $portal = session('portal', 'admin');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($portal === 'vendor' ? route('vendor.login') : route('admin.login'));
    }
}
