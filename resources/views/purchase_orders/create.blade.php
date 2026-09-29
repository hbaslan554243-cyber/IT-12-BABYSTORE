@extends('layouts.app')
@section('title', 'New Purchase Order')

@section('content')
    <h2>New Purchase Order <small class="text-muted fs-6">(supporting restocking module)</small></h2>
    <p class="text-muted">Formally records what was ordered from the supplier — replacing verbal or Messenger-only arrangements.</p>

    <form method="POST" action="{{ route('purchase-orders.store') }}" id="po-form">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="card p-3 mb-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-select" required>
                                <option value="">-- Select supplier --</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                            @if($suppliers->isEmpty())
                                <div class="form-text text-danger">No suppliers yet — <a href="{{ route('suppliers.create') }}">add one first</a>.</div>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Order Date</label>
                            <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Expected Delivery</label>
                            <input type="date" name="expected_delivery_date" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="card p-3">
                    <h5>Items to Order</h5>
                    <table class="table" id="items-table">
                        <thead><tr><th>Product</th><th style="width:100px">Qty</th><th style="width:140px">Unit Cost (₱)</th><th>Subtotal</th><th></th></tr></thead>
                        <tbody></tbody>
                    </table>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="add-item">+ Add Item</button>
                </div>

                <div class="card p-3 mt-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. delivery instructions, arrangement details"></textarea>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3">
                    <h5>Summary</h5>
                    <div class="d-flex justify-content-between fs-4 fw-bold">
                        <span>Order Total</span><span id="total-display">₱0.00</span>
                    </div>
                    <button class="btn btn-danger w-100 mt-3">Save Purchase Order</button>
                </div>
            </div>
        </div>
    </form>

    <template id="row-template">
        <tr>
            <td>
                <select class="form-select product-select">
                    <option value="">-- Select product --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-cost="{{ $product->cost_price }}">
                            {{ $product->name }} (On hand: {{ $product->quantity_on_hand }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" min="1" value="1" class="form-control qty-input"></td>
            <td><input type="number" min="0" step="0.01" value="0" class="form-control cost-input"></td>
            <td class="subtotal">₱0.00</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
        </tr>
    </template>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.querySelector('#items-table tbody');
    const template = document.getElementById('row-template');
    const totalDisplay = document.getElementById('total-display');
    const form = document.getElementById('po-form');

    function addRow() {
        const clone = template.content.cloneNode(true);
        tbody.appendChild(clone);
        bindRow(tbody.lastElementChild);
        recalc();
    }

    function bindRow(row) {
        row.querySelector('.product-select').addEventListener('change', () => {
            const select = row.querySelector('.product-select');
            const opt = select.options[select.selectedIndex];
            const cost = opt && opt.dataset.cost ? parseFloat(opt.dataset.cost) : 0;
            row.querySelector('.cost-input').value = cost.toFixed(2);
            updateRow(row);
        });
        row.querySelector('.qty-input').addEventListener('input', () => updateRow(row));
        row.querySelector('.cost-input').addEventListener('input', () => updateRow(row));
        row.querySelector('.remove-row').addEventListener('click', () => { row.remove(); recalc(); });
    }

    function updateRow(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
        row.querySelector('.subtotal').textContent = '₱' + (qty * cost).toFixed(2);
        recalc();
    }

    function recalc() {
        let total = 0;
        tbody.querySelectorAll('.subtotal').forEach(el => {
            total += parseFloat(el.textContent.replace('₱', '')) || 0;
        });
        totalDisplay.textContent = '₱' + total.toFixed(2);
    }

    document.getElementById('add-item').addEventListener('click', addRow);

    form.addEventListener('submit', function (e) {
        document.querySelectorAll('input[name^="items"]').forEach(el => el.remove());
        let index = 0, valid = false;
        tbody.querySelectorAll('tr').forEach(row => {
            const select = row.querySelector('.product-select');
            const qty = row.querySelector('.qty-input').value;
            const cost = row.querySelector('.cost-input').value;
            if (select.value && qty > 0) {
                valid = true;
                ['product_id', 'quantity_ordered', 'unit_cost'].forEach((field, i) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `items[${index}][${field}]`;
                    input.value = [select.value, qty, cost][i];
                    form.appendChild(input);
                });
                index++;
            }
        });
        if (!valid) {
            e.preventDefault();
            alert('Add at least one item to the purchase order.');
        }
    });

    addRow();
});
</script>
@endsection
