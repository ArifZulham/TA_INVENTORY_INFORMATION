/**
 * SmartSport Assistant — Frontend Search Logic
 * Menggunakan Sanctum cookie auth (stateful API) + CSRF token.
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('search-form');
    const promptInput = document.getElementById('search-prompt');
    const searchBtn = document.getElementById('search-btn');
    const searchBtnText = document.getElementById('search-btn-text');
    const searchSpinner = document.getElementById('search-spinner');
    const results = document.getElementById('results');
    const emptyState = document.getElementById('empty-state');
    const errorAlert = document.getElementById('error-alert');
    const detectedProduct = document.getElementById('detected-product');
    const statusBadge = document.getElementById('status-badge');
    const recommendationBox = document.getElementById('recommendation-box');
    const recommendationMessage = document.getElementById('recommendation-message');
    const productCards = document.getElementById('product-cards');

    if (!form) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Quick prompt chips
    document.querySelectorAll('.quick-prompt').forEach((btn) => {
        btn.addEventListener('click', () => {
            promptInput.value = btn.dataset.prompt;
            promptInput.focus();
        });
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await performSearch(promptInput.value.trim());
    });

    async function performSearch(prompt) {
        if (prompt.length < 3) return;

        setLoading(true);
        hideError();
        emptyState.classList.add('hidden');

        try {
            const response = await fetch('/api/products/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ prompt }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Terjadi kesalahan saat mencari produk.');
            }

            renderResults(data);
        } catch (err) {
            showError(err.message || 'Gagal menghubungi server. Coba lagi.');
            results.classList.add('hidden');
            emptyState.classList.remove('hidden');
        } finally {
            setLoading(false);
        }
    }

    function renderResults(data) {
        results.classList.remove('hidden');
        detectedProduct.textContent = data.detected_product;

        productCards.innerHTML = '';

        if (data.status === 'found') {
            statusBadge.textContent = 'Tersedia';
            statusBadge.className = 'rounded-lg px-3 py-1 text-xs font-semibold bg-emerald-500/20 text-emerald-400';
            recommendationBox.classList.add('hidden');
            productCards.appendChild(createProductCard(data.product, true));
        } else {
            statusBadge.textContent = data.reason === 'out_of_stock' ? 'Stok Habis' : 'Tidak Ditemukan';
            statusBadge.className = 'rounded-lg px-3 py-1 text-xs font-semibold bg-amber-500/20 text-amber-400';
            recommendationBox.classList.remove('hidden');
            recommendationMessage.textContent = data.message;

            if (data.original_product) {
                productCards.appendChild(createProductCard(data.original_product, false));
            }

            (data.alternatives || []).forEach((product) => {
                productCards.appendChild(createProductCard(product, true, true));
            });
        }
    }

    function createProductCard(product, inStock, isAlternative = false) {
        const card = document.createElement('div');
        card.className = 'rounded-2xl border border-slate-800 bg-slate-900 p-5 transition hover:border-slate-700';

        const tags = (product.spec_tags || [])
            .map((tag) => `<span class="rounded-full bg-slate-800 px-2 py-0.5 text-xs text-slate-400">${tag}</span>`)
            .join(' ');

        const items = (product.available_items || [])
            .map((item) => `
                <li class="flex justify-between text-xs text-slate-400">
                    <span>${item.size || '-'} / ${item.color || '-'}</span>
                    <span class="text-emerald-400">${item.stock_quantity} stok</span>
                </li>
            `)
            .join('');

        const stockBadge = inStock
            ? `<span class="text-xs font-medium text-emerald-400">${product.total_stock} stok tersedia</span>`
            : `<span class="text-xs font-medium text-red-400">Stok habis</span>`;

        const altLabel = isAlternative
            ? `<span class="mb-2 inline-block rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs text-emerald-400">Alternatif</span>`
            : '';

        card.innerHTML = `
            ${altLabel}
            <h3 class="text-base font-semibold text-white">${product.name}</h3>
            <p class="mt-1 text-lg font-bold text-emerald-400">Rp ${formatPrice(product.base_price)}</p>
            <p class="mt-2 line-clamp-2 text-xs text-slate-400">${product.description || ''}</p>
            <div class="mt-3 flex flex-wrap gap-1">${tags}</div>
            <div class="mt-4 border-t border-slate-800 pt-3">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs text-slate-500">Varian tersedia</span>
                    ${stockBadge}
                </div>
                <ul class="space-y-1">${items || '<li class="text-xs text-slate-500">Tidak ada varian tersedia</li>'}</ul>
            </div>
        `;

        return card;
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('id-ID').format(price);
    }

    function setLoading(loading) {
        searchBtn.disabled = loading;
        searchSpinner.classList.toggle('hidden', !loading);
        searchBtnText.textContent = loading ? 'Mencari...' : 'Cari Produk';
    }

    function showError(message) {
        errorAlert.textContent = message;
        errorAlert.classList.remove('hidden');
    }

    function hideError() {
        errorAlert.classList.add('hidden');
    }
});
