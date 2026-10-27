<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::active()->orderBy('name')->paginate(15);
        $archivedSuppliers = Supplier::whereNotNull('archived_at')->orderByDesc('archived_at')->get();

        return view('suppliers.index', compact('suppliers', 'archivedSuppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Supplier::create($data);

        return redirect()->route('suppliers.index')->with('status', 'Supplier added successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $this->validated($request);

        $supplier->update($data);

        return redirect()->route('suppliers.index')->with('status', 'Supplier updated successfully.');
    }

    public function archive(Supplier $supplier)
    {
        if (!$supplier->isArchived()) {
            $supplier->update(['archived_at' => now()]);
        }

        return redirect()->route('suppliers.index')->with('status', 'Supplier archived. You can find it in Archived suppliers.');
    }

    public function restore(Supplier $supplier)
    {
        $supplier->update(['archived_at' => null]);

        return redirect()->route('suppliers.index')->with('status', 'Supplier restored.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:50',
            'facebook_handle' => 'nullable|string|max:150',
            'address' => 'nullable|string|max:255',
        ]);
    }
}
