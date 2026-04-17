<?php

namespace Database\Seeders;

use App\Models\SampleDataTemplate;
use Illuminate\Database\Seeder;

class SampleDataTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'industry' => 'retail',
                'template_name' => 'Retail Starter',
                'description' => 'Sample data for retail businesses including products, customers, and sales.',
                'modules_included' => ['inventory', 'sales', 'customers', 'accounting'],
                'data_config' => ['products' => 50, 'customers' => 30, 'transactions' => 100],
            ],
            [
                'industry' => 'restaurant',
                'template_name' => 'Restaurant Starter',
                'description' => 'Sample data for restaurant businesses including menu items, orders, and staff.',
                'modules_included' => ['pos', 'inventory', 'hrm', 'accounting'],
                'data_config' => ['menu_items' => 40, 'orders' => 80, 'employees' => 10],
            ],
            [
                'industry' => 'hotel',
                'template_name' => 'Hotel Starter',
                'description' => 'Sample data for hotel businesses including rooms, bookings, and guests.',
                'modules_included' => ['reservations', 'inventory', 'hrm', 'accounting'],
                'data_config' => ['rooms' => 20, 'bookings' => 50, 'guests' => 40],
            ],
            [
                'industry' => 'construction',
                'template_name' => 'Construction Starter',
                'description' => 'Sample data for construction businesses including projects, materials, and contractors.',
                'modules_included' => ['projects', 'inventory', 'hrm', 'accounting'],
                'data_config' => ['projects' => 10, 'materials' => 60, 'employees' => 20],
            ],
            [
                'industry' => 'agriculture',
                'template_name' => 'Agriculture Starter',
                'description' => 'Sample data for agriculture businesses including crops, equipment, and harvests.',
                'modules_included' => ['inventory', 'assets', 'hrm', 'accounting'],
                'data_config' => ['products' => 30, 'assets' => 15, 'employees' => 12],
            ],
            [
                'industry' => 'manufacturing',
                'template_name' => 'Manufacturing Starter',
                'description' => 'Sample data for manufacturing businesses including raw materials, production, and distribution.',
                'modules_included' => ['inventory', 'production', 'sales', 'accounting'],
                'data_config' => ['raw_materials' => 40, 'products' => 25, 'orders' => 60],
            ],
            [
                'industry' => 'services',
                'template_name' => 'Services Starter',
                'description' => 'Sample data for service businesses including clients, projects, and invoices.',
                'modules_included' => ['crm', 'projects', 'invoicing', 'accounting'],
                'data_config' => ['clients' => 25, 'projects' => 15, 'invoices' => 40],
            ],
            [
                'industry' => 'healthcare',
                'template_name' => 'Healthcare Demo Data',
                'description' => 'Data demo untuk klinik/rumah sakit: pasien, dokter, appointment, rekam medis, dan inventory obat',
                'modules_included' => ['patients', 'doctors', 'appointments', 'medical_records', 'pharmacy_inventory'],
                'data_config' => ['patients' => 10, 'doctors' => 3, 'appointments' => 10, 'medical_records' => 5, 'medicines' => 15],
            ],
        ];

        foreach ($templates as $template) {
            SampleDataTemplate::firstOrCreate(
                [
                    'industry' => $template['industry'],
                    'template_name' => $template['template_name'],
                ],
                [
                    'description' => $template['description'],
                    'modules_included' => $template['modules_included'],
                    'data_config' => $template['data_config'],
                    'is_active' => true,
                    'usage_count' => 0,
                ]
            );
        }
    }
}
