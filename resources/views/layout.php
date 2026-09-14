<?php
$isAdmin=$admin??false;$notice=$_SESSION['flash']??null;unset($_SESSION['flash']);
$brand='দেশি ভোজ';try{$brand=setting('brand.name','দেশি ভোজ');}catch(Throwable){}
$nav=['/'=>'home','/menu'=>'menu','/about'=>'about','/gallery'=>'gallery','/contact'=>'contact'];
?>
<!doctype html><html lang="<?= $isAdmin?'en':App\Services\Locale::current(); ?>" translate="no" class="notranslate"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="google" content="notranslate">
<title><?= e(($page??'')==='home'?setting('seo.title',$brand):plain($title??$brand).' · '.$brand); ?></title>
<meta name="description" content="<?= e($isAdmin?'রেস্টুরেন্ট ব্যবস্থাপনা':plain(($page??'')==='home'?setting('seo.description','বাংলাদেশি খাবার'):$title??'Restaurant')); ?>">
<?php if($isAdmin||in_array($page??'',['cart','checkout','track','order'])): ?><meta name="robots" content="noindex,nofollow"><?php endif; ?>
<?php if($isAdmin): ?><link rel="stylesheet" href="/assets/quill.snow.css"><?php endif; ?>
<link rel="stylesheet" href="/assets/app.css"><script src="/assets/app.js" defer></script>
<?php if($isAdmin): ?><script src="/assets/quill.js" defer></script><script src="/assets/admin.js" defer></script><?php endif; ?>

