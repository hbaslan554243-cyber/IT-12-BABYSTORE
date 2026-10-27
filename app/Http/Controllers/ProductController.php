<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active();

        if ($request->filled('search')) {
            $search = $request->search;
            $matchingSizeChoices = [];
            foreach (Product::SIZE_CONTENT_CHOICES as $choice => $label) {
                [$type, $value] = explode(':', $choice, 2);
                if (strcasecmp($value, $search) === 0 || (mb_strlen($search) > 1 && stripos($label, $search) !== false)) {
                    $matchingSizeChoices[] = [$type, $value];
                }
            }
            preg_match('/^\s*(\d+(?:\.\d+)?)\s*(ml|l|g|kg|sheets|pcs)\s*$/i', $search, $measurementMatch);

            $query->where(function ($q) use ($search, $matchingSizeChoices, $measurementMatch) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('sku', 'like', '%' . $search . '%')
                  ->orWhere('brand', 'like', '%' . $search . '%')
                  ->orWhere('variant', 'like', '%' . $search . '%')
                  ->orWhere('size', 'like', '%' . $search . '%');

                if (mb_strlen($search) > 1) {
                    $q->orWhere('size_content', 'like', '%' . $search . '%');
                }
                if ($matchingSizeChoices) {
                    $q->orWhere(function ($choiceQuery) use ($matchingSizeChoices) {
                        foreach ($matchingSizeChoices as [$type, $value]) {
                            $choiceQuery->orWhere(function ($specificChoice) use ($type, $value) {
                                $specificChoice->where('size_content', $value);
                                if ($type === 'size') {
                                    $specificChoice->whereNull('size_content_amount');
                                }
                            });
                        }
                    });
                }
                if ($measurementMatch) {
                    $amount = (float) $measurementMatch[1];
                    $unit = strtolower($measurementMatch[2]);
                    $q->orWhere(function ($measurementQuery) use ($amount, $unit) {
                        $measurementQuery->where('size_content_amount', $amount)
                            ->whereRaw('LOWER(size_content) = ?', [$unit]);
                    });
                }
            });
        }

        if ($request->get('stock_filter') === 'low') {
            $query->lowStock();
        } elseif ($request->get('stock_filter') === 'out') {
            $query->outOfStock();
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();

        $outOfStockCount = Product::active()->outOfStock()->count();
        $lowStockCount = Product::active()->lowStock()->count();
        $activeProductCount = Product::active()->count();
        $archivedProducts = Product::whereNotNull('archived_at')->orderByDesc('archived_at')->get();

        return view('products.index', compact('products', 'outOfStockCount', 'lowStockCount', 'activeProductCount', 'archivedProducts'));
    }

    public function create()
    {
        return view('products.create', [
            'categories' => Product::CATEGORIES,
            'units' => Product::UNITS,
            'sizeContentChoices' => Product::SIZE_CONTENT_CHOICES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request, Product::CATEGORIES);
        $data = $this->normalizeSizeContent($data);

        $data['sku'] = Product::generateSku($data['category']);
        $product = Product::create($data);

        if ($product->quantity_on_hand > 0) {
            DB::table('inventory_movements')->insert([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity_delta' => $product->quantity_on_hand,
                'occurred_at' => now(),
                'note' => 'Initial stock entered',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('products.index')->with('status', 'Product added successfully. SKU: ' . $product->sku);
    }

    public function edit(Product $product)
    {
        $sizeContentChoices = Product::SIZE_CONTENT_CHOICES;
        if ($product->size_content && $product->size_content_amount === null && !array_key_exists('size:' . $product->size_content, $sizeContentChoices)) {
            $sizeContentChoices['size:' . $product->size_content] = $product->size_content . ' (existing)';
        }

        return view('products.edit', [
            'product' => $product,
            'categories' => Product::categoriesForEdit($product->category),
            'units' => Product::UNITS,
            'sizeContentChoices' => $sizeContentChoices,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $categories = Product::categoriesForEdit($product->category);
        $data = $this->normalizeSizeContent($this->validateProduct($request, $categories, $product));

        $stockDelta = (int) $data['quantity_on_hand'] - (int) $product->quantity_on_hand;
        DB::transaction(function () use ($product, $data, $stockDelta) {
            $product->update($data);

            if ($stockDelta !== 0) {
                DB::table('inventory_movements')->insert([
                    'product_id' => $product->id,
                    'type' => 'adjustment',
                    'quantity_delta' => $stockDelta,
                    'occurred_at' => now(),
                    'note' => 'Manual stock adjustment',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('products.index')->with('status', 'Product updated successfully.');
    }

    public function archive(Product $product)
    {
        if (!$product->isArchived()) {
            $product->update(['archived_at' => now()]);
        }

        return redirect()->route('products.index')->with('status', 'Product archived. You can find it in Archived products.');
    }

    public function restore(Product $product)
    {
        $product->update(['archived_at' => null]);

        return redirect()->route('products.index')->with('status', 'Product restored to inventory.');
    }

    private function validateProduct(Request $request, array $categories, ?Product $product = null): array
    {
        $sizeContentChoices = Product::SIZE_CONTENT_CHOICES;
        if ($product && $product->size_content && $product->size_content_amount === null && !array_key_exists('size:' . $product->size_content, $sizeContentChoices)) {
            $sizeContentChoices['size:' . $product->size_content] = $product->size_content . ' (existing)';
        }

        return $request->validate([
            'name' => 'required|string|max:150',
            'brand' => 'nullable|string|max:150',
            'variant' => 'nullable|string|max:100',
            'size_content' => ['nullable', Rule::in(array_keys($sizeContentChoices))],
            'size_content_amount' => [
                Rule::requiredIf(fn () => str_starts_with((string) $request->input('size_content', ''), 'content:')),
                'nullable', 'numeric', 'gt:0', 'max:999999999',
            ],
            'category' => ['required', Rule::in($categories)],
            'unit' => ['required', Rule::in(Product::UNITS)],
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity_on_hand' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);
    }

    private function normalizeSizeContent(array $data): array
    {
        $choice = $data['size_content'] ?? '';
        if ($choice === '') {
            $data['size_content'] = null;
            $data['size_content_amount'] = null;
        } else {
            [$type, $value] = explode(':', $choice, 2);
            $data['size_content'] = $value;
            $data['size_content_amount'] = $type === 'content' ? $data['size_content_amount'] : null;
        }

        // Keep the legacy field empty; size_content is now the canonical value.
        $data['size'] = null;

        return $data;
    }
}
