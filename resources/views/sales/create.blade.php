@extends('layouts.app')
@section('title', 'New Sale')

@section('content')
    <h2>New Sale (Point of Sale) <small class="text-muted fs-6">(Problem 2 fix)</small></h2>

    <form method="POST" action="{{ route('sales.store') }}" id="pos-form">
        @csrf
        <input type="hidden" name="cashier_name" value="{{ auth()->user()->name }}">
        <input type="hidden" name="payment_method" id="payment-method" value="cash">

        <div class="pos-wrap">
            <div class="pos-main">
                <div class="card p-3">
                    <input type="text" id="pos-search" class="form-control mb-3" placeholder="🔍 Search product name or SKU...">

                    <div class="category-tabs" id="category-tabs">
                        <button type="button" class="category-tab active" data-category="">All</button>
                        @foreach($categories as $c)
                            <button type="button" class="category-tab" data-category="{{ $c }}">{{ $c }}</button>
                        @endforeach
                    </div>

                    <div class="product-grid" id="product-grid"></div>
                </div>
            </div>

            <div class="pos-checkout">
                <div class="card p-3">
                    <h5 class="mb-3">Checkout</h5>

                    <div id="cart-list"></div>

                    <hr>
                    <div class="d-flex justify-content-between fs-4 fw-bold mb-3">
                        <span>Total</span><span id="total-display">₱0.00</span>
                    </div>

                    <label class="form-label">Payment Method</label>
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-outline-primary flex-fill pay-method-btn active" data-method="cash">💵 Cash</button>
                        <button type="button" class="btn btn-outline-primary flex-fill pay-method-btn" data-method="gcash">📱 GCash</button>
                    </div>

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
                    </div>

                    <button type="submit" class="btn btn-brand w-100 mt-3" id="submit-btn">Pay <span id="pay-btn-total">₱0.00</span></button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<style>
    .pos-wrap { display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap; }
    .pos-main { flex: 2; min-width: 320px; }
    .pos-checkout { flex: 1; min-width: 300px; position: sticky; top: 16px; }

    .category-tabs { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 6px; margin-bottom: 14px; }
    .category-tab {
        padding: 8px 16px; border-radius: 12px; border: 1px solid var(--clr-border);
        background: #fff; cursor: pointer; font-weight: 700; font-size: 13px;
        white-space: nowrap; color: var(--clr-text); flex-shrink: 0;
    }
    .category-tab.active {
        background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-dark));
        color: #fff; border-color: transparent;
    }

    .product-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(135px, 1fr));
        gap: 14px; max-height: 600px; overflow-y: auto; padding: 4px 6px 4px 2px;
    }
    .product-card {
        background: #fff; border: 1px solid var(--clr-border); border-radius: 16px;
        padding: 14px 8px; text-align: center; cursor: pointer;
        transition: box-shadow .15s ease, transform .1s ease;
    }
    .product-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .product-card.disabled { opacity: .4; cursor: not-allowed; pointer-events: none; }
    .product-card .p-icon { font-size: 32px; margin-bottom: 6px; }
    .product-card .p-name { font-weight: 700; font-size: 12.5px; color: var(--clr-text); min-height: 32px; line-height: 1.25; }
    .product-card .p-price { color: var(--clr-primary-darker); font-weight: 800; font-size: 13.5px; margin-top: 2px; }
    .product-card .p-stock { font-size: 10.5px; color: var(--clr-text-muted); }
    .product-card .p-stock.low { color: #B48A3E; }

    .cart-line { display: flex; align-items: center; gap: 8px; padding: 9px 0; border-bottom: 1px solid var(--clr-border); }
    .cart-line .cart-name { flex: 1; font-size: 13px; font-weight: 700; color: var(--clr-text); }
    .cart-line .cart-sub { font-size: 11.5px; color: var(--clr-text-muted); }
    .cart-qty-btn {
        width: 24px; height: 24px; border-radius: 50%; border: 1px solid var(--clr-border);
        background: #fff; display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 14px; color: var(--clr-primary-darker); cursor: pointer; flex-shrink: 0;
    }
    .cart-qty-val { min-width: 20px; text-align: center; font-weight: 700; font-size: 13px; }
    .cart-remove { color: var(--clr-danger-text); cursor: pointer; font-size: 15px; flex-shrink: 0; }
    .cart-empty { text-align: center; color: var(--clr-text-muted); padding: 24px 8px; font-size: 13.5px; }
    .pay-method-btn.active {
        background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-dark)) !important;
        color: #fff !important; border-color: transparent !important;
    }
</style>

