document.querySelector('.menu-button')?.addEventListener('click', () => document.querySelector('.site-header nav').classList.toggle('open'));
document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => {
    if (!confirm(form.dataset.confirm)) event.preventDefault();
}));

const drawer = document.querySelector('.cart-drawer');
const backdrop = document.querySelector('.cart-backdrop');
const cartActionUrl = drawer?.dataset.cartAction || '/gamegear/actions/index.php';
const openCart = () => { drawer?.classList.add('open'); backdrop?.classList.add('open'); drawer?.setAttribute('aria-hidden','false'); document.body.classList.add('drawer-open'); };
const closeCart = () => { drawer?.classList.remove('open'); backdrop?.classList.remove('open'); drawer?.setAttribute('aria-hidden','true'); document.body.classList.remove('drawer-open'); };
document.querySelector('.cart-trigger')?.addEventListener('click', openCart);
document.querySelectorAll('[data-cart-close]').forEach(el => el.addEventListener('click', closeCart));
document.addEventListener('keydown', event => { if(event.key === 'Escape') closeCart(); });

const escapeHtml = value => String(value).replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
const renderCart = data => {
    document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = data.count);
    document.querySelector('[data-cart-total]').textContent = data.total;
    const csrf = document.querySelector('[data-cart-csrf]')?.dataset.cartCsrf || '';
    document.querySelector('[data-cart-items]').innerHTML = data.items.length ? data.items.map(item => `<article class="drawer-item"><img src="${escapeHtml(item.image_url)}" alt=""><div><strong>${escapeHtml(item.name)}</strong><small>${item.quantity} × ${item.price}</small><div class="quantity-control"><button type="button" data-cart-change="-1" data-product-id="${item.id}" data-quantity="${item.quantity}" data-max="${item.stock}" data-csrf="${escapeHtml(csrf)}" aria-label="Decrease quantity">−</button><span>${item.quantity}</span><button type="button" data-cart-change="1" data-product-id="${item.id}" data-quantity="${item.quantity}" data-max="${item.stock}" data-csrf="${escapeHtml(csrf)}" aria-label="Increase quantity">+</button></div></div></article>`).join('') : '<div class="drawer-empty"><span>⌁</span><p>Your cart is ready for an upgrade.</p></div>';
};

document.addEventListener('click', async event => {
    const button = event.target.closest('[data-cart-change]');
    if (!button) return;
    const quantity = Math.max(0, Math.min(Number(button.dataset.max), Number(button.dataset.quantity) + Number(button.dataset.cartChange)));
    button.disabled = true;
    const formData = new FormData();
    formData.set('csrf', button.dataset.csrf); formData.set('action', 'update_cart'); formData.set(`quantities[${button.dataset.productId}]`, quantity);
    try {
        const response = await fetch(cartActionUrl, {method:'POST', body:formData, headers:{'X-Requested-With':'XMLHttpRequest'}});
        const data = await response.json(); if (!response.ok) throw new Error(data.message || 'Could not update your cart.');
        renderCart(data);
    } catch (error) { alert(error.message); button.disabled = false; }
});

document.querySelectorAll('[data-quantity-change]').forEach(button => button.addEventListener('click', () => {
    const input = button.parentElement.querySelector('input');
    input.value = Math.max(Number(input.min), Math.min(Number(input.max), Number(input.value) + Number(button.dataset.quantityChange)));
}));
document.querySelectorAll('[data-remove-cart]').forEach(button => button.addEventListener('click', () => {
    const form = button.closest('form');
    const input = button.closest('.cart-item')?.querySelector('input[type="number"]');
    if (!form || !input) return;
    input.value = 0;
    form.requestSubmit();
}));

document.querySelectorAll('.quick-add, .ajax-cart-form').forEach(form => form.addEventListener('submit', async event => {
    event.preventDefault();
    const button = form.querySelector('button'); const original = button.textContent; button.disabled = true; button.textContent = '…';
    try {
        const endpoint = form.getAttribute('action') || cartActionUrl;
        const response = await fetch(endpoint, {method:'POST', body:new FormData(form), headers:{'X-Requested-With':'XMLHttpRequest'}});
        const responseText = await response.text();
        let data;
        try { data = JSON.parse(responseText); } catch { throw new Error(`Server returned an invalid response (HTTP ${response.status}) at ${response.url}.`); }
        if(!response.ok) throw new Error(data.message || 'Could not add this item.');
        renderCart(data); openCart(); button.textContent = form.classList.contains('quick-add') ? '✓' : 'Added ✓';
        setTimeout(() => { button.textContent = original; button.disabled = false; }, 1200);
    } catch(error) { button.textContent = original; button.disabled = false; alert(error.message); }
}));

const adminSidebar = document.querySelector('.admin-sidebar');
const adminBackdrop = document.querySelector('.admin-sidebar-backdrop');
const closeAdminSidebar = () => { adminSidebar?.classList.remove('open'); adminBackdrop?.classList.remove('open'); };
document.querySelector('.admin-sidebar-toggle')?.addEventListener('click', () => {
    adminSidebar?.classList.toggle('open'); adminBackdrop?.classList.toggle('open');
});
adminBackdrop?.addEventListener('click', closeAdminSidebar);
adminSidebar?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeAdminSidebar));
