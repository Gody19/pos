import { showToast, confirmAction } from './helpers';

document.addEventListener('DOMContentLoaded', () => {
    const pos = document.querySelector('[data-pos-page]');
    if (!pos) return;

    const barcodeInput = document.querySelector('#barcode-input');
    const searchInput = document.querySelector('#pos-search');
    const categoryFilter = document.querySelector('#category-filter');
    const productGrid = document.querySelector('#product-grid');
    const cartPanel = document.querySelector('#cart-panel');
    const cartItems = document.querySelector('#cart-items');
    const totalsSubtotal = document.querySelector('#totals-subtotal');
    const totalsDiscount = document.querySelector('#totals-discount');
    const totalsTax = document.querySelector('#totals-tax');
    const totalsTotal = document.querySelector('#totals-total');
    const checkoutBtn = document.querySelector('#checkout-btn');
    const customerSearch = document.querySelector('#customer-search');
    const customerResults = document.querySelector('#customer-results');
    const cartForm = document.querySelector('#cart-form');

    const loadCart = async () => {
        const res = await fetch('/pos/cart', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        renderCart(data);
    };

    const renderCart = (data) => {
        const { cart, subtotal, discount, tax, total, count } = data;
        cartPanel.dataset.count = count;
        document.querySelectorAll('[data-cart-count]').forEach((el) => {
            el.textContent = count;
            el.classList.toggle('hidden', count === 0);
        });

        if (!cart.length) {
            cartItems.innerHTML = `
                <div class="py-16 text-center text-gray-400">
                    <p class="text-4xl mb-2">🛒</p>
                    <p class="text-sm">Cart is empty</p>
                    <p class="text-xs mt-1">Scan or search a product to begin</p>
                </div>`;
            totalsSubtotal.textContent = window.formatMoney(0);
            totalsDiscount.textContent = window.formatMoney(0);
            totalsTax.textContent = window.formatMoney(0);
            totalsTotal.textContent = window.formatMoney(0);
            const emptyTotal = document.querySelector('#checkout-total');
            if (emptyTotal) emptyTotal.textContent = window.formatMoney(0);
            checkoutBtn.disabled = true;
            checkoutBtn.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }

        checkoutBtn.disabled = false;
        checkoutBtn.classList.remove('opacity-50', 'cursor-not-allowed');

        cartItems.innerHTML = cart.map((item) => `
            <div class="flex flex-col gap-2 border-b border-gray-100 px-4 py-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-gray-800">${item.name}</p>
                        <p class="text-xs text-gray-400">${window.formatMoney(item.price)} × ${item.quantity}</p>
                    </div>
                    <button type="button" class="text-gray-300 hover:text-red-500" data-remove="${item.product_id}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        <button type="button" class="h-7 w-7 rounded-md border border-gray-200 text-gray-500 hover:bg-gray-100"
                            data-qty="${item.product_id}" data-delta="-1">−</button>
                        <span class="w-8 text-center text-sm font-semibold">${item.quantity}</span>
                        <button type="button" class="h-7 w-7 rounded-md border border-gray-200 text-gray-500 hover:bg-gray-100"
                            data-qty="${item.product_id}" data-delta="1">+</button>
                    </div>
                    <span class="text-sm font-bold text-gray-900">${window.formatMoney(item.total)}</span>
                </div>
            </div>`).join('');

        totalsSubtotal.textContent = window.formatMoney(subtotal);
        totalsDiscount.textContent = window.formatMoney(discount);
        totalsTax.textContent = window.formatMoney(tax);
        totalsTotal.textContent = window.formatMoney(total);
        const checkoutTotal = document.querySelector('#checkout-total');
        if (checkoutTotal) checkoutTotal.textContent = window.formatMoney(total);
    };

    const postCart = async (url, body) => {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'Something went wrong.');
        }
        return data;
    };

    const addToCart = async (productId, quantity = 1) => {
        try {
            const data = await postCart('/pos/cart/add', { product_id: productId, quantity });
            renderCart(data);
            barcodeInput.value = '';
            searchInput.value = '';
        } catch (err) {
            showToast(err.message, 'error');
        }
    };

    productGrid?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-product-id]');
        if (btn) addToCart(btn.dataset.productId);
    });

    cartItems?.addEventListener('click', async (e) => {
        const remove = e.target.closest('[data-remove]');
        if (remove) {
            if (!(await confirmAction('Remove this item from the cart?'))) return;
            try {
                const data = await postCart('/pos/cart/remove', { product_id: remove.dataset.remove });
                renderCart(data);
            } catch (err) {
                showToast(err.message, 'error');
            }
            return;
        }

        const qty = e.target.closest('[data-qty]');
        if (qty) {
            try {
                const data = await postCart('/pos/cart/update', {
                    product_id: qty.dataset.qty,
                    quantity: qty.dataset.delta,
                });
                renderCart(data);
            } catch (err) {
                showToast(err.message, 'error');
            }
        }
    });

    barcodeInput?.addEventListener('keydown', async (e) => {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const value = barcodeInput.value.trim();
        if (!value) return;
        try {
            const res = await fetch(`/products/barcode/${encodeURIComponent(value)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();
            if (!res.ok) {
                showToast(data.message || 'Product not found.', 'error');
                barcodeInput.value = '';
                return;
            }
            addToCart(data.data.id);
        } catch (err) {
            showToast('Failed to look up barcode.', 'error');
        }
    });

    let searchTimer = null;
    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(async () => {
            const term = searchInput.value.trim();
            const category = categoryFilter?.value || '';
            const params = new URLSearchParams({ search: term, category_id: category });
            const res = await fetch(`/pos/products?${params}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            productGrid.innerHTML = data.html;
        }, 250);
    });

    document.addEventListener('click', (e) => {
        if (e.target.id === 'clear-filters-btn') {
            searchInput.value = '';
            categoryFilter.value = '';
            searchInput.dispatchEvent(new Event('input'));
        }
    });

    categoryFilter?.addEventListener('change', () => {
        searchInput.dispatchEvent(new Event('input'));
    });

    let customerTimer = null;
    customerSearch?.addEventListener('input', () => {
        clearTimeout(customerTimer);
        const term = customerSearch.value.trim();
        if (term.length < 1) {
            customerResults.innerHTML = '';
            return;
        }
        customerTimer = setTimeout(async () => {
            const res = await fetch(`/customers/search?q=${encodeURIComponent(term)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();
            customerResults.innerHTML = data.html || '';
        }, 250);
    });

    customerResults?.addEventListener('click', (e) => {
        const option = e.target.closest('[data-customer]');
        if (option) {
            customerSearch.value = option.dataset.customerName;
            customerSearch.dataset.customerId = option.dataset.customer;
            customerResults.innerHTML = '';
        }
    });

    const discountInput = document.querySelector('#cart-discount');
    const noteInput = document.querySelector('#cart-note');
    const paymentModal = document.querySelector('#payment-modal');
    const paymentMethods = document.querySelector('#payment-methods');
    const addPaymentRowBtn = document.querySelector('#add-payment-row');
    const payRemaining = document.querySelector('#pay-remaining');
    const completeSaleBtn = document.querySelector('#complete-sale-btn');

    discountInput?.addEventListener('input', () => {
        const subtotal = parseFloat(totalsSubtotal.textContent.replace(/,/g, '')) || 0;
        const tax = parseFloat(totalsTax.textContent.replace(/,/g, '')) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const total = Math.max(subtotal - discount + tax, 0);
        totalsTotal.textContent = window.formatMoney(total);
        const checkoutTotal = document.querySelector('#checkout-total');
        if (checkoutTotal) checkoutTotal.textContent = window.formatMoney(total);
        payRemaining?.textContent = window.formatMoney(total);
    });

    const cartTotal = () => parseFloat(totalsTotal.textContent.replace(/,/g, '')) || 0;

    const updatePayRemaining = () => {
        const entered = [...paymentMethods.querySelectorAll('.payment-amount')]
            .reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
        const remaining = cartTotal() - entered;
        payRemaining.textContent = window.formatMoney(Math.max(remaining, 0));
        payRemaining.classList.toggle('text-red-600', remaining < -0.01);
    };

    const addPaymentRow = (method = 'cash', amount = '') => {
        const row = document.createElement('div');
        row.className = 'payment-row flex items-center gap-2';
        row.innerHTML = `
            <select class="payment-method input flex-1">
                ${['cash', 'card', 'mobile', 'qr'].map((m) =>
                    `<option value="${m}" ${m === method ? 'selected' : ''}>${m.charAt(0).toUpperCase() + m.slice(1)}</option>`
                ).join('')}
            </select>
            <input type="number" min="0" step="0.01" placeholder="Amount" value="${amount}"
                class="payment-amount input w-32" inputmode="decimal">
            <button type="button" class="remove-payment h-8 w-8 rounded-md text-gray-300 hover:text-red-500">
                <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>`;
        row.querySelectorAll('.payment-amount, .payment-method').forEach((el) =>
            el.addEventListener('input', updatePayRemaining));
        row.querySelector('.remove-payment').addEventListener('click', () => {
            if (paymentMethods.querySelectorAll('.payment-row').length > 1) {
                row.remove();
                updatePayRemaining();
            }
        });
        paymentMethods.appendChild(row);
        row.querySelector('.payment-amount').focus();
        updatePayRemaining();
    };

    checkoutBtn?.addEventListener('click', () => {
        paymentMethods.innerHTML = '';
        addPaymentRow('cash', cartTotal());
        paymentModal?.classList.remove('hidden');
        paymentModal?.querySelector('[data-close-payment]')?.focus();
    });

    addPaymentRowBtn?.addEventListener('click', () => addPaymentRow('cash', ''));

    completeSaleBtn?.addEventListener('click', async () => {
        const payments = [...paymentMethods.querySelectorAll('.payment-row')].map((row) => ({
            method: row.querySelector('.payment-method').value,
            amount: parseFloat(row.querySelector('.payment-amount').value) || 0,
        }));
        const payload = {
            customer_id: customerSearch?.dataset.customerId || null,
            discount: discountInput?.value || 0,
            notes: noteInput?.value || '',
            payments,
        };
        completeSaleBtn.disabled = true;
        completeSaleBtn.textContent = 'Processing…';
        try {
            const data = await postCart('/pos/checkout', payload);
            window.location.href = `/sales/${data.sale_id}/receipt`;
        } catch (err) {
            showToast(err.message, 'error');
            completeSaleBtn.disabled = false;
            completeSaleBtn.textContent = 'Complete Sale';
        }
    });

    if (cartForm) {
        cartForm.addEventListener('submit', (e) => e.preventDefault());
    }
});