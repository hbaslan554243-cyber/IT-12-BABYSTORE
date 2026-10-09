@php $p = $product ?? null; @endphp

@if($p)
<div class="mb-3">
    <label class="form-label">SKU</label>
    <input type="text" value="{{ $p->sku }}" class="form-control" disabled>
    <div class="form-text">System-generated — cannot be changed.</div>
</div>
@else
<div class="alert alert-success py-2 small">A SKU will be generated automatically once this product is saved — coded by category, e.g. <strong>DIA-0001</strong> for Diapers, <strong>MLK-0001</strong> for Milk.</div>
@endif

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Product Name</label>
        <input type="text" name="name" value="{{ old('name', $p->name ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Brand</label>
        <input type="text" name="brand" value="{{ old('brand', $p->brand ?? '') }}" class="form-control" placeholder="e.g. Pampers">
    </div>
    <div class="col-md-2">
        <label class="form-label">Variant</label>
        <input type="text" name="variant" value="{{ old('variant', $p->variant ?? '') }}" class="form-control" placeholder="e.g. Cool Blue">
        <div class="form-text">Color/scent/flavor</div>
    </div>
    <div class="col-md-3">
        <label class="form-label">Size</label>
        @php $currentSize = old('size', $p->size ?? ''); @endphp
        <select name="size" class="form-select">
            <option value="">-- None --</option>
            @foreach($sizes as $code => $label)
                <option value="{{ $code }}" {{ $currentSize === $code ? 'selected' : '' }}>{{ $code }} - {{ $label }}</option>
            @endforeach
            @if($currentSize && !array_key_exists($currentSize, $sizes))
                <option value="{{ $currentSize }}" selected>{{ $currentSize }} (existing)</option>
            @endif
        </select>
        <div class="form-text">Optional</div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Category</label>
        <select name="category" class="form-select" required>
            <option value="">-- Select category --</option>
            @foreach($categories as $c)
                <option value="{{ $c }}" {{ old('category', $p->category ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Unit</label>
        <select name="unit" class="form-select" required>
            <option value="">--</option>
            @foreach($units as $u)
                <option value="{{ $u }}" {{ old('unit', $p->unit ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
            @endforeach
        </select>
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
        <div class="form-text">Flags as "Low Stock" at or below this number.</div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Expiration Date</label>
        <input type="date" name="expiration_date" value="{{ old('expiration_date', isset($p->expiration_date) ? $p->expiration_date->format('Y-m-d') : '') }}" class="form-control">
        <div class="form-text">Optional — leave blank if not applicable.</div>
    </div>
</div>
<hr>