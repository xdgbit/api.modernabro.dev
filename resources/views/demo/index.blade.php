<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'سایت من'))</title>
    <meta name="description" content="@yield('description', 'صفحه اصلی سایت')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --sky-50:  #f0f9ff;
            --sky-100: #e0f3fe;
            --sky-200: #bde5fb;
            --sky-400: #5cbcf0;
            --sky-500: #2a9fe3;
            --sky-700: #0f6aa8;
            --ink:     #0a2a43;
            --ink-soft:#35546b;
            --sun:     #ffc94d;
            --white:   #ffffff;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            background: var(--sky-50);
            color: var(--ink);
            line-height: 1.9;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; text-decoration: none; }
        :focus-visible { outline: 3px solid var(--sun); outline-offset: 3px; border-radius: 6px; }

        .container { width: min(1120px, 100% - 2.5rem); margin-inline: auto; }

        /* ---------- Header ---------- */
        .header {
            position: absolute; inset: 0 0 auto 0; z-index: 10;
            padding-block: 1.25rem;
        }
        .header .container { display: flex; align-items: center; justify-content: space-between; }
        .brand { font-weight: 900; font-size: 1.4rem; color: var(--white); display: flex; align-items: center; gap: .6rem; }
        .brand-dot { width: 14px; height: 14px; border-radius: 50%; background: var(--sun); box-shadow: 0 0 0 5px rgba(255, 201, 77, .3); }
        .nav { display: flex; gap: 2rem; color: var(--white); font-weight: 500; }
        .nav a { opacity: .9; transition: opacity .2s; }
        .nav a:hover { opacity: 1; }
        .header-cta {
            background: var(--white); color: var(--sky-700);
            padding: .5rem 1.3rem; border-radius: 999px; font-weight: 700;
            transition: transform .2s, box-shadow .2s;
        }
        .header-cta:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(10, 42, 67, .18); }

        /* ---------- Hero ---------- */
        .hero {
            position: relative; overflow: hidden;
            background: linear-gradient(180deg, var(--sky-500) 0%, var(--sky-400) 55%, var(--sky-200) 100%);
            color: var(--white);
            padding: 10rem 0 11rem;
        }
        .hero .container { position: relative; z-index: 3; }
        .hero h1 {
            font-size: clamp(2.2rem, 5.5vw, 4.2rem);
            font-weight: 900; line-height: 1.45; max-width: 15ch;
            text-wrap: balance;
        }
        .hero p {
            margin-top: 1.5rem; max-width: 46ch;
            font-size: 1.15rem; color: rgba(255, 255, 255, .92);
        }
        .hero-actions { margin-top: 2.5rem; display: flex; gap: 1rem; flex-wrap: wrap; }

        .btn {
            display: inline-block; padding: .9rem 2rem; border-radius: 14px;
            font-weight: 700; font-size: 1rem; transition: transform .2s, box-shadow .2s, background .2s;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary { background: var(--ink); color: var(--white); box-shadow: 0 10px 24px rgba(10, 42, 67, .28); }
        .btn-primary:hover { box-shadow: 0 14px 30px rgba(10, 42, 67, .36); }
        .btn-ghost { background: rgba(255, 255, 255, .18); color: var(--white); border: 1.5px solid rgba(255, 255, 255, .6); }
        .btn-ghost:hover { background: rgba(255, 255, 255, .3); }

        /* sun + clouds */
        .sun {
            position: absolute; z-index: 1; top: 7rem; left: 9%;
            width: clamp(110px, 16vw, 210px); aspect-ratio: 1; border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #fff3c4, var(--sun) 70%);
            box-shadow: 0 0 0 28px rgba(255, 201, 77, .18), 0 0 0 64px rgba(255, 201, 77, .09);
        }
        .cloud { position: absolute; z-index: 2; fill: var(--white); opacity: .92; }
        .cloud-1 { top: 9rem;  right: 4%;  width: 230px; animation: drift 38s ease-in-out infinite alternate; }
        .cloud-2 { top: 20rem; left: 28%;  width: 150px; opacity: .7; animation: drift 52s ease-in-out infinite alternate-reverse; }
        .cloud-3 { top: 5.5rem; left: 46%; width: 110px; opacity: .55; animation: drift 44s ease-in-out infinite alternate; }

        @keyframes drift {
            from { transform: translateX(-40px); }
            to   { transform: translateX(40px); }
        }

        .horizon { position: absolute; z-index: 2; bottom: -1px; inset-inline: 0; width: 100%; height: auto; display: block; }

        /* ---------- Sections ---------- */
        section { padding-block: 5.5rem; }
        .section-title { font-size: clamp(1.7rem, 3.4vw, 2.4rem); font-weight: 900; line-height: 1.5; max-width: 22ch; }
        .section-lead { margin-top: .75rem; color: var(--ink-soft); max-width: 52ch; }

        /* Features */
        .features { margin-top: 3rem; display: grid; gap: 1.25rem; grid-template-columns: repeat(6, 1fr); }
        .feature { padding: 2rem; position: relative; overflow: hidden; }
        .feature h3 { font-size: 1.25rem; font-weight: 700; margin-bottom: .5rem; }
        .feature p { color: var(--ink-soft); font-size: .98rem; }

        .f-main  { grid-column: span 4; background: var(--ink); color: var(--white); border-radius: 32px; min-height: 280px; display: flex; flex-direction: column; justify-content: flex-end; }
        .f-main p { color: var(--sky-200); max-width: 40ch; }
        .f-main::before {
            content: ""; position: absolute; top: -70px; left: -70px; width: 260px; height: 260px;
            border-radius: 50%; background: radial-gradient(circle, var(--sky-500), transparent 70%); opacity: .7;
        }
        .f-side  { grid-column: span 2; background: var(--sky-200); border-radius: 32px 32px 32px 8px; }
        .f-wide  { grid-column: span 3; background: var(--white); border-radius: 8px 32px 32px 32px; border: 1.5px solid var(--sky-200); }
        .f-wide2 { grid-column: span 3; background: var(--sky-100); border-radius: 32px 8px 32px 32px; }

        /* Steps */
        .steps-wrap { background: var(--white); }
        .steps { margin-top: 3rem; display: grid; gap: 2.5rem; grid-template-columns: repeat(3, 1fr); counter-reset: step; }
        .step { position: relative; padding-top: 4.2rem; }
        .step::before {
            counter-increment: step; content: counter(step, persian);
            position: absolute; top: 0; right: 0;
            width: 3.2rem; height: 3.2rem; display: grid; place-items: center;
            border-radius: 50%; background: var(--sky-500); color: var(--white); font-weight: 900; font-size: 1.3rem;
        }
        .step:not(:last-child)::after {
            content: ""; position: absolute; top: 1.6rem; right: 4rem; left: -1.5rem;
            border-top: 2px dashed var(--sky-200);
        }
        .step h3 { font-size: 1.15rem; font-weight: 700; }
        .step p { margin-top: .4rem; color: var(--ink-soft); }

        /* CTA */
        .cta {
            background: linear-gradient(135deg, var(--sky-500), var(--sky-700));
            color: var(--white); border-radius: 40px; padding: 4rem 2.5rem;
            display: flex; align-items: center; justify-content: space-between; gap: 2rem; flex-wrap: wrap;
        }
        .cta h2 { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 900; line-height: 1.5; max-width: 20ch; }
        .cta .btn { background: var(--sun); color: var(--ink); }
        .cta .btn:hover { box-shadow: 0 12px 26px rgba(10, 42, 67, .25); }

        /* Footer */
        .footer { padding-block: 2.5rem 3rem; color: var(--ink-soft); font-size: .95rem; }
        .footer .container { display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        .footer-links { display: flex; gap: 1.5rem; }
        .footer-links a:hover { color: var(--sky-700); }

        /* ---------- Products ---------- */
        .products {
            margin-top: 3rem; display: grid; gap: 1.5rem;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        }
        .product {
            background: var(--white); border: 1.5px solid var(--sky-200); border-radius: 24px;
            overflow: hidden; display: flex; flex-direction: column;
            transition: transform .2s, box-shadow .2s;
        }
        .product:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(15, 106, 168, .14); }
        .product-img {
            position: relative;
            aspect-ratio: 4 / 3; background: var(--sky-100); display: grid; place-items: center;
            color: var(--sky-400); font-size: 2.5rem; overflow: hidden;
        }
        .product-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .product-body { padding: 1.1rem 1.25rem 1.4rem; display: flex; flex-direction: column; gap: .3rem; flex: 1; }
        .product-name { font-size: 1.05rem; font-weight: 700; line-height: 1.7; }
        .product-desc {
            color: var(--ink-soft); font-size: .92rem; line-height: 1.8;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .product-badge {
            position: absolute; top: .6rem; right: .6rem; z-index: 1;
            background: #e0453a; color: var(--white);
            font-weight: 900; font-size: .8rem; line-height: 1;
            padding: .35rem .65rem; border-radius: 999px;
        }
        .product-meta { font-size: .8rem; color: var(--sky-700); font-weight: 500; }
        .product-price-row {
            margin-top: auto; padding-top: .6rem;
            display: flex; align-items: baseline; gap: .5rem; flex-wrap: wrap;
        }
        .product-price { margin: 0; padding: 0; font-weight: 900; color: var(--sky-700); }
        .product-old-price { color: var(--ink-soft); font-size: .85rem; }
        .btn-add:disabled { background: #94a3b8; cursor: not-allowed; }

        .product.skeleton { pointer-events: none; }
        .skeleton .product-img, .skeleton .line {
            background: linear-gradient(90deg, var(--sky-100) 25%, var(--sky-50) 50%, var(--sky-100) 75%);
            background-size: 200% 100%; animation: shimmer 1.4s linear infinite;
        }
        .skeleton .line { height: 14px; border-radius: 8px; margin-top: .6rem; }
        .skeleton .line.short { width: 55%; }
        @keyframes shimmer { to { background-position: -200% 0; } }

        .products-message {
            grid-column: 1 / -1; text-align: center; padding: 3rem 1rem;
            background: var(--white); border: 1.5px dashed var(--sky-200); border-radius: 24px; color: var(--ink-soft);
        }
        .products-message .btn { margin-top: 1rem; background: var(--sky-500); color: var(--white); border: 0; cursor: pointer; font-family: inherit; }

        /* ---------- Cart ---------- */
        .header-actions { display: flex; align-items: center; gap: .75rem; }
        .cart-btn {
            position: relative; width: 44px; height: 44px; display: grid; place-items: center;
            border-radius: 50%; border: 1.5px solid rgba(255, 255, 255, .6);
            background: rgba(255, 255, 255, .18); color: var(--white); cursor: pointer;
            transition: background .2s, transform .2s;
        }
        .cart-btn:hover { background: rgba(255, 255, 255, .3); transform: translateY(-2px); }
        .cart-count {
            position: absolute; top: -6px; left: -6px; min-width: 22px; height: 22px; padding: 0 5px;
            display: grid; place-items: center; border-radius: 999px;
            background: var(--sun); color: var(--ink); font-size: .78rem; font-weight: 900; line-height: 1;
        }
        .cart-count[hidden] { display: none; }
        .cart-count.bump { animation: bump .35s ease; }
        @keyframes bump { 40% { transform: scale(1.35); } }

        .btn-add {
            margin-top: .8rem; width: 100%; padding: .65rem 1rem; border: 0; border-radius: 12px;
            background: var(--sky-500); color: var(--white); font-family: inherit; font-weight: 700; font-size: .95rem;
            cursor: pointer; transition: background .2s, transform .2s;
        }
        .btn-add:hover { background: var(--sky-700); }
        .btn-add:active { transform: scale(.98); }
        .btn-add.added { background: var(--ink); }

        .cart-overlay {
            position: fixed; inset: 0; z-index: 90; background: rgba(10, 42, 67, .45);
            opacity: 0; visibility: hidden; transition: opacity .25s, visibility .25s;
        }
        .cart-drawer {
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 100;
            width: min(420px, 100%); background: var(--white); color: var(--ink);
            display: flex; flex-direction: column; box-shadow: 0 0 40px rgba(10, 42, 67, .25);
            transform: translateX(-100%); visibility: hidden; transition: transform .3s ease, visibility .3s;
        }
        body.cart-open .cart-overlay { opacity: 1; visibility: visible; }
        body.cart-open .cart-drawer { transform: none; visibility: visible; }
        body.cart-open { overflow: hidden; }

        .cart-head { display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem; border-bottom: 1.5px solid var(--sky-100); }
        .cart-head h2 { font-size: 1.2rem; font-weight: 900; }
        .cart-close { width: 38px; height: 38px; border: 0; border-radius: 50%; background: var(--sky-100); color: var(--ink); font-size: 1.3rem; cursor: pointer; font-family: inherit; }
        .cart-close:hover { background: var(--sky-200); }

        .cart-list { list-style: none; flex: 1; overflow-y: auto; padding: .5rem 1.5rem; }
        .cart-empty { flex: 1; display: grid; place-items: center; text-align: center; padding: 2rem; color: var(--ink-soft); }
        .cart-empty[hidden], .cart-foot[hidden], .cart-list[hidden] { display: none; }

        .cart-item { display: grid; grid-template-columns: 64px 1fr; gap: .9rem; padding: 1rem 0; border-bottom: 1.5px solid var(--sky-100); }
        .cart-thumb { width: 64px; height: 64px; border-radius: 14px; background: var(--sky-100); overflow: hidden; display: grid; place-items: center; font-size: 1.5rem; }
        .cart-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .cart-name { font-weight: 700; font-size: .98rem; line-height: 1.7; }
        .cart-unit { color: var(--ink-soft); font-size: .85rem; }
        .cart-row { margin-top: .4rem; display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
        .qty { display: inline-flex; align-items: center; border: 1.5px solid var(--sky-200); border-radius: 999px; overflow: hidden; }
        .qty button { width: 32px; height: 32px; border: 0; background: transparent; color: var(--sky-700); font-size: 1.1rem; font-weight: 700; cursor: pointer; font-family: inherit; }
        .qty button:hover { background: var(--sky-100); }
        .qty span { min-width: 28px; text-align: center; font-weight: 700; font-size: .95rem; }
        .cart-line { font-weight: 900; color: var(--sky-700); font-size: .95rem; }
        .cart-remove { border: 0; background: none; color: var(--ink-soft); font-size: .82rem; cursor: pointer; text-decoration: underline; font-family: inherit; }
        .cart-remove:hover { color: #c0392b; }

        .cart-foot { padding: 1.25rem 1.5rem 1.5rem; border-top: 1.5px solid var(--sky-100); background: var(--sky-50); }
        .cart-total { display: flex; justify-content: space-between; font-weight: 900; font-size: 1.1rem; margin-bottom: 1rem; }
        .cart-checkout {
            display: block; width: 100%; padding: .9rem 1rem; border: 0; border-radius: 14px;
            background: var(--sky-500); color: var(--white); font-family: inherit; font-weight: 700; font-size: 1rem;
            cursor: pointer; transition: background .2s, transform .2s;
        }
        .cart-checkout:hover { background: var(--sky-700); transform: translateY(-1px); }
        .cart-checkout:disabled { background: #94a3b8; cursor: not-allowed; transform: none; }
        .cart-clear { display: block; margin: .8rem auto 0; border: 0; background: none; color: var(--ink-soft); font-size: .85rem; cursor: pointer; text-decoration: underline; font-family: inherit; }
        .cart-clear:hover { color: #c0392b; }

        /* ---------- Rating ---------- */
        .product-rating {
            display: flex; align-items: center; gap: .4rem;
            font-size: .85rem; color: var(--ink-soft);
        }
        .stars {
            position: relative; display: inline-block; direction: ltr;
            font-size: 1rem; line-height: 1; letter-spacing: 2px; white-space: nowrap;
        }
        .stars-bg   { color: var(--sky-200); }
        .stars-fill {
            position: absolute; top: 0; left: 0; overflow: hidden;
            color: #f5b301; white-space: nowrap;
        }
        .rating-value { font-weight: 700; color: var(--ink); }

        /* ---------- Responsive ---------- */
        @media (max-width: 860px) {
            .nav { display: none; }
            .hero { padding: 8rem 0 8rem; }
            .sun { top: 6rem; left: -30px; opacity: .85; }
            .cloud-1 { width: 150px; top: 11rem; }
            .cloud-2, .cloud-3 { display: none; }
            .f-main, .f-side, .f-wide, .f-wide2 { grid-column: span 6; }
            .steps { grid-template-columns: 1fr; gap: 2rem; }
            .step:not(:last-child)::after { display: none; }
            .cta { padding: 3rem 1.75rem; border-radius: 32px; }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .cloud, .skeleton .product-img, .skeleton .line { animation: none; }
            .btn, .header-cta, .product, .cart-btn, .btn-add, .cart-overlay, .cart-drawer, .cart-checkout { transition: none; }
            .cart-count.bump { animation: none; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="container">
        <a href="{{ url('/') }}" class="brand">
            <span class="brand-dot"></span>
            {{ config('app.name', 'سایت من') }}
        </a>

        <nav class="nav" aria-label="منوی اصلی">
            <a href="#features">امکانات</a>
            <a href="#products">محصولات</a>
            <a href="#steps">نحوه کار</a>
            <a href="#contact">تماس با ما</a>
        </nav>

        <div class="header-actions">
            <button type="button" id="cart-open" class="cart-btn" aria-label="باز کردن سبد خرید" aria-controls="cart-drawer">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.6 12.2a2 2 0 0 0 2 1.6h8.1a2 2 0 0 0 2-1.5L21.5 8H6"/></svg>
                <span id="cart-count" class="cart-count" hidden>۰</span>
            </button>
            <a href="#contact" class="header-cta">شروع کنید</a>
        </div>
    </div>
</header>

<main>
    <!-- Hero -->
    <div class="hero">
        <div class="sun" aria-hidden="true"></div>

        <svg class="cloud cloud-1" viewBox="0 0 200 80" aria-hidden="true"><path d="M50 78a30 30 0 0 1-3-59.8A42 42 0 0 1 128 14a34 34 0 0 1 48 30 22 22 0 0 1-6 34z"/></svg>
        <svg class="cloud cloud-2" viewBox="0 0 200 80" aria-hidden="true"><path d="M50 78a30 30 0 0 1-3-59.8A42 42 0 0 1 128 14a34 34 0 0 1 48 30 22 22 0 0 1-6 34z"/></svg>
        <svg class="cloud cloud-3" viewBox="0 0 200 80" aria-hidden="true"><path d="M50 78a30 30 0 0 1-3-59.8A42 42 0 0 1 128 14a34 34 0 0 1 48 30 22 22 0 0 1-6 34z"/></svg>

        <div class="container">
            <h1>@yield('hero_title', 'کارهایت را سبک‌تر و روشن‌تر کن')</h1>
            <p>@yield('hero_text', 'همه چیزی که برای شروع لازم داری در یک جا جمع شده؛ ساده، سریع و بدون پیچیدگی اضافه.')</p>

            <div class="hero-actions">
                <a href="#contact" class="btn btn-primary">همین حالا شروع کنید</a>
                <a href="#features" class="btn btn-ghost">دیدن امکانات</a>
            </div>
        </div>

        <svg class="horizon" viewBox="0 0 1440 140" preserveAspectRatio="none" aria-hidden="true">
            <path fill="#bde5fb" d="M0 70c180-50 360-50 540-10s360 60 540 20 270-40 360-10v70H0z"/>
            <path fill="#f0f9ff" d="M0 105c200-40 400-40 620-5s420 35 820-15v55H0z"/>
        </svg>
    </div>

    <!-- Features -->
    <section id="features">
        <div class="container">
            <h2 class="section-title">هر چه لازم داری، دم دست</h2>
            <p class="section-lead">چهار دلیل ساده برای اینکه از همان روز اول احساس راحتی کنی.</p>

            <div class="features">
                <article class="feature f-main">
                    <h3>سرعت بالا</h3>
                    <p>صفحه‌ها در کسری از ثانیه باز می‌شوند، حتی با اینترنت ضعیف.</p>
                </article>

                <article class="feature f-side">
                    <h3>طراحی واکنش‌گرا</h3>
                    <p>روی موبایل، تبلت و دسکتاپ یکسان دیده می‌شود.</p>
                </article>

                <article class="feature f-wide">
                    <h3>امنیت داده‌ها</h3>
                    <p>اطلاعات تو رمزگذاری و با دقت نگهداری می‌شود.</p>
                </article>

                <article class="feature f-wide2">
                    <h3>پشتیبانی واقعی</h3>
                    <p>هر سؤالی داشتی، یک نفر پاسخ می‌دهد؛ نه یک ربات.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- Products (loaded with JavaScript from the API) -->
    <section id="products">
        <div class="container">
            <h2 class="section-title">محصولات ما</h2>
            <p class="section-lead">جدیدترین محصولات، مستقیم از فروشگاه.</p>

            <div id="products-grid" class="products" aria-live="polite" aria-busy="true"></div>
        </div>
    </section>

    <!-- Steps -->
    <section id="steps" class="steps-wrap">
        <div class="container">
            <h2 class="section-title">در سه قدم شروع کن</h2>

            <div class="steps">
                <div class="step">
                    <h3>ثبت‌نام کن</h3>
                    <p>فقط با ایمیل یا شماره موبایل، در کمتر از یک دقیقه.</p>
                </div>
                <div class="step">
                    <h3>تنظیمات را انتخاب کن</h3>
                    <p>گزینه‌هایی که به کارت می‌آید را روشن کن.</p>
                </div>
                <div class="step">
                    <h3>استفاده را شروع کن</h3>
                    <p>همه‌چیز آماده است و می‌توانی کارت را شروع کنی.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section id="contact">
        <div class="container">
            <div class="cta">
                <h2>آماده‌ای اولین قدم را برداری؟</h2>
                <a href="{{ url('/register') }}" class="btn">ساخت حساب رایگان</a>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container">
        <span>© {{ date('Y') }} {{ config('app.name', 'سایت من') }}. همه حقوق محفوظ است.</span>
        <nav class="footer-links" aria-label="پیوندهای پایین صفحه">
            <a href="#">حریم خصوصی</a>
            <a href="#">قوانین</a>
            <a href="#contact">تماس</a>
        </nav>
    </div>
</footer>

<!-- Cart drawer -->
<div id="cart-overlay" class="cart-overlay"></div>
<aside id="cart-drawer" class="cart-drawer" role="dialog" aria-modal="true" aria-labelledby="cart-title" aria-hidden="true">
    <div class="cart-head">
        <h2 id="cart-title">سبد خرید</h2>
        <button type="button" id="cart-close" class="cart-close" aria-label="بستن سبد خرید">×</button>
    </div>

    <p id="cart-empty" class="cart-empty">سبد خرید شما خالی است.<br>از بخش محصولات چیزی به آن اضافه کنید.</p>
    <ul id="cart-list" class="cart-list" hidden></ul>

    <div id="cart-foot" class="cart-foot" hidden>
        <div class="cart-total"><span>جمع کل</span><span id="cart-total">۰ تومان</span></div>
        <button type="button" id="cart-checkout" class="cart-checkout">ادامه و پرداخت</button>
        <button type="button" id="cart-clear" class="cart-clear">خالی کردن سبد</button>
    </div>
</aside>

<div id="cart-live" class="sr-only" aria-live="polite" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap;"></div>

<script>
    // سبد خرید: ذخیره در localStorage مرورگر
    (function () {
        var KEY = 'cart_v1';

        // آدرس‌ها: در صورت نیاز تغییر بده
        var USER_API_URL = "{{ url('/api/v1/customer/auth/me') }}";
        var TOKEN_KEY    = 'customer_token';
        var LOGIN_URL    = '/demo/customer-auth?redirect=/demo/checkout';
        var CHECKOUT_URL = '/demo/checkout';

        var items = load();

        var openBtn      = document.getElementById('cart-open');
        var closeBtn     = document.getElementById('cart-close');
        var overlay      = document.getElementById('cart-overlay');
        var drawer       = document.getElementById('cart-drawer');
        var listEl       = document.getElementById('cart-list');
        var emptyEl      = document.getElementById('cart-empty');
        var footEl       = document.getElementById('cart-foot');
        var totalEl      = document.getElementById('cart-total');
        var countEl      = document.getElementById('cart-count');
        var clearBtn     = document.getElementById('cart-clear');
        var liveEl       = document.getElementById('cart-live');
        var checkoutBtn  = document.getElementById('cart-checkout');

        function el(tag, className, text) {
            var node = document.createElement(tag);
            if (className) node.className = className;
            if (text !== undefined && text !== null) {
                node.textContent = text;
            }
            return node;
        }

        function fa(n) {
            return Number(n).toLocaleString('fa-IR');
        }

        function money(n) {
            return fa(n) + ' تومان';
        }

        function load() {
            try {
                var raw = localStorage.getItem(KEY);
                var data = raw ? JSON.parse(raw) : [];

                if (!Array.isArray(data)) return [];

                return data.filter(function (i) {
                    return i && typeof i.id === 'string' &&
                        i.id && Number(i.qty) >= 1;
                }).map(function (i) {
                    return {
                        id: i.id,
                        name: String(i.name || ''),
                        price: Math.max(0, Number(i.price) || 0),
                        image: typeof i.image === 'string' ? i.image : '',
                        qty: Math.min(99, Math.floor(Number(i.qty)))
                    };
                });
            } catch (e) {
                return [];
            }
        }

        function save() {
            try {
                localStorage.setItem(KEY, JSON.stringify(items));
            } catch (e) {
                // حالت خصوصی یا پر بودن حافظه
            }
        }

        function find(id) {
            for (var i = 0; i < items.length; i++) {
                if (items[i].id === id) return items[i];
            }
            return null;
        }

        function add(product) {
            var id = String(product.id);
            var found = find(id);

            if (found) {
                found.qty = Math.min(99, found.qty + 1);
            } else {
                items.push({
                    id: id,
                    name: product.name,
                    price: Number(product.price) || 0,
                    image: product.image || '',
                    qty: 1
                });
            }

            save();
            render(true);

            if (liveEl) {
                liveEl.textContent = product.name + ' به سبد خرید اضافه شد.';
            }
        }

        function setQty(id, qty) {
            var item = find(id);
            if (!item) return;

            if (qty < 1) {
                remove(id);
                return;
            }

            item.qty = Math.min(99, qty);
            save();
            render();
        }

        function remove(id) {
            items = items.filter(function (i) {
                return i.id !== id;
            });

            save();
            render();
        }

        function clearAll() {
            items = [];
            save();
            render();
        }

        function renderItem(item) {
            var li = el('li', 'cart-item');

            var thumb = el('div', 'cart-thumb');

            if (item.image) {
                var img = document.createElement('img');
                img.src = item.image;
                img.alt = item.name;
                img.loading = 'lazy';

                img.addEventListener('error', function () {
                    thumb.textContent = '🛍️';
                });

                thumb.appendChild(img);
            } else {
                thumb.textContent = '🛍️';
            }

            li.appendChild(thumb);

            var info = el('div');
            info.appendChild(el('div', 'cart-name', item.name));
            info.appendChild(el('div', 'cart-unit', money(item.price)));

            var row = el('div', 'cart-row');
            var qty = el('div', 'qty');

            var minus = el('button', null, '−');
            minus.type = 'button';
            minus.setAttribute('aria-label', 'کم کردن تعداد');

            var plus = el('button', null, '+');
            plus.type = 'button';
            plus.setAttribute('aria-label', 'زیاد کردن تعداد');

            minus.addEventListener('click', function () {
                setQty(item.id, item.qty - 1);
            });

            plus.addEventListener('click', function () {
                setQty(item.id, item.qty + 1);
            });

            qty.appendChild(plus);
            qty.appendChild(el('span', null, fa(item.qty)));
            qty.appendChild(minus);

            row.appendChild(qty);
            row.appendChild(el('span', 'cart-line', money(item.price * item.qty)));

            info.appendChild(row);

            var rm = el('button', 'cart-remove', 'حذف');
            rm.type = 'button';

            rm.addEventListener('click', function () {
                remove(item.id);
            });

            info.appendChild(rm);
            li.appendChild(info);

            return li;
        }

        function render(bump) {
            var count = 0;
            var total = 0;

            items.forEach(function (i) {
                count += i.qty;
                total += i.price * i.qty;
            });

            listEl.replaceChildren();

            items.forEach(function (i) {
                listEl.appendChild(renderItem(i));
            });

            var hasItems = items.length > 0;

            listEl.hidden = !hasItems;
            footEl.hidden = !hasItems;
            emptyEl.hidden = hasItems;

            totalEl.textContent = money(total);

            countEl.hidden = count === 0;
            countEl.textContent = fa(count);

            if (bump) {
                countEl.classList.remove('bump');
                void countEl.offsetWidth;
                countEl.classList.add('bump');
            }
        }

        // بررسی ورود مشتری و انتقال به صفحه پرداخت یا صفحه لاگین
        async function continueToCheckout() {
            if (items.length === 0) {
                alert('سبد خرید شما خالی است.');
                return;
            }

            var token = localStorage.getItem(TOKEN_KEY);

            if (!token) {
                window.location.href = LOGIN_URL;
                return;
            }

            checkoutBtn.disabled = true;

            try {
                var response = await fetch(USER_API_URL, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                });

                if (response.ok) {
                    window.location.href = CHECKOUT_URL;
                    return;
                }

                // توکن نامعتبر یا منقضی
                localStorage.removeItem(TOKEN_KEY);
                window.location.href = LOGIN_URL;

            } catch (error) {
                checkoutBtn.disabled = false;
                alert('ارتباط با سرور برقرار نشد.');
            }
        }

        checkoutBtn.addEventListener('click', continueToCheckout);

        function openCart() {
            document.body.classList.add('cart-open');
            drawer.setAttribute('aria-hidden', 'false');
            closeBtn.focus();
        }

        function closeCart() {
            document.body.classList.remove('cart-open');
            drawer.setAttribute('aria-hidden', 'true');
            openBtn.focus();
        }

        openBtn.addEventListener('click', openCart);
        closeBtn.addEventListener('click', closeCart);
        overlay.addEventListener('click', closeCart);
        clearBtn.addEventListener('click', clearAll);

        document.addEventListener('keydown', function (e) {
            if (
                e.key === 'Escape' &&
                document.body.classList.contains('cart-open')
            ) {
                closeCart();
            }
        });

        // همگام‌سازی بین تب‌های باز
        window.addEventListener('storage', function (e) {
            if (e.key === KEY) {
                items = load();
                render();
            }
        });

        window.Cart = {
            add: add
        };

        render();
    })();
</script>

<script>
    (function () {
        // آدرس API. اگر صفحه از همان پروژه لاراول سرو می‌شود، url() همان دامنه را می‌سازد
        var API_URL = "{{ url('/api/v1/products') }}";
        var grid = document.getElementById('products-grid');

        function el(tag, className, text) {
            var node = document.createElement(tag);
            if (className) node.className = className;
            if (text !== undefined && text !== null) node.textContent = text; // textContent: امن در برابر XSS
            return node;
        }

        function showSkeletons(count) {
            grid.setAttribute('aria-busy', 'true');
            grid.replaceChildren();
            for (var i = 0; i < count; i++) {
                var card = el('article', 'product skeleton');
                card.appendChild(el('div', 'product-img'));
                var body = el('div', 'product-body');
                body.appendChild(el('div', 'line'));
                body.appendChild(el('div', 'line short'));
                card.appendChild(body);
                grid.appendChild(card);
            }
        }

        function showMessage(text, withRetry) {
            grid.setAttribute('aria-busy', 'false');
            grid.replaceChildren();
            var box = el('div', 'products-message');
            box.appendChild(el('p', null, text));
            if (withRetry) {
                var btn = el('button', 'btn', 'تلاش دوباره');
                btn.type = 'button';
                btn.addEventListener('click', loadProducts);
                box.appendChild(btn);
            }
            grid.appendChild(box);
        }

        // پاسخ ممکن است آرایه ساده یا شکل صفحه‌بندی‌شده لاراول ({ data: [...] }) باشد
        function extractList(json) {
            if (Array.isArray(json)) return json;
            if (json && Array.isArray(json.data)) return json.data;
            if (json && json.data && Array.isArray(json.data.data)) return json.data.data;
            if (json && Array.isArray(json.products)) return json.products;
            return [];
        }

        function fa(n) {
            return Number(n).toLocaleString('fa-IR');
        }

        function renderRating(value) {
            var r = Number(value);
            if (value === null || value === undefined || value === '' || isNaN(r) || r <= 0) return null;
            r = Math.min(5, Math.max(0, r));

            var wrap = el('div', 'product-rating');
            wrap.setAttribute('role', 'img');
            wrap.setAttribute('aria-label', 'امتیاز ' + Number(r.toFixed(1)).toLocaleString('fa-IR') + ' از ۵');

            var stars = el('span', 'stars');
            stars.setAttribute('aria-hidden', 'true');
            stars.appendChild(el('span', 'stars-bg', '★★★★★'));

            var fill = el('span', 'stars-fill', '★★★★★');
            fill.style.width = (r / 5 * 100) + '%';
            stars.appendChild(fill);

            wrap.appendChild(stars);
            wrap.appendChild(el('span', 'rating-value', Number(r.toFixed(1)).toLocaleString('fa-IR')));
            return wrap;
        }

        function renderProduct(p) {
            var name        = p.name || p.title || 'بدون نام';
            var desc        = p.description || p.summary || '';
            var image       = p.cover_image || p.image || p.image_url || p.thumbnail || '';
            var price       = Number(p.price) || 0;
            var finalPrice  = (p.final_price !== undefined && p.final_price !== null)
                ? Number(p.final_price)
                : price;
            var percent     = Number(p.discount_percent) || 0;
            var hasDiscount = percent > 0 && finalPrice < price;
            var brand       = p.brand_title || '';
            var category    = p.category_title || '';
            var outOfStock  = p.stock_status === 'out_of_stock' || Number(p.stock) === 0;

            var card = el('article', 'product');

            // تصویر + برچسب تخفیف
            var imgBox = el('div', 'product-img');
            if (image) {
                var img = document.createElement('img');
                img.src = image;
                img.alt = name;
                img.loading = 'lazy';
                img.addEventListener('error', function () {
                    imgBox.replaceChildren(document.createTextNode('🛍️'));
                });
                imgBox.appendChild(img);
            } else {
                imgBox.textContent = '🛍️';
            }
            if (hasDiscount) {
                imgBox.appendChild(el('span', 'product-badge', '٪' + fa(percent)));
            }
            card.appendChild(imgBox);

            var body = el('div', 'product-body');
            body.appendChild(el('h3', 'product-name', name));

            var rating = renderRating(p.display_rating);
            if (rating) body.appendChild(rating);

            if (brand || category) {
                body.appendChild(el('div', 'product-meta', [brand, category].filter(Boolean).join(' · ')));
            }

            if (desc) {
                body.appendChild(el('p', 'product-desc', desc));
            }

            // قیمت: قیمت اصلی خط‌خورده + قیمت نهایی
            var priceRow = el('div', 'product-price-row');
            if (hasDiscount) {
                priceRow.appendChild(el('del', 'product-old-price', fa(price) + ' تومان'));
            }
            priceRow.appendChild(el('span', 'product-price', fa(finalPrice) + ' تومان'));
            body.appendChild(priceRow);

            var addBtn = el('button', 'btn-add', outOfStock ? 'ناموجود' : 'افزودن به سبد خرید');
            addBtn.type = 'button';
            addBtn.disabled = outOfStock;

            addBtn.addEventListener('click', function () {
                var id = (p.id !== undefined && p.id !== null) ? String(p.id) : String(p.sku || name);
                // سبد خرید با قیمت نهایی (بعد از تخفیف) ذخیره می‌شود
                window.Cart.add({ id: id, name: name, price: finalPrice, image: image });
                addBtn.textContent = 'به سبد اضافه شد ✓';
                addBtn.classList.add('added');
                setTimeout(function () {
                    addBtn.textContent = 'افزودن به سبد خرید';
                    addBtn.classList.remove('added');
                }, 1400);
            });

            body.appendChild(addBtn);
            card.appendChild(body);

            return card;
        }

        function loadProducts() {
            showSkeletons(4);

            fetch(API_URL, { headers: { 'Accept': 'application/json' } })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (json) {
                var list = extractList(json);
                if (!list.length) {
                    showMessage('هنوز محصولی ثبت نشده است.', false);
                    return;
                }
                grid.replaceChildren();
                list.forEach(function (p) { grid.appendChild(renderProduct(p)); });
                grid.setAttribute('aria-busy', 'false');
            })
            .catch(function (err) {
                console.error('خطا در دریافت محصولات:', err);
                showMessage('دریافت محصولات انجام نشد. اتصال سرور را بررسی کنید و دوباره امتحان کنید.', true);
            });
        }

        loadProducts();
    })();
</script>

</body>
</html>