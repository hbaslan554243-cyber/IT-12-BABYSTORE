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
    <div class="col-12 col-md-4">
        <label class="form-label">Product Name</label>
        <input type="text" name="name" value="{{ old('name', $p->name ?? '') }}" class="form-control" required>
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Brand</label>
        <input type="text" name="brand" value="{{ old('brand', $p->brand ?? '') }}" class="form-control" placeholder="e.g. Pampers">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Variant</label>
        <input type="text" name="variant" value="{{ old('variant', $p->variant ?? '') }}" class="form-control" placeholder="e.g. Cool Blue">
        <div class="form-text">Color/scent/flavor</div>
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Category</label>
        <select name="category" class="form-select" required>
            <option value="">-- Select category --</option>
            @foreach($categories as $c)
                <option value="{{ $c }}" {{ old('category', $p->category ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Unit</label>
        <select name="unit" class="form-select" required>
            <option value="">--</option>
            @foreach($units as $u)
                <option value="{{ $u }}" {{ old('unit', $p->unit ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label" for="size-content-choice">Size / Content <span class="text-muted fw-normal">(optional)</span></label>
        @php
            $storedChoice = $p && $p->size_content
                ? (($p->size_content_amount !== null ? 'content:' : 'size:') . $p->size_content)
                : '';
            $currentChoice = old('size_content', $storedChoice);
            $currentAmount = old('size_content_amount', $p->size_content_amount ?? '');
            $isContentChoice = str_starts_with((string) $currentChoice, 'content:');
        @endphp
        <div class="d-flex gap-2 align-items-start">
            <select name="size_content" id="size-content-choice" class="form-select" style="flex:1; min-width:0;">
                <option value="">-- None --</option>
                <optgroup label="Size">
                    @foreach($sizeContentChoices as $choice => $label)
                        @if(str_starts_with($choice, 'size:'))
                            <option value="{{ $choice }}" {{ $currentChoice === $choice ? 'selected' : '' }}>{{ $label }}</option>
                        @endif
                    @endforeach
                    @if($p && $p->size_content && $p->size_content_amount === null && !array_key_exists('size:' . $p->size_content, $sizeContentChoices))
                        <option value="size:{{ $p->size_content }}" {{ $currentChoice === 'size:' . $p->size_content ? 'selected' : '' }}>{{ $p->size_content }} (existing)</option>
                    @endif
                </optgroup>
                <optgroup label="Content">
                    @foreach($sizeContentChoices as $choice => $label)
                        @if(str_starts_with($choice, 'content:'))
                            <option value="{{ $choice }}" {{ $currentChoice === $choice ? 'selected' : '' }}>{{ $label }}</option>
                        @endif
                    @endforeach
                </optgroup>
            </select>
            <div id="size-content-amount-wrap" class="flex-shrink-0" style="width: 42%; {{ $isContentChoice ? '' : 'display:none;' }}">
                <input type="number" name="size_content_amount" id="size-content-amount" value="{{ $currentAmount }}" class="form-control" min="0.001" step="any" placeholder="Amount, e.g. 200" {{ $isContentChoice ? 'required' : '' }}>
            </div>
        </div>
        <div class="form-text"></div>
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label">Selling Price (₱)</label>
        <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', $p->selling_price ?? '') }}" class="form-control" required>
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label">Cost Price (₱)</label>
        <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $p->cost_price ?? 0) }}" class="form-control">
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label">Quantity on Hand</label>
        <input type="number" name="quantity_on_hand" value="{{ old('quantity_on_hand', $p->quantity_on_hand ?? 0) }}" class="form-control" required>
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label">Reorder Point</label>
        <input type="number" name="reorder_point" value="{{ old('reorder_point', $p->reorder_point ?? 5) }}" class="form-control" required>
        <div class="form-text">Flags as "Low Stock" at or below this number.</div>
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Expiration Date</label>
        <input type="date" name="expiration_date" value="{{ old('expiration_date', isset($p->expiration_date) ? $p->expiration_date->format('Y-m-d') : '') }}" class="form-control">
        <div class="form-text">Optional — leave blank if not applicable.</div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const choice = document.getElementById('size-content-choice');
    const amountWrap = document.getElementById('size-content-amount-wrap');
    const amount = document.getElementById('size-content-amount');

    if (!choice || !amountWrap || !amount) return;

    choice.addEventListener('change', function () {
        const isContent = this.value.startsWith('content:');
        amountWrap.style.display = isContent ? '' : 'none';
        amount.required = isContent;
        if (!isContent) amount.value = '';
    });
});
</script>
<hr>
