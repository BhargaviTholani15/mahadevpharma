<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CompanySetting;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SalesReturn;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminWebController extends Controller
{
    // ─── Users ────────────────────────────────────────────────────
    public function users(Request $request)
    {
        $query = User::with('role');
        if ($search = $request->get('search')) {
            $query->where(fn($q) => $q->where('full_name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
        }
        $users = $query->latest()->paginate(25)->withQueryString();
        $roles = Role::all();
        return view('admin.users', compact('users', 'roles'));
    }

    public function storeUser(Request $request)
    {
        $v = $request->validate([
            'full_name' => 'required|string|max:100', 'email' => 'nullable|email|max:100',
            'phone' => 'required|string|max:15|unique:users,phone', 'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);
        User::create($v);
        return back()->with('success', 'User created.');
    }

    public function updateUser(Request $request, User $user)
    {
        $v = $request->validate([
            'full_name' => 'required|string|max:100', 'email' => 'nullable|email|max:100',
            'phone' => 'required|string|max:15|unique:users,phone,' . $user->id,
            'role_id' => 'required|exists:roles,id',
        ]);
        if ($request->filled('password')) $v['password'] = $request->password;
        $user->update($v);
        return back()->with('success', 'User updated.');
    }

    public function toggleUserActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'User status toggled.');
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return back()->with('success', 'User deleted.');
    }

    // ─── Roles & Permissions ──────────────────────────────────────
    public function roles()
    {
        $roles = Role::withCount('users')->with('permissions')->get();
        $permissions = Permission::orderBy('name')->get();
        return view('admin.roles', compact('roles', 'permissions'));
    }

    public function storeRole(Request $request)
    {
        $v = $request->validate(['name' => 'required|string|max:50', 'slug' => 'required|string|max:50|unique:roles,slug']);
        Role::create($v);
        return back()->with('success', 'Role created.');
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $ids = $request->input('permission_ids', []);
        $role->permissions()->sync($ids);
        return back()->with('success', 'Permissions updated.');
    }

    // ─── Notifications ────────────────────────────────────────────
    public function notifications(Request $request)
    {
        $query = Notification::where('user_id', auth()->id());
        $notifications = $query->latest()->paginate(25);
        return view('admin.notifications', compact('notifications'));
    }

    public function markNotificationRead(Notification $notification)
    {
        $notification->update(['read_at' => now()]);
        return back();
    }

    public function markAllNotificationsRead()
    {
        Notification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('success', 'All marked as read.');
    }

    // ─── Audit Logs ───────────────────────────────────────────────
    public function auditLogs(Request $request)
    {
        $query = ActivityLog::with('user:id,full_name');
        if ($search = $request->get('search')) {
            $query->where('action', 'like', "%{$search}%");
        }
        $logs = $query->latest()->paginate(25)->withQueryString();
        return view('admin.audit-logs', compact('logs'));
    }

    // ─── Settings ─────────────────────────────────────────────────
    public function settings()
    {
        $settings = CompanySetting::first();
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $v = $request->validate([
            'company_name' => 'required|string|max:255', 'gst_number' => 'nullable|string|max:15',
            'drug_license_no' => 'nullable|string|max:100', 'state_code' => 'nullable|string|max:5',
            'address_line1' => 'nullable|string|max:255', 'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100', 'pincode' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:15', 'email' => 'nullable|email|max:100',
            'invoice_prefix' => 'nullable|string|max:10', 'financial_year' => 'nullable|string|max:10',
        ]);
        CompanySetting::first()->update($v);
        return back()->with('success', 'Settings updated.');
    }

    // ─── Returns ──────────────────────────────────────────────────
    public function returns(Request $request)
    {
        $query = SalesReturn::with(['client:id,business_name', 'order:id,order_number']);
        if (auth()->user()->isClient()) {
            $query->where('client_id', auth()->user()->client?->id);
        }
        if ($search = $request->get('search')) {
            $query->where('return_number', 'like', "%{$search}%");
        }
        $returns = $query->latest()->paginate(25)->withQueryString();
        return view('returns.index', compact('returns'));
    }

    // ─── Reports ──────────────────────────────────────────────────
    public function reports()
    {
        return view('reports.index');
    }

    public function gstReports()
    {
        return view('reports.gst');
    }

    public function bulkOperations()
    {
        return view('reports.bulk');
    }
}
