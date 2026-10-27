<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        User::firstOrCreate(['username' => 'owner'], [
            'name' => 'Store Owner',
            'username' => 'owner',
            'email' => 'owner@aizenbabystore.test',
            'role' => 'owner',
            'status' => 'active',
            'password' => Hash::make('owner12345'),
        ]);

        User::firstOrCreate(['username' => 'staff'], [
            'name' => 'Store Staff',
            'username' => 'staff',
            'email' => 'staff@aizenbabystore.test',
            'role' => 'staff',
            'status' => 'active',
            'password' => Hash::make('staff12345'),
        ]);

        $supplier = Supplier::firstOrCreate(['name' => 'Manila Baby Essentials Supplier'], [
            'name' => 'Manila Baby Essentials Supplier',
            'contact_person' => 'Juan Dela Cruz',
            'phone' => '0917-000-0000',
            'facebook_handle' => 'fb.com/manilababysupplier',
            'address' => 'Manila, Philippines',
        ]);

        $products = [
            ['sku' => 'DIAP-M-01', 'name' => 'Baby Diapers (Medium)', 'brand' => 'Pampers', 'size_content' => 'M', 'category' => 'Diapers', 'unit' => 'pack', 'selling_price' => 350, 'cost_price' => 280, 'quantity_on_hand' => 20, 'reorder_point' => 5],
            ['sku' => 'DIAP-L-01', 'name' => 'Baby Diapers (Large)', 'brand' => 'Huggies', 'size_content' => 'L', 'category' => 'Diapers', 'unit' => 'pack', 'selling_price' => 380, 'cost_price' => 300, 'quantity_on_hand' => 15, 'reorder_point' => 5],
            ['sku' => 'MILK-01', 'name' => 'Powdered Milk 900g', 'brand' => 'Nestle', 'size_content' => 'g', 'size_content_amount' => 900, 'category' => 'Milk', 'unit' => 'can', 'selling_price' => 520, 'cost_price' => 430, 'quantity_on_hand' => 10, 'reorder_point' => 4, 'expiration_date' => now()->addMonths(8)->format('Y-m-d')],
            ['sku' => 'LOTION-01', 'name' => 'Baby Lotion 200ml', 'brand' => 'Johnson\'s', 'size_content' => 'ml', 'size_content_amount' => 200, 'category' => 'Lotion', 'unit' => 'bottle', 'selling_price' => 180, 'cost_price' => 130, 'quantity_on_hand' => 25, 'reorder_point' => 6, 'expiration_date' => now()->addMonths(18)->format('Y-m-d')],
            ['sku' => 'WIPES-01', 'name' => 'Baby Wet Wipes (80ct)', 'brand' => 'Pampers', 'size_content' => 'sheets', 'size_content_amount' => 80, 'category' => 'Wipes', 'unit' => 'pack', 'selling_price' => 120, 'cost_price' => 85, 'quantity_on_hand' => 0, 'reorder_point' => 8],
            ['sku' => 'SAMPLE-006', 'name' => 'Baby Shampoo', 'brand' => 'Tiny Tots', 'size_content' => 'ml', 'size_content_amount' => 250, 'category' => 'Baby Care', 'unit' => 'bottle', 'selling_price' => 165, 'cost_price' => 120, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-007', 'name' => 'Baby Bath Wash', 'brand' => 'SoftCare', 'size_content' => 'ml', 'size_content_amount' => 200, 'category' => 'Bath/Hygiene', 'unit' => 'bottle', 'selling_price' => 150, 'cost_price' => 108, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-008', 'name' => 'Baby Powder', 'brand' => 'Tiny Tots', 'size_content' => 'g', 'size_content_amount' => 100, 'category' => 'Baby Care', 'unit' => 'bottle', 'selling_price' => 95, 'cost_price' => 65, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-009', 'name' => 'Baby Oil', 'brand' => 'SoftCare', 'size_content' => 'ml', 'size_content_amount' => 100, 'category' => 'Baby Care', 'unit' => 'bottle', 'selling_price' => 130, 'cost_price' => 92, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-010', 'name' => 'Diaper Rash Cream', 'brand' => 'Little Bloom', 'size_content' => 'g', 'size_content_amount' => 50, 'category' => 'Baby Care', 'unit' => 'bottle', 'selling_price' => 145, 'cost_price' => 100, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-011', 'name' => 'Baby Toothbrush Set', 'brand' => 'CleanStart', 'size_content' => 'pcs', 'size_content_amount' => 2, 'category' => 'Bath/Hygiene', 'unit' => 'set', 'selling_price' => 85, 'cost_price' => 55, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-012', 'name' => 'Baby Washcloths', 'brand' => 'SoftCare', 'size_content' => 'pcs', 'size_content_amount' => 6, 'category' => 'Bath/Hygiene', 'unit' => 'pack', 'selling_price' => 110, 'cost_price' => 72, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-013', 'name' => 'Feeding Bottle', 'brand' => 'Nurture', 'size_content' => 'ml', 'size_content_amount' => 240, 'category' => 'Feeding', 'unit' => 'bottle', 'selling_price' => 210, 'cost_price' => 150, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-014', 'name' => 'Bottle Cleaning Brush', 'brand' => 'Nurture', 'category' => 'Feeding', 'unit' => 'pc', 'selling_price' => 75, 'cost_price' => 48, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-015', 'name' => 'Silicone Baby Bib', 'brand' => 'Little Bloom', 'category' => 'Feeding', 'unit' => 'pc', 'selling_price' => 125, 'cost_price' => 82, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-016', 'name' => 'Baby Pacifier', 'brand' => 'Nurture', 'size_content' => 'pcs', 'size_content_amount' => 2, 'category' => 'Baby Care', 'unit' => 'set', 'selling_price' => 105, 'cost_price' => 68, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-017', 'name' => 'Soft Baby Teether', 'brand' => 'Playful', 'category' => 'Toys', 'unit' => 'pc', 'selling_price' => 90, 'cost_price' => 58, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-018', 'name' => 'Baby Rattle', 'brand' => 'Playful', 'category' => 'Toys', 'unit' => 'pc', 'selling_price' => 115, 'cost_price' => 74, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-019', 'name' => 'Baby Blanket', 'brand' => 'Snuggle', 'size_content' => 'pcs', 'size_content_amount' => 1, 'category' => 'Accessories', 'unit' => 'pc', 'selling_price' => 320, 'cost_price' => 225, 'quantity_on_hand' => 100, 'reorder_point' => 10],
            ['sku' => 'SAMPLE-020', 'name' => 'Baby Socks Set', 'brand' => 'Snuggle', 'size_content' => 'S', 'category' => 'Accessories', 'unit' => 'pair', 'selling_price' => 140, 'cost_price' => 90, 'quantity_on_hand' => 100, 'reorder_point' => 10],
        ];

        $productModels = [];
        foreach ($products as $product) {
            $productModel = Product::firstOrCreate(['sku' => $product['sku']], $product);
            $productModels[$productModel->sku] = $productModel;

            if ($productModel->wasRecentlyCreated && $productModel->quantity_on_hand > 0) {
                DB::table('inventory_movements')->insert([
                    'product_id' => $productModel->id,
                    'type' => 'adjustment',
                    'quantity_delta' => $productModel->quantity_on_hand,
                    'occurred_at' => now(),
                    'note' => 'Example opening stock',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->seedExampleSales($productModels);
        $this->seedExamplePurchaseOrders($productModels, $supplier);
    }

    private function seedExampleSales(array $products): void
    {
        $examples = [
            ['number' => 'DEMO-SALE-001', 'days_ago' => 1, 'method' => 'cash', 'items' => ['DIAP-M-01' => 2, 'LOTION-01' => 1]],
            ['number' => 'DEMO-SALE-002', 'days_ago' => 3, 'method' => 'gcash', 'items' => ['MILK-01' => 1, 'DIAP-L-01' => 1]],
            ['number' => 'DEMO-SALE-003', 'days_ago' => 5, 'method' => 'cash', 'items' => ['DIAP-M-01' => 1, 'LOTION-01' => 2]],
        ];

        foreach ($examples as $example) {
            $soldAt = now()->subDays($example['days_ago'])->setTime(10 + $example['days_ago'], 15);
            $lineItems = [];
            $total = 0;
            foreach ($example['items'] as $sku => $quantity) {
                $product = $products[$sku];
                $unitPrice = (float) $product->selling_price;
                $subtotal = round($unitPrice * $quantity, 2);
                $total += $subtotal;
                $lineItems[] = compact('product', 'quantity', 'unitPrice', 'subtotal');
            }

            $cashReceived = $example['method'] === 'cash' ? $total + 50 : $total;
            $sale = Sale::firstOrCreate(['sale_number' => $example['number']], [
                'cashier_name' => $example['days_ago'] % 2 ? 'Store Owner' : 'Store Staff',
                'payment_method' => $example['method'],
                'gcash_reference' => $example['method'] === 'gcash' ? 'DEMO-GCASH-001' : null,
                'total_amount' => $total,
                'cash_received' => $cashReceived,
                'change_due' => $cashReceived - $total,
            ]);

            if (!$sale->wasRecentlyCreated) {
                continue;
            }

            DB::table('sales')->where('id', $sale->id)->update([
                'created_at' => $soldAt,
                'updated_at' => $soldAt,
            ]);

            foreach ($lineItems as $line) {
                DB::table('sale_items')->insert([
                    'sale_id' => $sale->id,
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unitPrice'],
                    'unit_cost_at_sale' => $line['product']->cost_price,
                    'subtotal' => $line['subtotal'],
                    'created_at' => $soldAt,
                    'updated_at' => $soldAt,
                ]);

                $line['product']->decrement('quantity_on_hand', $line['quantity']);
                DB::table('inventory_movements')->insert([
                    'product_id' => $line['product']->id,
                    'type' => 'sale',
                    'quantity_delta' => -$line['quantity'],
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'occurred_at' => $soldAt,
                    'note' => 'Example sale',
                    'created_at' => $soldAt,
                    'updated_at' => $soldAt,
                ]);
            }
        }

        // Add stable examples up to 50 total transactions. The first three
        // remain the named cash/GCash examples above; these use the 15 sample
        // products and cover the last 47 days for meaningful report charts.
        for ($number = 4; $number <= 50; $number++) {
            $itemCount = 1 + ($number % 3);
            $lineItems = [];
            $total = 0;
            for ($offset = 0; $offset < $itemCount; $offset++) {
                $productNumber = 6 + (($number * 3 + $offset * 7) % 15);
                $product = $products[sprintf('SAMPLE-%03d', $productNumber)];
                $quantity = 1 + (($number + $offset) % 3);
                $unitPrice = (float) $product->selling_price;
                $subtotal = round($unitPrice * $quantity, 2);
                $total += $subtotal;
                $lineItems[] = compact('product', 'quantity', 'unitPrice', 'subtotal');
            }

            $method = $number % 2 === 0 ? 'cash' : 'gcash';
            $cashReceived = $method === 'cash' ? $total + 50 : $total;
            $sale = Sale::firstOrCreate(['sale_number' => sprintf('DEMO-SALE-%03d', $number)], [
                'cashier_name' => $number % 2 === 0 ? 'Store Owner' : 'Store Staff',
                'payment_method' => $method,
                'gcash_reference' => $method === 'gcash' ? sprintf('DEMO-GCASH-%03d', $number) : null,
                'total_amount' => $total,
                'cash_received' => $cashReceived,
                'change_due' => $cashReceived - $total,
            ]);

            if (!$sale->wasRecentlyCreated) {
                continue;
            }

            $soldAt = now()->subDays($number - 4)->setTime(10 + ($number % 9), ($number * 7) % 60);
            DB::table('sales')->where('id', $sale->id)->update(['created_at' => $soldAt, 'updated_at' => $soldAt]);

            foreach ($lineItems as $line) {
                DB::table('sale_items')->insert([
                    'sale_id' => $sale->id,
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unitPrice'],
                    'unit_cost_at_sale' => $line['product']->cost_price,
                    'subtotal' => $line['subtotal'],
                    'created_at' => $soldAt,
                    'updated_at' => $soldAt,
                ]);

                $line['product']->decrement('quantity_on_hand', $line['quantity']);
                DB::table('inventory_movements')->insert([
                    'product_id' => $line['product']->id,
                    'type' => 'sale',
                    'quantity_delta' => -$line['quantity'],
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'occurred_at' => $soldAt,
                    'note' => 'Example sale',
                    'created_at' => $soldAt,
                    'updated_at' => $soldAt,
                ]);
            }
        }
    }

    private function seedExamplePurchaseOrders(array $products, Supplier $supplier): void
    {
        $deliveredDate = now()->subDays(2)->toDateString();
        $deliveredItems = [['sku' => 'MILK-01', 'quantity' => 5]];
        $deliveredTotal = array_sum(array_map(fn ($item) => $item['quantity'] * (float) $products[$item['sku']]->cost_price, $deliveredItems));
        $deliveredOrder = PurchaseOrder::firstOrCreate(['po_number' => 'DEMO-PO-DELIVERED-001'], [
            'supplier_id' => $supplier->id,
            'status' => 'delivered',
            'order_date' => now()->subDays(4)->toDateString(),
            'expected_delivery_date' => $deliveredDate,
            'delivered_date' => $deliveredDate,
            'total_amount' => $deliveredTotal,
            'notes' => 'Example completed restock order.',
        ]);

        if ($deliveredOrder->wasRecentlyCreated) {
            foreach ($deliveredItems as $item) {
                $product = $products[$item['sku']];
                $unitCost = (float) $product->cost_price;
                $deliveredOrder->items()->create([
                    'product_id' => $product->id,
                    'quantity_ordered' => $item['quantity'],
                    'quantity_delivered' => $item['quantity'],
                    'unit_cost' => $unitCost,
                    'subtotal' => $unitCost * $item['quantity'],
                ]);
                $product->increment('quantity_on_hand', $item['quantity']);
                DB::table('inventory_movements')->insert([
                    'product_id' => $product->id,
                    'type' => 'receipt',
                    'quantity_delta' => $item['quantity'],
                    'reference_type' => 'purchase_order',
                    'reference_id' => $deliveredOrder->id,
                    'occurred_at' => $deliveredDate . ' 12:00:00',
                    'note' => 'Example delivered purchase order',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $pendingDate = now()->toDateString();
        $pendingItems = [['sku' => 'DIAP-L-01', 'quantity' => 3], ['sku' => 'WIPES-01', 'quantity' => 4]];
        $pendingTotal = array_sum(array_map(fn ($item) => $item['quantity'] * (float) $products[$item['sku']]->cost_price, $pendingItems));
        $pendingOrder = PurchaseOrder::firstOrCreate(['po_number' => 'DEMO-PO-PENDING-001'], [
            'supplier_id' => $supplier->id,
            'status' => 'pending',
            'order_date' => $pendingDate,
            'expected_delivery_date' => now()->addDays(7)->toDateString(),
            'total_amount' => $pendingTotal,
            'notes' => 'Example order awaiting delivery.',
        ]);

        if ($pendingOrder->wasRecentlyCreated) {
            foreach ($pendingItems as $item) {
                $product = $products[$item['sku']];
                $unitCost = (float) $product->cost_price;
                $pendingOrder->items()->create([
                    'product_id' => $product->id,
                    'quantity_ordered' => $item['quantity'],
                    'quantity_delivered' => 0,
                    'unit_cost' => $unitCost,
                    'subtotal' => $unitCost * $item['quantity'],
                ]);
            }
        }
    }
}
