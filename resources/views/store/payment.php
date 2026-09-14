<section class="section mx-auto px-5 py-16 lg:px-10 max-w-2xl"><p class="eyebrow text-xs font-semibold uppercase tracking-[.22em] text-accent">PAYMENT UPDATE</p><h1 class="font-display [overflow-wrap:anywhere] my-5 text-4xl"><?= $result ===
'success'
    ? 'Payment response received.'
    : 'Payment was not completed.' ?></h1><p class="mb-8 text-stone-600"><?= $result === 'success'
    ? 'The provider response has been checked. Return to your private order page to see whether payment is paid or under review.'
    : 'Your order remains saved. A cancelled or failed browser redirect never marks an order as paid. Check your private order page before trying again.' ?></p><a class="btn inline-flex items-center justify-center rounded-full bg-accent px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-800 disabled:opacity-40" href="/menu">Back to menu</a></section>