<style>
/* Mobile menu only — desktop/laptop layout is left unchanged. */
@media (max-width: 1023.98px) {
    .site-mobile-menu[hidden] { display: none !important; }

    .site-mobile-menu {
        position: fixed;
        inset: 0;
        z-index: 70;
    }

    .site-mobile-menu__backdrop {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0;
        padding: 0;
        background: rgba(7, 18, 12, .70);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        animation: siteMenuFadeIn .22s ease both;
    }

    .site-mobile-menu__panel {
        position: absolute;
        top: 0;
        right: 0;
        display: flex;
        width: min(88vw, 390px);
        height: 100dvh;
        flex-direction: column;
        overflow: hidden;
        color: #f7f2e8;
        background:
            radial-gradient(circle at 105% -5%, rgba(216, 198, 162, .14), transparent 34%),
            linear-gradient(180deg, #153e2e 0%, #123426 58%, #0d241a 100%);
        border-left: 1px solid rgba(216, 198, 162, .20);
        box-shadow: -24px 0 60px rgba(0, 0, 0, .30);
        animation: siteMenuSlideIn .28s cubic-bezier(.22, .8, .24, 1) both;
    }

    .site-mobile-menu__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.35rem 1.35rem 1.2rem;
        border-bottom: 1px solid rgba(216, 198, 162, .17);
    }

    .site-mobile-menu__brand {
        margin: 0 0 .35rem;
        color: #d8c6a2;
        font-size: .68rem;
        font-weight: 700;
        line-height: 1;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .site-mobile-menu__title {
        margin: 0;
        color: #fffaf0;
        font-size: 1.8rem;
        line-height: 1.05;
    }

    .site-mobile-menu__subtitle {
        margin: .45rem 0 0;
        color: rgba(255, 250, 240, .58);
        font-size: .78rem;
        line-height: 1.5;
    }

    .site-mobile-menu__close {
        display: inline-flex;
        width: 2.75rem;
        height: 2.75rem;
        flex: 0 0 2.75rem;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(216, 198, 162, .30);
        border-radius: 999px;
        background: rgba(255, 255, 255, .045);
        color: #fffaf0;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
    }

    .site-mobile-menu__close:hover,
    .site-mobile-menu__close:focus-visible {
        border-color: #d8c6a2;
        background: #d8c6a2;
        color: #153e2e;
    }

    .site-mobile-menu__body {
        flex: 1;
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: .65rem 1.35rem 1.4rem;
    }

    .site-mobile-menu__nav {
        display: grid;
    }

    .site-mobile-menu__link {
        position: relative;
        display: flex;
        min-height: 3.55rem;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-bottom: 1px solid rgba(216, 198, 162, .14);
        color: rgba(255, 250, 240, .86);
        text-decoration: none;
        font-size: 1rem;
        font-weight: 600;
        transition: color .18s ease, padding-left .18s ease, background-color .18s ease;
    }

    .site-mobile-menu__link::after {
        content: "→";
        color: rgba(216, 198, 162, .55);
        font-size: 1rem;
        transition: transform .18s ease, color .18s ease;
    }

    .site-mobile-menu__link:hover,
    .site-mobile-menu__link:focus-visible,
    .site-mobile-menu__link[aria-current="page"] {
        color: #d8c6a2;
        padding-left: .35rem;
    }

    .site-mobile-menu__link:hover::after,
    .site-mobile-menu__link:focus-visible::after,
    .site-mobile-menu__link[aria-current="page"]::after {
        color: #d8c6a2;
        transform: translateX(.2rem);
    }

    .site-mobile-menu__section-label {
        margin: 1.35rem 0 .55rem;
        color: #d8c6a2;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .site-mobile-menu__language {
        padding: .9rem;
        border: 1px solid rgba(216, 198, 162, .18);
        border-radius: 1rem;
        background: rgba(255, 255, 255, .045);
    }

    .site-mobile-menu__language select,
    .site-mobile-menu__language button,
    .site-mobile-menu__language a {
        max-width: 100%;
    }

    .site-mobile-menu__language select {
        width: 100%;
        border: 1px solid rgba(216, 198, 162, .28);
        border-radius: 999px;
        background: #fffaf0;
        color: #153e2e;
        padding: .72rem .9rem;
    }

    .site-mobile-menu__footer {
        padding: 1rem 1.35rem 1.35rem;
        border-top: 1px solid rgba(216, 198, 162, .16);
        background: rgba(5, 15, 10, .20);
    }

    .site-mobile-menu__track {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: center;
        margin-bottom: .7rem;
        padding: .9rem 1rem;
        border: 1px solid rgba(216, 198, 162, .32);
        border-radius: 999px;
        color: #fffaf0;
        font-size: .88rem;
        font-weight: 600;
        text-decoration: none;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
    }

    .site-mobile-menu__track:hover,
    .site-mobile-menu__track:focus-visible {
        border-color: #d8c6a2;
        background: rgba(216, 198, 162, .10);
        color: #d8c6a2;
    }

    .site-mobile-menu__booking {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: center;
        padding: .95rem 1rem;
        border-radius: 999px;
        color: #fff;
        font-size: .92rem;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 10px 24px rgba(0, 0, 0, .16);
        transition: transform .18s ease, filter .18s ease;
    }

    .site-mobile-menu__booking:hover,
    .site-mobile-menu__booking:focus-visible {
        transform: translateY(-1px);
        filter: brightness(.94);
    }

    .site-mobile-menu-trigger {
        display: inline-flex;
        width: 2.75rem;
        height: 2.75rem;
        flex: 0 0 2.75rem;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
    }

    @keyframes siteMenuFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes siteMenuSlideIn {
        from { transform: translateX(100%); }
        to { transform: translateX(0); }
    }
}
</style>
</head><body class="[&_.ql-color-red]:text-[#c23935] [&_.ql-color-gold]:text-[#ac853b] [&_.ql-color-green]:text-[#398047] [&_.ql-color-blue]:text-[#2563eb] [&_.ql-color-purple]:text-[#7c3aed] [&_.ql-color-white]:text-[#fff] [&_.ql-color-black]:text-[#111] font-sans text-ink antialiased [&_a]:focus-visible:outline [&_button]:focus-visible:outline [&_input]:focus-visible:outline [&_select]:focus-visible:outline [&_textarea]:focus-visible:outline [&_:focus-visible]:outline-2 [&_:focus-visible]:outline-offset-4 [&_:focus-visible]:outline-accent motion-reduce:[&_*]:!transition-none motion-reduce:[&_*]:!animate-none motion-reduce:[&_*]:!scroll-auto print:!bg-white print:!text-black print:[&_header]:!hidden print:[&_footer]:!hidden print:[&_aside]:!hidden print:[&_main]:!p-0 <?= $isAdmin?'admin-body bg-stone-100':'bg-paper'; ?>">
<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:bg-white focus:p-4">Skip to content</a>
<?php if($isAdmin): ?>
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
<aside class="bg-ink p-5 text-paper lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto">
<a href="/" class="block border-b border-white/10 pb-6 font-display text-4xl"><?= e($brand); ?><span class="mt-2 block font-sans text-xs tracking-[.2em] text-stone-400">RESTAURANT STUDIO</span></a>
<nav aria-label="Admin navigation" class="mt-5 grid grid-cols-2 gap-1 lg:block">
<?php foreach(['dashboard'=>'Overview','products'=>'Menu products','categories'=>'Categories','orders'=>'Orders','payments'=>'Payments','reservations'=>'Reservations','messages'=>'Inbox','customers'=>'Guests','media'=>'Image library','features'=>'Our promises','gallery'=>'Gallery','reviews'=>'Guest reviews','faqs'=>'FAQs','settings'=>'Website settings','team'=>'Your team','security'=>'Account security'] as $key=>$label):if(in_array($key,['settings','team'])&&user()['role']!=='owner')continue; ?>
<a class="admin-link block rounded-lg px-4 py-2 text-sm text-stone-300 hover:bg-white/10 hover:text-white <?= ($section??'')===$key?'bg-white/10 text-white':''; ?>" <?= ($section??'')===$key?'aria-current="page"':''; ?> href="/control-center/<?= e($key); ?>"><?= e($label); ?></a>
<?php endforeach; ?></nav>
<form action="/logout" method="post" class="mt-5 border-t border-white/10 pt-4"><?= csrf(); ?><button class="admin-link block rounded-lg px-4 py-2 text-sm text-stone-300 hover:bg-white/10 hover:text-white">Sign out →</button></form>
</aside><div class="min-w-0"><header class="flex items-center justify-between gap-4 border-b bg-white px-6 py-6"><div><p class="eyebrow text-xs font-semibold uppercase tracking-[.22em] text-accent">Your restaurant, in one place</p><h1 class="font-display [overflow-wrap:anywhere] mt-2 text-3xl"><?= e($title); ?></h1></div><div class="text-right"><p class="text-sm font-semibold"><?= e(user()['name']); ?></p><p class="text-xs capitalize text-stone-500"><?= e(user()['role']); ?></p><a href="/" class="mt-2 inline-block text-sm text-accent" target="_blank" rel="noopener">Visit website ↗</a></div></header><main id="main-content" class="p-5 lg:p-8">
<?php else: ?>
<div class="bg-[#d8c6a2] px-4 py-2 text-center text-xs text-ink rich-inline [&_.ql-color-red]:text-[#c23935] [&_.ql-color-gold]:text-[#ac853b] [&_.ql-color-green]:text-[#398047] [&_.ql-color-blue]:text-[#2563eb] [&_.ql-color-purple]:text-[#7c3aed] [&_.ql-color-white]:text-[#fff] [&_.ql-color-black]:text-[#111] [&_strong]:font-bold [&_em]:italic [&_p]:[display:inline] [&_p+p]:ml-1"><?= rich_inline(setting('announcement','আপনার পছন্দের দেশি খাবার')); ?></div>
<header class="site-header sticky top-0 z-40 border-b border-white/10 bg-[#153e2e] text-paper backdrop-blur-lg">
<div class="mx-auto flex max-w-[1440px] flex-nowrap items-center justify-between gap-3 px-4 py-4 sm:gap-4 sm:px-8 xl:flex-nowrap lg:px-10">
<a href="/" class="min-w-0 shrink font-display text-3xl sm:text-4xl" aria-label="<?= e($brand); ?> home"><?php if(setting('brand.logo','')): ?><img class="h-12 max-w-40 object-contain" src="<?= e(safeImage(setting('brand.logo'))); ?>" alt="<?= e($brand); ?>"><?php else: ?><?= e($brand); ?><span class="rich-inline [&_.ql-color-red]:text-[#c23935] [&_.ql-color-gold]:text-[#ac853b] [&_.ql-color-green]:text-[#398047] [&_.ql-color-blue]:text-[#2563eb] [&_.ql-color-purple]:text-[#7c3aed] [&_.ql-color-white]:text-[#fff] [&_.ql-color-black]:text-[#111] [&_strong]:font-bold [&_em]:italic [&_p]:[display:inline] [&_p+p]:ml-1 mt-1 block font-sans text-xs tracking-[.22em]"><?= rich_inline(setting('brand.tagline','বাংলার স্বাদ')); ?></span><?php endif; ?></a>
<nav aria-label="Main navigation" class="hidden items-center gap-6 text-sm lg:flex"><?php foreach($nav as $href=>$key): ?><a class="transition-colors hover:text-[#d8c6a2]" href="<?= e($href); ?>"><?= e(setting('navigation.'.$key,ucfirst($key))); ?></a><?php endforeach; ?></nav>
<div class="public-header-actions flex shrink-0 items-center justify-end gap-2 sm:gap-3">
<div class="hidden lg:block"><?php require __DIR__.'/store/language-selector.php'; ?></div>
<a class="hidden rounded-full border border-white/30 px-4 py-3 text-sm lg:inline-block" href="/track-order">অর্ডার ট্র্যাক করুন</a>
<a class="rounded-full border border-white/30 px-3 py-3 text-sm sm:px-4" href="/cart">কার্ট (<?= array_sum($_SESSION['cart']??[]); ?>)</a>
<a class="btn items-center justify-center rounded-full bg-accent px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-800 disabled:opacity-40 hidden xl:inline-flex" href="/contact#reserve">টেবিল বুকিং</a>
<button type="button" class="site-mobile-menu-trigger rounded-lg border border-white/30 text-paper lg:hidden" data-site-menu-open aria-controls="mobile-navigation" aria-expanded="false" aria-label="মেনু খুলুন">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
</button>
</div>
</div>

<div id="mobile-navigation" class="site-mobile-menu lg:hidden" aria-hidden="true" hidden>
<button type="button" class="site-mobile-menu__backdrop" data-site-menu-close aria-label="মেনু বন্ধ করুন"></button>
<aside class="site-mobile-menu__panel" role="dialog" aria-modal="true" aria-labelledby="mobile-menu-title">
<div class="site-mobile-menu__top">
<div>
<p class="site-mobile-menu__brand"><?= e($brand); ?></p>
<h2 id="mobile-menu-title" class="site-mobile-menu__title font-display">মেনু</h2>
<p class="site-mobile-menu__subtitle">আমাদের খাবার, গল্প ও প্রয়োজনীয় তথ্য</p>
</div>
<button type="button" class="site-mobile-menu__close" data-site-menu-close aria-label="মেনু বন্ধ করুন">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
</button>
</div>

<div class="site-mobile-menu__body">
<nav class="site-mobile-menu__nav" aria-label="Mobile navigation links">
<?php foreach($nav as $href=>$key): ?>
<a class="site-mobile-menu__link" href="<?= e($href); ?>" <?= ($page??'')===$key?'aria-current="page"':''; ?>><?= e(setting('navigation.'.$key,ucfirst($key))); ?></a>
<?php endforeach; ?>
</nav>

<p class="site-mobile-menu__section-label">ভাষা নির্বাচন</p>
<div class="site-mobile-menu__language">
<?php $languageId='mobile-site-language'; require __DIR__.'/store/language-selector.php'; ?>
</div>
</div>

<div class="site-mobile-menu__footer">
<a class="site-mobile-menu__track" href="/track-order">অর্ডার ট্র্যাক করুন</a>
<a class="site-mobile-menu__booking bg-accent hover:bg-orange-800" href="/contact#reserve">টেবিল বুকিং</a>
</div>
</aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const menu = document.getElementById('mobile-navigation');
    const openButton = document.querySelector('[data-site-menu-open]');
    const closeButtons = document.querySelectorAll('[data-site-menu-close]');
    const mobileQuery = window.matchMedia('(max-width: 1023.98px)');
    let lastFocused = null;

    if (!menu || !openButton) return;

    const closeMenu = (restoreFocus = true) => {
        menu.hidden = true;
        menu.setAttribute('aria-hidden', 'true');
        openButton.setAttribute('aria-expanded', 'false');
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
        if (restoreFocus && lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    };

    const openMenu = () => {
        if (!mobileQuery.matches) return;
        lastFocused = document.activeElement;
        menu.hidden = false;
        menu.setAttribute('aria-hidden', 'false');
        openButton.setAttribute('aria-expanded', 'true');
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
        const closeButton = menu.querySelector('.site-mobile-menu__close');
        if (closeButton) window.setTimeout(() => closeButton.focus(), 30);
    };

    openButton.addEventListener('click', openMenu);
    closeButtons.forEach((button) => button.addEventListener('click', () => closeMenu(true)));
    menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => closeMenu(false)));

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !menu.hidden) closeMenu(true);
    });

    const handleBreakpoint = () => {
        if (!mobileQuery.matches && !menu.hidden) closeMenu(false);
    };

    if (mobileQuery.addEventListener) mobileQuery.addEventListener('change', handleBreakpoint);
    else mobileQuery.addListener(handleBreakpoint);
});
</script>
</header>
<main id="main-content">
<?php endif; ?>
<?php if($notice): ?><div role="status" class="mx-auto my-5 max-w-6xl rounded-xl border border-green-300 bg-green-50 p-4 text-green-900"><?= e($notice); ?></div><?php endif; ?>
<?php require $viewFile; ?>
</main><?php if($isAdmin): ?></div></div><?php else: ?>
<footer class="bg-[#0d120d] text-paper"><div class="mx-auto grid max-w-[1440px] gap-10 px-5 py-16 sm:grid-cols-2 sm:px-8 lg:grid-cols-4 lg:px-14">
<div><a href="/" class="font-display text-4xl"><?= e($brand); ?></a><div class="rich-content [&_.ql-color-red]:text-[#c23935] [&_.ql-color-gold]:text-[#ac853b] [&_.ql-color-green]:text-[#398047] [&_.ql-color-blue]:text-[#2563eb] [&_.ql-color-purple]:text-[#7c3aed] [&_.ql-color-white]:text-[#fff] [&_.ql-color-black]:text-[#111] [&_p_+_p]:mt-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_blockquote]:my-4 [&_blockquote]:border-l-2 [&_blockquote]:border-accent [&_blockquote]:pl-4 [&_blockquote]:italic [&_strong]:font-bold [&_em]:italic mt-5 text-sm text-white/60"><?= rich(setting('footer.description')); ?></div><div class="mt-4 flex gap-4 text-sm"><?php foreach(['instagram','facebook'] as $social):$url=setting('social.'.$social,'');if($url): ?><a href="<?= e($url); ?>" rel="noopener noreferrer" target="_blank"><?= ucfirst($social); ?></a><?php endif; endforeach; ?></div></div>
<div><h2 class="[overflow-wrap:anywhere] mb-5 font-sans text-sm uppercase tracking-widest text-[#d8c6a2]">ঘুরে দেখুন</h2><nav class="grid gap-3 text-sm"><a href="/menu">আমাদের মেনু</a><a href="/about">আমাদের গল্প</a><a href="/gallery">গ্যালারি</a><a href="/contact#reserve">টেবিল বুকিং</a></nav></div>
<div><h2 class="[overflow-wrap:anywhere] mb-5 font-sans text-sm uppercase tracking-widest text-[#d8c6a2]">প্রয়োজনীয় তথ্য</h2><nav class="grid gap-3 text-sm"><a href="/faq">সাধারণ প্রশ্ন</a><a href="/delivery">পিকআপ ও ডেলিভারি</a><a href="/privacy">গোপনীয়তা</a><a href="/terms">শর্তাবলি</a></nav></div>
<div><h2 class="[overflow-wrap:anywhere] mb-5 font-sans text-sm uppercase tracking-widest text-[#d8c6a2]">আমাদের ঠিকানা</h2><div class="rich-content [&_.ql-color-red]:text-[#c23935] [&_.ql-color-gold]:text-[#ac853b] [&_.ql-color-green]:text-[#398047] [&_.ql-color-blue]:text-[#2563eb] [&_.ql-color-purple]:text-[#7c3aed] [&_.ql-color-white]:text-[#fff] [&_.ql-color-black]:text-[#111] [&_p_+_p]:mt-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_blockquote]:my-4 [&_blockquote]:border-l-2 [&_blockquote]:border-accent [&_blockquote]:pl-4 [&_blockquote]:italic [&_strong]:font-bold [&_em]:italic text-sm text-white/60"><?= rich(setting('contact.address')); ?></div><p class="mt-4 text-sm"><?= e(setting('contact.phone')); ?></p><div class="rich-content [&_.ql-color-red]:text-[#c23935] [&_.ql-color-gold]:text-[#ac853b] [&_.ql-color-green]:text-[#398047] [&_.ql-color-blue]:text-[#2563eb] [&_.ql-color-purple]:text-[#7c3aed] [&_.ql-color-white]:text-[#fff] [&_.ql-color-black]:text-[#111] [&_p_+_p]:mt-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_blockquote]:my-4 [&_blockquote]:border-l-2 [&_blockquote]:border-accent [&_blockquote]:pl-4 [&_blockquote]:italic [&_strong]:font-bold [&_em]:italic mt-3 text-sm text-white/60"><?= rich(setting('contact.hours')); ?></div></div>
</div><div class="mx-auto flex max-w-[1440px] flex-wrap justify-between gap-4 border-t border-white/10 px-5 py-6 text-xs text-white/40 sm:px-8 lg:px-14"><p>© <?= date('Y'); ?> <?= e($brand); ?>. সর্বস্বত্ব সংরক্ষিত।</p><a href="/control-center">রেস্টুরেন্ট ব্যবস্থাপনা</a></div></footer>
<?php endif; ?></body></html>
