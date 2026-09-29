@php $p = $product ?? null; @endphp
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">SKU</label>
        <input type="text" name="sku" value="{{ old('sku', $p->sku ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-8">
        <label class="form-label">Product Name</label>
        <input type="text" name="name" value="{{ old('name', $p->name ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Category</label>
        <input type="text" name="category" value="{{ old('category', $p->category ?? '') }}" class="form-control" placeholder="e.g. Diapers, Milk">
    </div>
    <div class="col-md-2">
        <label class="form-label">Unit</label>
        <input type="text" name="unit" value="{{ old('unit', $p->unit ?? 'pc') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Selling Price (₱)</label>
        <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', $p->selling_price ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Cost Price (₱)</label>
        <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $p->cost_price ?? 0) }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Quantity on Hand</label>
        <input type="number" name="quantity_on_hand" value="{{ old('quantity_on_hand', $p->quantity_on_hand ?? 0) }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Reorder Point</label>
        <input type="number" name="reorder_point" value="{{ old('reorder_point', $p->reorder_point ?? 5) }}" class="form-control" required>
        <div class="form-text">System flags this product as "low stock" at or below this number.</div>
    </div>
</div>
<hr>
