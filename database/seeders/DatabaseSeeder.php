<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeds sample data matching the products described in the feasibility
     * study (diapers, powdered milk, baby lotion) so the system can be
     * demonstrated immediately after installation, plus one Owner and one
     * Staff account for testing role-based access control.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Store Owner',
            'username' => 'owner',
            'email' => 'owner@aizenbabystore.test',
            'role' => 'owner',
            'status' => 'active',
            'password' => Hash::make('owner12345'),
        ]);

        User::create([
            'name' => 'Store Staff',
            'username' => 'staff',
            'email' => 'staff@aizenbabystore.test',
            'role' => 'staff',
            'status' => 'active',
            'password' => Hash::make('staff12345'),
        ]);

        Supplier::create([
            'name' => 'Manila Baby Essentials Supplier',
            'contact_person' => 'Juan Dela Cruz',
            'phone' => '0917-000-0000',
            'facebook_handle' => 'fb.com/manilababysupplier',
            'address' => 'Manila, Philippines',
        ]);

        $products = [
            ['sku' => 'DIAP-M-01', 'name' => 'Baby Diapers (Medium)', 'category' => 'Diapers', 'unit' => 'pack', 'selling_price' => 350, 'cost_price' => 280, 'quantity_on_hand' => 20, 'reorder_point' => 5],
            ['sku' => 'DIAP-L-01', 'name' => 'Baby Diapers (Large)', 'category' => 'Diapers', 'unit' => 'pack', 'selling_price' => 380, 'cost_price' => 300, 'quantity_on_hand' => 15, 'reorder_point' => 5],
            ['sku' => 'MILK-01', 'name' => 'Powdered Milk 900g', 'category' => 'Milk', 'unit' => 'can', 'selling_price' => 520, 'cost_price' => 430, 'quantity_on_hand' => 10, 'reorder_point' => 4],
            ['sku' => 'LOTION-01', 'name' => 'Baby Lotion 200ml', 'category' => 'Lotion', 'unit' => 'bottle', 'selling_price' => 180, 'cost_price' => 130, 'quantity_on_hand' => 25, 'reorder_point' => 6],
            ['sku' => 'WIPES-01', 'name' => 'Baby Wet Wipes (80ct)', 'category' => 'Hygiene', 'unit' => 'pack', 'selling_price' => 120, 'cost_price' => 85, 'quantity_on_hand' => 3, 'reorder_point' => 8],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
