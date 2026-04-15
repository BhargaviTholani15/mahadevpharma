<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CompanySetting;
use App\Models\DosageForm;
use App\Models\DrugSchedule;
use App\Models\HsnCode;
use App\Models\PackSize;
use App\Models\Strength;
use App\Models\StorageCondition;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Company Settings ───────────────────────────────────────────
        CompanySetting::create([
            'company_name' => 'Mahadev Pharma',
            'gst_number' => '36ABBPT6277A1ZN',
            'drug_license_no' => '345/HD/AP/2002, 346/HD/AP/2002',
            'state_code' => '36',
            'address_line1' => '2-3-166 & 17, 1st Floor, Taj Plaza, Nallagopalpet Main Road',
            'city' => 'Secunderabad',
            'state' => 'Telangana',
            'pincode' => '500003',
            'phone' => '8919383362',
            'email' => 'info@mahadevpharma.in',
            'invoice_prefix' => 'INV',
            'current_invoice_seq' => 0,
            'financial_year' => '2025-26',
        ]);

        // ─── Admin User ─────────────────────────────────────────────────
        User::create([
            'role_id' => 1,
            'full_name' => 'Admin',
            'email' => 'admin@mahadevpharma.in',
            'phone' => '8919383362',
            'password' => 'Admin@123',
            'is_active' => true,
        ]);

        // ─── Demo Vendor User ───────────────────────────────────────────
        User::create([
            'role_id' => 3,
            'full_name' => 'Demo Pharmacy',
            'email' => 'vendor@demo.com',
            'phone' => '9999999999',
            'password' => 'Vendor@123',
            'is_active' => true,
        ]);

        // ─── Default Warehouse ──────────────────────────────────────────
        Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'WH-SEC-01',
            'state_code' => '36',
            'address_line1' => '2-3-166 & 17, 1st Floor, Taj Plaza',
            'city' => 'Secunderabad',
            'state' => 'Telangana',
            'pincode' => '500003',
        ]);

        // ─── Categories ─────────────────────────────────────────────────
        $categories = [
            'Tablets', 'Capsules', 'Syrups & Suspensions', 'Injections & Vials',
            'Ointments & Creams', 'Drops & Solutions', 'Powders & Sachets',
            'Inhalers & Nebulizers', 'Surgical & Dressing', 'Vitamins & Supplements',
        ];
        foreach ($categories as $i => $name) {
            Category::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'sort_order' => $i]);
        }

        // ─── Brands ─────────────────────────────────────────────────────
        $brands = [
            ['Cipla', 'Cipla Ltd.'],
            ['Sun Pharma', 'Sun Pharmaceutical Industries Ltd.'],
            ['Dr. Reddy\'s', 'Dr. Reddy\'s Laboratories Ltd.'],
            ['Lupin', 'Lupin Ltd.'],
            ['Mankind', 'Mankind Pharma Ltd.'],
            ['Zydus', 'Zydus Lifesciences Ltd.'],
            ['Torrent', 'Torrent Pharmaceuticals Ltd.'],
            ['Alkem', 'Alkem Laboratories Ltd.'],
            ['Abbott', 'Abbott India Ltd.'],
            ['GSK', 'GlaxoSmithKline Pharmaceuticals Ltd.'],
            ['Biocon', 'Biocon Ltd.'],
            ['Glenmark', 'Glenmark Pharmaceuticals Ltd.'],
        ];
        foreach ($brands as $b) {
            Brand::create(['name' => $b[0], 'slug' => \Illuminate\Support\Str::slug($b[0]), 'manufacturer' => $b[1]]);
        }

        // ─── HSN Codes ──────────────────────────────────────────────────
        $hsnCodes = [
            ['3004', 'Medicaments for therapeutic/prophylactic use, in measured doses', 6, 6, 12],
            ['3003', 'Medicaments not put up in measured doses or packing for retail', 6, 6, 12],
            ['3005', 'Wadding, gauze, bandages, dressings with pharmaceutical substances', 6, 6, 12],
            ['3006', 'Pharmaceutical goods (surgical, dental, ophthalmic)', 6, 6, 12],
            ['2106', 'Food preparations / Nutritional supplements', 9, 9, 18],
            ['9018', 'Medical/surgical instruments and apparatus', 6, 6, 12],
        ];
        foreach ($hsnCodes as $h) {
            HsnCode::create([
                'code' => $h[0], 'description' => $h[1],
                'cgst_rate' => $h[2], 'sgst_rate' => $h[3], 'igst_rate' => $h[4],
                'effective_from' => '2024-01-01',
            ]);
        }

        // ─── Dosage Forms ───────────────────────────────────────────────
        $dosageForms = [
            'Tablet', 'Capsule', 'Syrup', 'Suspension', 'Injection',
            'Cream', 'Ointment', 'Gel', 'Drops', 'Powder',
            'Inhaler', 'Nebulizer Solution', 'Suppository', 'Patch', 'Spray',
            'Lotion', 'Solution', 'Sachet',
        ];
        foreach ($dosageForms as $i => $name) {
            DosageForm::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'sort_order' => $i]);
        }

        // ─── Strengths ──────────────────────────────────────────────────
        $strengths = [
            '5mg', '10mg', '20mg', '25mg', '40mg', '50mg', '100mg', '150mg',
            '200mg', '250mg', '300mg', '400mg', '500mg', '625mg', '750mg', '1000mg',
            '1g', '2g', '5ml', '10ml', '15ml', '30ml', '50ml', '60ml', '100ml',
            '150ml', '200ml', '5mg/5ml', '10mg/5ml', '125mg/5ml', '250mg/5ml',
            '1%', '2%', '5%', '0.1%', '0.5%', '10%',
            '5mg/ml', '10mg/ml', '40mg/ml',
        ];
        foreach ($strengths as $i => $name) {
            Strength::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'sort_order' => $i]);
        }

        // ─── Pack Sizes ─────────────────────────────────────────────────
        $packSizes = [
            '1 unit', '3 units', '5 units', '10 units',
            '5 tablets/strip', '10 tablets/strip', '15 tablets/strip', '20 tablets/strip', '30 tablets/strip',
            '10 capsules/strip', '15 capsules/strip',
            '30ml bottle', '60ml bottle', '100ml bottle', '150ml bottle', '200ml bottle',
            '1 vial', '5 vials', '10 vials',
            '1 ampoule', '5 ampoules', '10 ampoules',
            '5g tube', '10g tube', '15g tube', '20g tube', '30g tube', '50g tube',
            '1 inhaler', '1 sachet', '10 sachets', '30 sachets',
        ];
        foreach ($packSizes as $i => $name) {
            PackSize::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'sort_order' => $i]);
        }

        // ─── Drug Schedules ─────────────────────────────────────────────
        $drugSchedules = [
            ['OTC', 'Over the counter — no prescription needed'],
            ['Schedule G', 'Can be sold only under supervision of a pharmacist'],
            ['Schedule H', 'Can be sold only on prescription of a registered medical practitioner'],
            ['Schedule H1', 'Requires prescription; record to be maintained by pharmacist for 3 years'],
            ['Schedule X', 'Narcotic and psychotropic substances — strict prescription and record-keeping'],
            ['Narcotics', 'Controlled under NDPS Act — special license required'],
        ];
        foreach ($drugSchedules as $i => [$name, $desc]) {
            DrugSchedule::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'description' => $desc,
                'sort_order' => $i,
            ]);
        }

        // ─── Storage Conditions ─────────────────────────────────────────
        $storageConditions = [
            'Room Temperature',
            'Cool & Dry Place',
            'Refrigerated (2-8°C)',
            'Frozen (-20°C)',
            'Controlled Room Temperature',
            'Protect from Light',
            'Protect from Moisture',
            'Do Not Freeze',
        ];
        foreach ($storageConditions as $i => $name) {
            StorageCondition::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'sort_order' => $i]);
        }
    }
}
