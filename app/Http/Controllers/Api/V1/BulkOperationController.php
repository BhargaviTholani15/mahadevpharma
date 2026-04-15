<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BulkOperationController extends Controller
{
    public function exportProducts(Request $request): JsonResponse
    {
        $query = Product::with(['brand:id,name', 'category:id,name', 'hsnCode:id,code,description']);

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $products = $query->orderBy('name')->get();

        return response()->json(['data' => $products, 'count' => $products->count()]);
    }

    public function exportClients(Request $request): JsonResponse
    {
        $query = Client::with('user:id,full_name,phone,email');

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $clients = $query->orderBy('business_name')->get()->makeVisible([])->makeHidden([
            'bank_account_no', 'bank_ifsc', 'bank_name', 'bank_branch',
            'pan_number',
        ]);

        return response()->json(['data' => $clients, 'count' => $clients->count()]);
    }

    public function exportOrders(Request $request): JsonResponse
    {
        $query = Order::with([
            'client:id,business_name',
            'warehouse:id,name,code',
            'items.product:id,name,sku',
            'items.batch:id,batch_number',
        ]);

        if ($from = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $orders = $query->latest()->get();

        return response()->json(['data' => $orders, 'count' => $orders->count()]);
    }

    public function importProducts(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|array|min:1',
        ]);

        $created = 0;
        $failed = 0;
        $errors = [];

        return DB::transaction(function () use ($request, &$created, &$failed, &$errors) {
            foreach ($request->data as $index => $productData) {
                $validator = Validator::make($productData, [
                    'name' => 'required|string|max:255',
                    'generic_name' => 'nullable|string|max:255',
                    'brand_id' => 'nullable|exists:brands,id',
                    'category_id' => 'nullable|exists:categories,id',
                    'hsn_code_id' => 'nullable|exists:hsn_codes,id',
                    'dosage_form' => 'nullable|string|max:50',
                    'strength' => 'nullable|string|max:50',
                    'pack_size' => 'nullable|string|max:50',
                    'sku' => 'required|string|max:50|unique:products,sku',
                    'is_active' => 'boolean',
                ]);

                if ($validator->fails()) {
                    $failed++;
                    $errors[] = ['index' => $index, 'errors' => $validator->errors()->toArray()];
                    continue;
                }

                Product::create($validator->validated());
                $created++;
            }

            ActivityLog::log('bulk.products_imported', 'product', 0, null, [
                'created' => $created,
                'failed' => $failed,
            ]);

            return response()->json([
                'message' => 'Product import completed',
                'created' => $created,
                'failed' => $failed,
                'errors' => $errors,
            ], $failed > 0 && $created === 0 ? 422 : 200);
        });
    }

    public function importClients(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|array|min:1',
        ]);

        $created = 0;
        $failed = 0;
        $errors = [];

        return DB::transaction(function () use ($request, &$created, &$failed, &$errors) {
            foreach ($request->data as $index => $clientData) {
                $validator = Validator::make($clientData, [
                    'full_name' => 'required|string|max:150',
                    'email' => 'nullable|email|unique:users,email',
                    'phone' => 'required|string|max:15|unique:users,phone',
                    'password' => 'required|string|min:6',
                    'business_name' => 'required|string|max:255',
                    'proprietor_name' => 'nullable|string|max:150',
                    'drug_license_no' => 'required|string|max:50|unique:clients,drug_license_no',
                    'gst_number' => 'nullable|string|max:15',
                    'pan_number' => 'nullable|string|max:10',
                    'state_code' => 'required|string|size:2',
                    'address_line1' => 'required|string|max:255',
                    'address_line2' => 'nullable|string|max:255',
                    'city' => 'required|string|max:100',
                    'district' => 'nullable|string|max:100',
                    'state' => 'required|string|max:100',
                    'pincode' => 'required|string|size:6',
                    'credit_limit' => 'nullable|numeric|min:0',
                    'credit_period_days' => 'nullable|integer|min:0',
                ]);

                if ($validator->fails()) {
                    $failed++;
                    $errors[] = ['index' => $index, 'errors' => $validator->errors()->toArray()];
                    continue;
                }

                $data = $validator->validated();

                $user = User::create([
                    'role_id' => 3,
                    'full_name' => $data['full_name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'],
                    'password' => $data['password'],
                ]);

                Client::create([
                    'user_id' => $user->id,
                    'business_name' => $data['business_name'],
                    'proprietor_name' => $data['proprietor_name'] ?? null,
                    'drug_license_no' => $data['drug_license_no'],
                    'gst_number' => $data['gst_number'] ?? null,
                    'pan_number' => $data['pan_number'] ?? null,
                    'state_code' => $data['state_code'],
                    'address_line1' => $data['address_line1'],
                    'address_line2' => $data['address_line2'] ?? null,
                    'city' => $data['city'],
                    'district' => $data['district'] ?? null,
                    'state' => $data['state'],
                    'pincode' => $data['pincode'],
                    'credit_limit' => $data['credit_limit'] ?? 0,
                    'credit_period_days' => $data['credit_period_days'] ?? 30,
                ]);

                $created++;
            }

            ActivityLog::log('bulk.clients_imported', 'client', 0, null, [
                'created' => $created,
                'failed' => $failed,
            ]);

            return response()->json([
                'message' => 'Client import completed',
                'created' => $created,
                'failed' => $failed,
                'errors' => $errors,
            ], $failed > 0 && $created === 0 ? 422 : 200);
        });
    }
}
