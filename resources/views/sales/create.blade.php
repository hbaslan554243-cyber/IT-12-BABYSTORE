@extends('layouts.app')
@section('title', 'New Sale')

@section('content')
    <h2>New Sale (Point of Sale) <small class="text-muted fs-6"></small></h2>
    <p class="text-muted">Totals are computed automatically — no more manual mental arithmetic or miscalculation.</p>

    <form method="POST" action="{{ route('sales.store') }}" id="pos-form">
        @csrf
        <input type="hidden" name="cashier_name" value="{{ auth()->user()->name }}">

        <div class="row">
            <div class="col-md-8">
                <div class="card p-3">
                    <h5>Items</h5>
                    <table class="table" id="items-table">
                        <thead><tr><th>Product</th><th>Unit Price</th><th style="width:100px">Qty</th><th>Subtotal</th><th></th></tr></thead>
                        <tbody></tbody>
                    </table>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="add-item">+ Add Item</button>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3">
                    <h5>Summary</h5>
                    <div class="d-flex justify-content-between fs-4 fw-bold mb-3">
                        <span>Total</span><span id="total-display">₱0.00</span>
                    </div>
                    <hr>

                    <label class="form-label">Payment Method</label>
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-outline-primary flex-fill pay-method-btn active" data-method="cash">💵 Cash</button>
                        <button type="button" class="btn btn-outline-primary flex-fill pay-method-btn" data-method="gcash">📱 GCash</button>
                    </div>
                    <input type="hidden" name="payment_method" id="payment-method" value="cash">

                    <div id="cash-fields">
                        <label class="form-label">Cash Received (₱)</label>
                        <input type="number" step="0.01" name="cash_received" id="cash-received" class="form-control mb-2">
                        <div id="cash-error" class="text-danger small mb-2" style="display:none;"></div>
                        <div class="d-flex justify-content-between">
                            <span>Change Due</span><span id="change-display">₱0.00</span>
                        </div>
                    </div>

                    <div id="gcash-fields" style="display:none;">
                        <label class="form-label">GCash Reference Number</label>
                        <input type="text" name="gcash_reference" id="gcash-reference" class="form-control" placeholder="e.g. 1234-567-890123">
                        <div class="form-text">Enter the reference number shown after the customer completes the GCash payment.</div>
                    </div>

                    <button class="btn btn-brand w-100 mt-3" id="submit-btn">Complete Sale &amp; Generate Receipt</button>
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
                        <option value="{{ $product->id }}" data-price="{{ $product->selling_price }}" data-stock="{{ $product->quantity_on_hand }}">
                            {{ $product->name }} (Stock: {{ $product->quantity_on_hand }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="unit-price">₱0.00</td>
            <td><input type="number" min="1" value="1" class="form-control qty-input"></td>
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
    const changeDisplay = document.getElementById('change-display');
    const cashInput = document.getElementById('cash-received');
    const cashError = document.getElementById('cash-error');
    const form = document.getElementById('pos-form');
    const paymentMethodInput = document.getElementById('payment-method');
    const cashFields = document.getElementById('cash-fields');
    const gcashFields = document.getElementById('gcash-fields');

    function addRow() {
        const clone = template.content.cloneNode(true);
        tbody.appendChild(clone);
        bindRow(tbody.lastElementChild);
        recalc();
    }

    function bindRow(row) {
        const select = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');
        const removeBtn = row.querySelector('.remove-row');

        select.addEventListener('change', () => updateRow(row));
        qtyInput.addEventListener('input', () => updateRow(row));
        removeBtn.addEventListener('click', () => { row.remove(); recalc(); });
    }

    function updateRow(row) {
        const select = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');
        const opt = select.options[select.selectedIndex];
        const price = opt && opt.dataset.price ? parseFloat(opt.dataset.price) : 0;
        const stock = opt && opt.dataset.stock ? parseInt(opt.dataset.stock) : 0;
        let qty = parseInt(qtyInput.value) || 0;

        if (qty > stock && stock > 0) {
            alert('Only ' + stock + ' left in stock.');
            qty = stock;
            qtyInput.value = stock;
        }

        const subtotal = price * qty;
        row.querySelector('.unit-price').textContent = '₱' + price.toFixed(2);
        row.querySelector('.subtotal').textContent = '₱' + subtotal.toFixed(2);
        recalc();
    }

    function recalc() {
        let total = 0;
        tbody.querySelectorAll('tr').forEach(row => {
            const text = row.querySelector('.subtotal').textContent.replace('₱', '');
            total += parseFloat(text) || 0;
        });
        totalDisplay.textContent = '₱' + total.toFixed(2);
        checkCash(total);
        form.dataset.total = total;
    }

    // Core error-handling requirement: flag it immediately, in real time,
    // whenever cash received is lower than the total amount due.
    function checkCash(total) {
        const cash = parseFloat(cashInput.value) || 0;
        if (paymentMethodInput.value === 'cash' && cashInput.value !== '' && cash < total) {
            cashError.textContent = 'Cash received is lower than the total amount due.';
            cashError.style.display = 'block';
            changeDisplay.textContent = '₱0.00';
            changeDisplay.classList.add('text-danger');
        } else {
            cashError.style.display = 'none';
            changeDisplay.classList.remove('text-danger');
            changeDisplay.textContent = '₱' + Math.max(0, cash - total).toFixed(2);
        }
    }

    cashInput.addEventListener('input', () => checkCash(parseFloat(form.dataset.total) || 0));
    document.getElementById('add-item').addEventListener('click', addRow);

    // Payment method toggle
    document.querySelectorAll('.pay-method-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.pay-method-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const method = this.dataset.method;
            paymentMethodInput.value = method;
            if (method === 'cash') {
                cashFields.style.display = 'block';
                gcashFields.style.display = 'none';
                cashInput.setAttribute('required', 'required');
                document.getElementById('gcash-reference').removeAttribute('required');
            } else {
                cashFields.style.display = 'none';
                gcashFields.style.display = 'block';
                cashInput.removeAttribute('required');
                document.getElementById('gcash-reference').setAttribute('required', 'required');
                cashError.style.display = 'none';
            }
        });
    });

    form.addEventListener('submit', function (e) {
        // Build hidden inputs for items[] before submitting
        document.querySelectorAll('input[name^="items"]').forEach(el => el.remove());
        let index = 0;
        let valid = false;
        tbody.querySelectorAll('tr').forEach(row => {
            const select = row.querySelector('.product-select');
            const qty = row.querySelector('.qty-input').value;
            if (select.value && qty > 0) {
                valid = true;
                const pidInput = document.createElement('input');
                pidInput.type = 'hidden';
                pidInput.name = `items[${index}][product_id]`;
                pidInput.value = select.value;
                form.appendChild(pidInput);

                const qtyHidden = document.createElement('input');
                qtyHidden.type = 'hidden';
                qtyHidden.name = `items[${index}][quantity]`;
                qtyHidden.value = qty;
                form.appendChild(qtyHidden);
                index++;
            }
        });
        if (!valid) {
            e.preventDefault();
            alert('Add at least one item before completing the sale.');
            return;
        }

        // Client-side guard mirroring the server-side rule: never submit a
        // cash sale where the amount received is below the total due.
        const total = parseFloat(form.dataset.total) || 0;
        if (paymentMethodInput.value === 'cash') {
            const cash = parseFloat(cashInput.value) || 0;
            if (cash < total) {
                e.preventDefault();
                checkCash(total);
                cashInput.focus();
            }
        }
    });

    addRow();
});
</script>
@endsection
