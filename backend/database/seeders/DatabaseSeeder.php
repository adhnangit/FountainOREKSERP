<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductBranchStock;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = [
            'super_admin', 'branch_manager', 'sales_person', 'accountant',
            'inventory_manager', 'purchase_officer', 'hr_admin', 'viewer',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Create branches
        $colombo = Branch::firstOrCreate(
            ['code' => 'COL'],
            [
                'name'      => 'Colombo Branch',
                'address'   => '123 Main Street, Colombo 03',
                'phone'     => '+94 11 234 5678',
                'email'     => 'colombo@medri.lk',
                'is_active' => true,
            ]
        );

        $kandy = Branch::firstOrCreate(
            ['code' => 'KDY'],
            [
                'name'      => 'Kandy Branch',
                'address'   => '45 Peradeniya Road, Kandy',
                'phone'     => '+94 81 234 5678',
                'email'     => 'kandy@medri.lk',
                'is_active' => true,
            ]
        );

        $galle = Branch::firstOrCreate(
            ['code' => 'GAL'],
            [
                'name'      => 'Galle Branch',
                'address'   => '78 Hospital Road, Galle',
                'phone'     => '+94 91 234 5678',
                'email'     => 'galle@medri.lk',
                'is_active' => true,
            ]
        );

        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@medri.lk'],
            [
                'name'              => 'System Administrator',
                'password'          => Hash::make('password'),
                'default_branch_id' => null,
                'is_active'         => true,
                'is_super_admin'    => true,
            ]
        );
        $superAdmin->is_super_admin = true;
        $superAdmin->save();
        $superAdmin->syncRoles(['super_admin']);
        $superAdmin->branches()->syncWithoutDetaching([$colombo->id, $kandy->id, $galle->id]);

        // Branch Manager - Colombo
        $bm1 = User::firstOrCreate(
            ['email' => 'manager.col@medri.lk'],
            [
                'name'              => 'Nimal Perera',
                'password'          => Hash::make('password'),
                'default_branch_id' => $colombo->id,
                'is_active'         => true,
            ]
        );
        $bm1->syncRoles(['branch_manager']);
        $bm1->branches()->syncWithoutDetaching([$colombo->id]);

        // Branch Manager - Kandy
        $bm2 = User::firstOrCreate(
            ['email' => 'manager.kdy@medri.lk'],
            [
                'name'              => 'Kumari Silva',
                'password'          => Hash::make('password'),
                'default_branch_id' => $kandy->id,
                'is_active'         => true,
            ]
        );
        $bm2->syncRoles(['branch_manager']);
        $bm2->branches()->syncWithoutDetaching([$kandy->id]);

        // Sales Person - Colombo
        $sales1 = User::firstOrCreate(
            ['email' => 'sales1@medri.lk'],
            [
                'name'              => 'Ashan Fernando',
                'password'          => Hash::make('password'),
                'default_branch_id' => $colombo->id,
                'is_active'         => true,
            ]
        );
        $sales1->syncRoles(['sales_person']);
        $sales1->branches()->syncWithoutDetaching([$colombo->id]);

        // Accountant
        $acct = User::firstOrCreate(
            ['email' => 'accounts@medri.lk'],
            [
                'name'              => 'Priya Jayawardena',
                'password'          => Hash::make('password'),
                'default_branch_id' => $colombo->id,
                'is_active'         => true,
            ]
        );
        $acct->syncRoles(['accountant']);
        $acct->branches()->syncWithoutDetaching([$colombo->id, $kandy->id]);

        // Inventory Manager
        $inv = User::firstOrCreate(
            ['email' => 'inventory@medri.lk'],
            [
                'name'              => 'Sunil Bandara',
                'password'          => Hash::make('password'),
                'default_branch_id' => $colombo->id,
                'is_active'         => true,
            ]
        );
        $inv->syncRoles(['inventory_manager']);
        $inv->branches()->syncWithoutDetaching([$colombo->id]);

        // Purchase Officer
        $pur = User::firstOrCreate(
            ['email' => 'purchase@medri.lk'],
            [
                'name'              => 'Dilshan Rathnayake',
                'password'          => Hash::make('password'),
                'default_branch_id' => $colombo->id,
                'is_active'         => true,
            ]
        );
        $pur->syncRoles(['purchase_officer']);
        $pur->branches()->syncWithoutDetaching([$colombo->id]);

        // Viewer
        $viewer = User::firstOrCreate(
            ['email' => 'viewer@medri.lk'],
            [
                'name'              => 'Amali Wickramasinghe',
                'password'          => Hash::make('password'),
                'default_branch_id' => $colombo->id,
                'is_active'         => true,
            ]
        );
        $viewer->syncRoles(['viewer']);
        $viewer->branches()->syncWithoutDetaching([$colombo->id]);

        // Product categories
        $categoriesData = [
            ['name' => 'Diagnostic Equipment', 'code' => 'DIAG', 'description' => 'Diagnostic devices and tools',         'is_active' => true],
            ['name' => 'Surgical Instruments',  'code' => 'SURG', 'description' => 'Surgical and procedural instruments',  'is_active' => true],
            ['name' => 'Patient Monitoring',    'code' => 'PMON', 'description' => 'Monitoring systems and devices',        'is_active' => true],
            ['name' => 'Consumables',           'code' => 'CONS', 'description' => 'Single-use medical supplies',           'is_active' => true],
            ['name' => 'Rehabilitation',        'code' => 'REHA', 'description' => 'Physical therapy and rehab equipment',  'is_active' => true],
            ['name' => 'Dental Equipment',      'code' => 'DENT', 'description' => 'Dental instruments and supplies',       'is_active' => true],
            ['name' => 'Laboratory',            'code' => 'LABR', 'description' => 'Lab equipment and reagents',            'is_active' => true],
            ['name' => 'Orthopedic',            'code' => 'ORTH', 'description' => 'Orthopedic devices and supports',       'is_active' => true],
        ];

        $catMap = [];
        foreach ($categoriesData as $cat) {
            $catMap[$cat['name']] = ProductCategory::firstOrCreate(['code' => $cat['code']], $cat);
        }

        // Products (global, no branch_id; stock seeded per branch separately)
        $productsData = [
            ['name' => 'Digital Blood Pressure Monitor', 'code' => 'MED-001', 'sku' => 'MED-001', 'cat' => 'Diagnostic Equipment',  'cost' => 2500,   'sell' => 3500,   'reorder' => 20, 'unit' => 'unit', 'stock' => 85],
            ['name' => 'Digital Thermometer',            'code' => 'MED-002', 'sku' => 'MED-002', 'cat' => 'Diagnostic Equipment',  'cost' => 350,    'sell' => 550,    'reorder' => 50, 'unit' => 'unit', 'stock' => 210],
            ['name' => 'Pulse Oximeter',                 'code' => 'MED-003', 'sku' => 'MED-003', 'cat' => 'Diagnostic Equipment',  'cost' => 1800,   'sell' => 2800,   'reorder' => 25, 'unit' => 'unit', 'stock' => 64],
            ['name' => 'ECG Machine',                    'code' => 'MED-004', 'sku' => 'MED-004', 'cat' => 'Diagnostic Equipment',  'cost' => 45000,  'sell' => 65000,  'reorder' => 3,  'unit' => 'unit', 'stock' => 8],
            ['name' => 'Stethoscope (Premium)',           'code' => 'MED-005', 'sku' => 'MED-005', 'cat' => 'Diagnostic Equipment',  'cost' => 3500,   'sell' => 5500,   'reorder' => 15, 'unit' => 'unit', 'stock' => 42],
            ['name' => 'Surgical Gloves (Box 100)',      'code' => 'CONS-001','sku' => 'CONS-001', 'cat' => 'Consumables',           'cost' => 450,    'sell' => 650,    'reorder' => 100,'unit' => 'box',  'stock' => 340],
            ['name' => 'Disposable Syringes 5ml (Box)',  'code' => 'CONS-002','sku' => 'CONS-002', 'cat' => 'Consumables',           'cost' => 280,    'sell' => 420,    'reorder' => 200,'unit' => 'box',  'stock' => 520],
            ['name' => 'Surgical Masks (Box 50)',        'code' => 'CONS-003','sku' => 'CONS-003', 'cat' => 'Consumables',           'cost' => 350,    'sell' => 500,    'reorder' => 150,'unit' => 'box',  'stock' => 180],
            ['name' => 'IV Cannula (Box)',               'code' => 'CONS-004','sku' => 'CONS-004', 'cat' => 'Consumables',           'cost' => 800,    'sell' => 1200,   'reorder' => 80, 'unit' => 'box',  'stock' => 95],
            ['name' => 'Patient Monitor (5-param)',      'code' => 'MON-001', 'sku' => 'MON-001',  'cat' => 'Patient Monitoring',    'cost' => 85000,  'sell' => 125000, 'reorder' => 2,  'unit' => 'unit', 'stock' => 5],
            ['name' => 'Wheelchair (Standard)',          'code' => 'REH-001', 'sku' => 'REH-001',  'cat' => 'Rehabilitation',        'cost' => 8500,   'sell' => 13000,  'reorder' => 10, 'unit' => 'unit', 'stock' => 22],
            ['name' => 'Crutches (Pair)',                'code' => 'REH-002', 'sku' => 'REH-002',  'cat' => 'Rehabilitation',        'cost' => 1200,   'sell' => 1800,   'reorder' => 20, 'unit' => 'pair', 'stock' => 35],
            ['name' => 'Nebulizer',                      'code' => 'MED-010', 'sku' => 'MED-010',  'cat' => 'Diagnostic Equipment',  'cost' => 4500,   'sell' => 6500,   'reorder' => 10, 'unit' => 'unit', 'stock' => 18],
            ['name' => 'Glucometer Kit',                 'code' => 'MED-011', 'sku' => 'MED-011',  'cat' => 'Diagnostic Equipment',  'cost' => 1800,   'sell' => 2800,   'reorder' => 30, 'unit' => 'unit', 'stock' => 7],
            ['name' => 'Knee Brace (Medium)',            'code' => 'ORT-001', 'sku' => 'ORT-001',  'cat' => 'Orthopedic',            'cost' => 1500,   'sell' => 2400,   'reorder' => 20, 'unit' => 'unit', 'stock' => 14],
        ];

        foreach ($productsData as $prod) {
            $cat = $catMap[$prod['cat']] ?? null;
            if (!Product::where('sku', $prod['sku'])->exists()) {
                $product = Product::create([
                    'category_id'   => $cat?->id,
                    'name'          => $prod['name'],
                    'code'          => $prod['code'],
                    'sku'           => $prod['sku'],
                    'cost_price'    => $prod['cost'],
                    'selling_price' => $prod['sell'],
                    'reorder_level' => $prod['reorder'],
                    'unit'          => $prod['unit'],
                    'is_active'     => true,
                ]);

                // Seed stock for Colombo branch
                ProductBranchStock::firstOrCreate(
                    ['product_id' => $product->id, 'branch_id' => $colombo->id],
                    ['quantity' => $prod['stock'], 'avg_cost' => $prod['cost']]
                );

                // Seed a smaller stock for Kandy
                ProductBranchStock::firstOrCreate(
                    ['product_id' => $product->id, 'branch_id' => $kandy->id],
                    ['quantity' => (int) round($prod['stock'] * 0.4), 'avg_cost' => $prod['cost']]
                );
            }
        }

        // Suppliers (no branch_id in schema)
        $suppliersData = [
            ['code' => 'SUP-001', 'name' => 'MedTech Distributors Pvt Ltd',  'contact_person' => 'Mr. Kamal Wijesinghe', 'phone' => '+94 11 456 7890', 'email' => 'sales@medtech.lk',      'payment_terms_days' => 30],
            ['code' => 'SUP-002', 'name' => 'Global Medical Supplies',        'contact_person' => 'Ms. Fathima Hassan',    'phone' => '+94 11 567 8901', 'email' => 'gms@globalmed.lk',      'payment_terms_days' => 45],
            ['code' => 'SUP-003', 'name' => 'Asia Pacific Healthcare',        'contact_person' => 'Mr. Chen Wei',           'phone' => '+94 11 678 9012', 'email' => 'ap@aphealthcare.com',   'payment_terms_days' => 30],
            ['code' => 'SUP-004', 'name' => 'Lanka Pharma Imports',           'contact_person' => 'Dr. Roshan Gunaratna',  'phone' => '+94 11 789 0123', 'email' => 'imports@lankapharma.lk','payment_terms_days' => 60],
        ];

        foreach ($suppliersData as $sup) {
            Supplier::firstOrCreate(
                ['email' => $sup['email']],
                array_merge($sup, ['is_active' => true])
            );
        }

        // Customers
        $customersData = [
            ['code' => 'CUST-001', 'name' => 'City General Hospital',      'phone' => '+94 11 234 5678', 'email' => 'procurement@cityhospital.lk',  'address' => 'No. 12 Hospital Road, Colombo 08',  'credit_limit' => 500000,  'payment_terms_days' => 30],
            ['code' => 'CUST-002', 'name' => 'Nawaloka Medical Centre',    'phone' => '+94 11 345 6789', 'email' => 'stores@nawaloka.lk',           'address' => '23 Deshamanya Mawatha, Colombo 02', 'credit_limit' => 750000,  'payment_terms_days' => 45],
            ['code' => 'CUST-003', 'name' => 'Asiri Hospital Holdings',    'phone' => '+94 11 456 7890', 'email' => 'medical@asiri.lk',            'address' => '181 Kirula Road, Colombo 05',        'credit_limit' => 1000000, 'payment_terms_days' => 30],
            ['code' => 'CUST-004', 'name' => 'Kandy Teaching Hospital',    'phone' => '+94 81 222 2261', 'email' => 'supply@kandyhosp.lk',         'address' => 'Kandy Teaching Hospital, Kandy',     'credit_limit' => 300000,  'payment_terms_days' => 60],
            ['code' => 'CUST-005', 'name' => 'Dr. Samantha Clinic',        'phone' => '+94 77 123 4567', 'email' => 'samantha@drclinic.lk',        'address' => '45 Galle Road, Colombo 03',          'credit_limit' => 100000,  'payment_terms_days' => 30],
            ['code' => 'CUST-006', 'name' => 'Lanka Eye Hospital',         'phone' => '+94 11 567 8901', 'email' => 'admin@lankaeyehospital.lk',   'address' => '78 Colombo Street, Colombo 11',      'credit_limit' => 250000,  'payment_terms_days' => 45],
            ['code' => 'CUST-007', 'name' => 'Galle Base Hospital',        'phone' => '+94 91 222 2261', 'email' => 'supply@gallebase.lk',         'address' => 'Galle Base Hospital, Galle',         'credit_limit' => 200000,  'payment_terms_days' => 60],
            ['code' => 'CUST-008', 'name' => 'National Hospital Colombo',  'phone' => '+94 11 269 1111', 'email' => 'medical@nhsrilanka.lk',       'address' => 'Regent Street, Colombo 10',          'credit_limit' => 2000000, 'payment_terms_days' => 30],
        ];

        foreach ($customersData as $cust) {
            Customer::firstOrCreate(
                ['email' => $cust['email']],
                array_merge($cust, ['branch_id' => $colombo->id, 'is_active' => true])
            );
        }

        $this->call(ChartOfAccountsSeeder::class);

        $this->command->info('');
        $this->command->info('✅  MedRi ERP seeded successfully!');
        $this->command->info('');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin',       'admin@medri.lk',         'password'],
                ['Branch Manager',    'manager.col@medri.lk',   'password'],
                ['Branch Manager',    'manager.kdy@medri.lk',   'password'],
                ['Sales Person',      'sales1@medri.lk',        'password'],
                ['Accountant',        'accounts@medri.lk',      'password'],
                ['Inventory Manager', 'inventory@medri.lk',     'password'],
                ['Purchase Officer',  'purchase@medri.lk',      'password'],
                ['Viewer',            'viewer@medri.lk',        'password'],
            ]
        );
    }
}
