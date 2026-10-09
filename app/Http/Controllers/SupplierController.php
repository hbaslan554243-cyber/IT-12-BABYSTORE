<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(15);

        return view('suppliers.index', compact('suppliers'));
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

    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
        } catch (QueryException $e) {
            // Supplier is referenced by existing purchase orders (FK
            // restrict) — keep the historical record intact rather than
            // allowing an orphaned reference.
            return back()->withErrors([
                'supplier' => "Cannot delete \"{$supplier->name}\" — it has existing purchase orders on record. Historical records must be preserved.",
            ]);
        }

        return redirect()->route('suppliers.index')->with('status', 'Supplier removed.');
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