<script>
    // Pre-built in the controller, not inline-encoded here — see SaleController's create method.
    const ALL_PRODUCTS = @json($productsForJs);

    const CATEGORY_ICONS = {
        'Diapers': '👶', 'Milk': '🍼', 'Lotion': '🧴', 'Wipes': '🧻',
        'Feeding': '🥄', 'Clothing': '👕', 'Toys': '🧸', 'Accessories': '🎀', 'Other': '📦',
    };

    document.addEventListener('DOMContentLoaded', function () {
        const grid = document.getElementById('product-grid');
        const searchInput = document.getElementById('pos-search');
        const tabsWrap = document.getElementById('category-tabs');
        const cartList = document.getElementById('cart-list');
        const totalDisplay = document.getElementById('total-display');
        const payBtnTotal = document.getElementById('pay-btn-total');
        const changeDisplay = document.getElementById('change-display');
        const cashInput = document.getElementById('cash-received');
        const cashError = document.getElementById('cash-error');
        const form = document.getElementById('pos-form');
        const paymentMethodInput = document.getElementById('payment-method');
        const cashFields = document.getElementById('cash-fields');
        const gcashFields = document.getElementById('gcash-fields');

        let activeCategory = '';
        let cart = {}; // product_id -> { product, qty }

        function renderGrid() {
            const term = searchInput.value.trim().toLowerCase();
            grid.innerHTML = '';
            ALL_PRODUCTS
                .filter(p => !activeCategory || p.category === activeCategory)
                .filter(p => !term || p.name.toLowerCase().includes(term) || p.sku.toLowerCase().includes(term))
                .forEach(p => {
                    const card = document.createElement('div');
                    card.className = 'product-card' + (p.stock <= 0 ? ' disabled' : '');
                    card.innerHTML = `
                        <div class="p-icon">${CATEGORY_ICONS[p.category] || '📦'}</div>
                        <div class="p-name">${p.name}</div>
                        <div class="p-price">₱${p.price.toFixed(2)}</div>
                        <div class="p-stock ${p.stock <= 5 ? 'low' : ''}">Stock: ${p.stock}</div>
                    `;
                    card.addEventListener('click', () => addToCart(p));
                    grid.appendChild(card);
                });
            if (grid.innerHTML === '') {
                grid.innerHTML = '<div class="cart-empty" style="grid-column:1/-1;">No products match your search.</div>';
            }
        }

        function addToCart(product) {
            const existing = cart[product.id];
            const currentQty = existing ? existing.qty : 0;
            if (currentQty + 1 > product.stock) {
                alert('Only ' + product.stock + ' left in stock for ' + product.name + '.');
                return;
            }
            cart[product.id] = { product, qty: currentQty + 1 };
            renderCart();
        }

        function changeQty(productId, delta) {
            const line = cart[productId];
            if (!line) return;
            const newQty = line.qty + delta;
            if (newQty <= 0) {
                delete cart[productId];
            } else if (newQty > line.product.stock) {
                alert('Only ' + line.product.stock + ' left in stock for ' + line.product.name + '.');
            } else {
                line.qty = newQty;
            }
            renderCart();
        }

        function removeLine(productId) {
            delete cart[productId];
            renderCart();
        }

        function renderCart() {
            const lines = Object.values(cart);
            cartList.innerHTML = '';
            if (lines.length === 0) {
                cartList.innerHTML = '<div class="cart-empty">Tap a product to add it here.</div>';
            } else {
                lines.forEach(line => {
                    const subtotal = line.product.price * line.qty;
                    const row = document.createElement('div');
                    row.className = 'cart-line';
                    row.innerHTML = `
                        <div>
                            <div class="cart-name">${line.product.name}</div>
                            <div class="cart-sub">₱${line.product.price.toFixed(2)} each · ₱${subtotal.toFixed(2)}</div>
                        </div>
                        <div class="cart-qty-btn" data-action="dec">−</div>
                        <div class="cart-qty-val">${line.qty}</div>
                        <div class="cart-qty-btn" data-action="inc">+</div>
                        <div class="cart-remove" data-action="remove">🗑</div>
                    `;
                    row.querySelector('[data-action="dec"]').addEventListener('click', () => changeQty(line.product.id, -1));
                    row.querySelector('[data-action="inc"]').addEventListener('click', () => changeQty(line.product.id, 1));
                    row.querySelector('[data-action="remove"]').addEventListener('click', () => removeLine(line.product.id));
                    cartList.appendChild(row);
                });
            }
            recalcTotal();
        }

        function recalcTotal() {
            const total = Object.values(cart).reduce((sum, l) => sum + l.product.price * l.qty, 0);
            totalDisplay.textContent = '₱' + total.toFixed(2);
            payBtnTotal.textContent = '₱' + total.toFixed(2);
            form.dataset.total = total;
            checkCash(total);
        }

        // Core error-handling requirement: flag it immediately whenever
        // cash received is lower than the total amount due.
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

        searchInput.addEventListener('input', renderGrid);
        tabsWrap.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                tabsWrap.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                activeCategory = this.dataset.category;
                renderGrid();
            });
        });
        cashInput.addEventListener('input', () => checkCash(parseFloat(form.dataset.total) || 0));

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
                recalcTotal();
            });
        });

        form.addEventListener('submit', function (e) {
            document.querySelectorAll('input[name^="items"]').forEach(el => el.remove());
            const lines = Object.values(cart);
            if (lines.length === 0) {
                e.preventDefault();
                alert('Tap at least one product to add it to the sale before completing checkout.');
                return;
            }

            lines.forEach((line, index) => {
                const pidInput = document.createElement('input');
                pidInput.type = 'hidden';
                pidInput.name = `items[${index}][product_id]`;
                pidInput.value = line.product.id;
                form.appendChild(pidInput);

                const qtyInput = document.createElement('input');
                qtyInput.type = 'hidden';
                qtyInput.name = `items[${index}][quantity]`;
                qtyInput.value = line.qty;
                form.appendChild(qtyInput);
            });

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

        renderGrid();
        renderCart();
    });
</script>
@endsection
