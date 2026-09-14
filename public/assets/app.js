'use strict';
const bangla = document.documentElement.lang === 'bn';
const menuLabel = bangla ? 'মেনু ☰' : 'Menu ☰';
const closeLabel = bangla ? 'বন্ধ করুন ×' : 'Close ×';
const languageSelects = document.querySelectorAll('[data-language-select]');
const bindLanguageSelect = select => select?.addEventListener('change', event => {
  if (event.target.form) {
    event.target.form.requestSubmit();
    return;
  }
  const url = new URL(window.location.href);
  url.searchParams.set('lang', event.target.value);
  window.location.assign(url.href);
});
if (languageSelects.length) languageSelects.forEach(bindLanguageSelect); else bindLanguageSelect(document.querySelector('[data-language-select]'));
if (!document.body?.classList.contains('admin-body')) {
  document.querySelectorAll('a[href]').forEach(link => {
    const url = new URL(link.href, window.location.href);
    if (url.origin === window.location.origin && !url.pathname.startsWith('/assets/')) {
      url.searchParams.set('lang', document.documentElement.lang === 'bn' ? 'bn' : 'en');
      link.href = url.href;
    }
  });
}
document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', e => { if (!window.confirm(form.dataset.confirm)) e.preventDefault(); }));
document.querySelectorAll('[data-back]').forEach(button => button.addEventListener('click', () => history.length > 1 ? history.back() : location.assign('/')));
document.querySelectorAll('[data-lightbox]').forEach(link => link.addEventListener('click', e => { e.preventDefault(); const dialog = document.createElement('dialog'); dialog.className = 'max-w-5xl rounded-2xl bg-ink p-4 text-white backdrop:bg-[#101510d9] backdrop:backdrop-blur-sm'; const img = document.createElement('img'); img.src = link.href; img.alt = link.dataset.lightbox; img.className = 'max-h-[80vh] w-full object-contain'; const close = document.createElement('button'); close.textContent = closeLabel; close.className = 'btn inline-flex items-center justify-center rounded-full bg-accent px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-800 disabled:opacity-40 mt-3'; close.onclick = () => dialog.close(); dialog.append(img, close); document.body.append(dialog); dialog.addEventListener('close', () => dialog.remove()); dialog.showModal(); }));
const orderTypes = document.querySelectorAll('[name="order_type"]');
if (orderTypes.length) {
  const update = () => {
    const delivery = document.querySelector('[name="order_type"]:checked')?.value === 'delivery';
    const address = document.querySelector('[name="address"]');
    if (address) address.required = delivery;
    const addressLabel = document.querySelector('[data-delivery-address]');
    if (addressLabel) addressLabel.hidden = !delivery;
    const total = document.querySelector('[data-checkout-total]');
    const format = value => 'Tk ' + (value / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (total) {
      const fee = delivery ? Number(total.dataset.fee) : 0;
      total.textContent = format(Number(total.dataset.subtotal) + fee);
      const feeLabel = document.querySelector('[data-checkout-fee]');
      if (feeLabel) feeLabel.textContent = format(fee);
    }
  };
  orderTypes.forEach(input => input.addEventListener('change', update)); update();
}

const navToggle = document.querySelector('[data-nav-toggle]'), mobileNav = document.querySelector('[data-mobile-nav]'), navClose = document.querySelector('[data-nav-close]');
const closeMobileNav = () => { if (!mobileNav) return; mobileNav.hidden = true; document.querySelector('[data-nav-backdrop]')?.remove(); navToggle?.setAttribute('aria-expanded', 'false'); if (navToggle) navToggle.textContent = menuLabel; };
navToggle?.addEventListener('click', () => { const open = mobileNav.hidden; mobileNav.hidden = !open; if (open && document.body && document.createElement) { const backdrop = document.createElement('div'); backdrop.className = 'fixed inset-0 z-30 bg-black/50 lg:hidden'; backdrop.dataset.navBackdrop = ''; backdrop.addEventListener('click', closeMobileNav); document.body.append(backdrop); } else if (!open) document.querySelector('[data-nav-backdrop]')?.remove(); navToggle.setAttribute('aria-expanded', String(open)); navToggle.textContent = open ? closeLabel : menuLabel; });
navClose?.addEventListener('click', closeMobileNav);
document.addEventListener('keydown', event => { if (event.key === 'Escape' && mobileNav && !mobileNav.hidden) { mobileNav.hidden = true; navToggle.setAttribute('aria-expanded', 'false'); navToggle.textContent = menuLabel; navToggle.focus(); } });
if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) { const io = new IntersectionObserver(entries => entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.remove('opacity-0', 'translate-y-6'); io.unobserve(entry.target); } }), { threshold: .08 }); document.querySelectorAll('body:not(.admin-body) main section > div, [data-reveal]').forEach(element => { if (element.getBoundingClientRect().top > innerHeight * .8) { element.classList.add('opacity-0', 'translate-y-6', 'transition-[opacity,transform]', 'duration-700', 'ease-out', 'motion-reduce:opacity-100', 'motion-reduce:transform-none'); io.observe(element); } }); }
document.querySelectorAll('[data-product-gallery]').forEach(gallery => { const hero = gallery.querySelector('[data-product-main]'), zoom = gallery.querySelector('[data-product-zoom]'); gallery.querySelectorAll('[data-product-thumb]').forEach(button => button.addEventListener('click', () => { hero.src = button.dataset.productThumb; if (zoom) zoom.href = button.dataset.productThumb; gallery.querySelectorAll('[data-product-thumb]').forEach(b => b.setAttribute('aria-pressed', String(b === button))); })); });
document.querySelectorAll('[data-print]').forEach(button => button.addEventListener('click', () => window.print()));

document.querySelectorAll('[data-order-tracking]').forEach(tracking => {
  let pending = false;
  const refresh = async () => {
    if (pending || document.hidden) return;
    pending = true;
    const message = tracking.querySelector('[data-tracking-message]');
    try {
      const response = await fetch(tracking.dataset.statusUrl, { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' }, signal: AbortSignal.timeout(12000) });
      if (!response.ok) throw new Error('Status unavailable');
      const data = await response.json();
      tracking.querySelector('[data-order-status]').textContent = data.statusLabel;
      tracking.querySelector('[data-payment-status]').textContent = data.paymentLabel;
      tracking.querySelector('[data-order-cancelled]').hidden = data.status !== 'Cancelled';
      tracking.querySelectorAll('[data-order-step]').forEach(step => {
        const current = Number(step.dataset.orderStep);
        step.dataset.done = String(data.stepIndex !== false && current <= data.stepIndex);
        if (current === data.stepIndex) step.setAttribute('aria-current', 'step'); else step.removeAttribute('aria-current');
        const event = data.events.findLast(event => event.status === step.dataset.stepStatus);
        step.querySelector('[data-step-time]').textContent = event?.created_at.slice(11, 16) || '';
      });
      const pay = tracking.querySelector('[data-order-pay]');
      if (pay) pay.hidden = data.paymentStatus !== 'unpaid' || data.status === 'Cancelled';
      message.textContent = data.checkedLabel;
    } catch (error) { message.textContent = bangla ? 'আপডেট পাওয়া যায়নি। আবার চেষ্টা করা হচ্ছে; চাইলে রিফ্রেশ করুন।' : 'Unable to update. Retrying; you can also refresh this page.'; }
    finally { pending = false; }
  };
  setInterval(refresh, 15000);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
});
