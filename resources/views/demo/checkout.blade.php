<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تکمیل سفارش | {{ config('app.name', 'سایت من') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --sky-50: #f0f9ff; --sky-100: #e0f3fe; --sky-200: #bde5fb; --sky-400: #5cbcf0;
            --sky-500: #2a9fe3; --sky-700: #0f6aa8; --ink: #0a2a43; --ink-soft: #35546b;
            --sun: #ffc94d; --white: #fff; --danger: #c0392b;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Vazirmatn', Tahoma, sans-serif; background: var(--sky-50); color: var(--ink); line-height: 1.9; -webkit-font-smoothing: antialiased; }
        a { color: inherit; text-decoration: none; }
        :focus-visible { outline: 3px solid var(--sun); outline-offset: 3px; border-radius: 6px; }
        .container { width: min(1060px, 100% - 2.5rem); margin-inline: auto; }

        .topbar { background: linear-gradient(180deg, var(--sky-500), var(--sky-400)); color: var(--white); padding: 1.1rem 0; }
        .topbar .container { display: flex; align-items: center; justify-content: space-between; }
        .brand { font-weight: 900; font-size: 1.3rem; display: flex; align-items: center; gap: .6rem; }
        .brand-dot { width: 14px; height: 14px; border-radius: 50%; background: var(--sun); box-shadow: 0 0 0 5px rgba(255, 201, 77, .3); }
        .back { font-weight: 500; opacity: .95; }
        .back:hover { text-decoration: underline; }

        h1 { font-size: clamp(1.6rem, 3.4vw, 2.2rem); font-weight: 900; margin: 2.5rem 0 1.5rem; }

        .layout { display: grid; grid-template-columns: 1.4fr 1fr; gap: 1.5rem; align-items: start; padding-bottom: 4rem; }
        .card { background: var(--white); border: 1.5px solid var(--sky-200); border-radius: 24px; padding: 1.75rem; }
        .card h2 { font-size: 1.15rem; font-weight: 900; margin-bottom: 1.2rem; }

        .fields { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem 1.1rem; }
        .field { display: flex; flex-direction: column; gap: .3rem; }
        .field.full { grid-column: 1 / -1; }
        label { font-weight: 700; font-size: .93rem; }
        label small { font-weight: 400; color: var(--ink-soft); }
        input, textarea {
            font: inherit; color: var(--ink); background: var(--sky-50); border: 1.5px solid var(--sky-200);
            border-radius: 12px; padding: .65rem .9rem; width: 100%; transition: border-color .2s, background .2s;
        }
        textarea { resize: vertical; min-height: 96px; }
        input:focus, textarea:focus { outline: none; border-color: var(--sky-500); background: var(--white); box-shadow: 0 0 0 3px rgba(42, 159, 227, .2); }
        .invalid input, .invalid textarea { border-color: var(--danger); }
        .err { color: var(--danger); font-size: .85rem; min-height: 0; }

        .alert { display: none; margin-top: 1rem; padding: .8rem 1rem; border-radius: 12px; background: #fdecea; color: var(--danger); font-weight: 500; }
        .alert.show { display: block; }

        .btn {
            margin-top: 1.4rem; width: 100%; padding: .95rem 1.5rem; border: 0; border-radius: 14px; cursor: pointer;
            background: var(--ink); color: var(--white); font: inherit; font-weight: 700; font-size: 1.02rem;
            transition: transform .2s, box-shadow .2s, opacity .2s;
        }
        .btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(10, 42, 67, .25); }
        .btn:disabled { opacity: .6; cursor: wait; }

        .summary { position: sticky; top: 1.25rem; }
        .sum-list { list-style: none; }
        .sum-item { display: grid; grid-template-columns: 52px 1fr auto; gap: .8rem; align-items: center; padding: .75rem 0; border-bottom: 1.5px solid var(--sky-100); }
        .sum-thumb { width: 52px; height: 52px; border-radius: 12px; background: var(--sky-100); overflow: hidden; display: grid; place-items: center; }
        .sum-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .sum-name { font-weight: 700; font-size: .93rem; line-height: 1.6; }
        .sum-qty { color: var(--ink-soft); font-size: .82rem; }
        .sum-line { font-weight: 700; font-size: .9rem; color: var(--sky-700); white-space: nowrap; }
        .sum-total { display: flex; justify-content: space-between; margin-top: 1rem; font-weight: 900; font-size: 1.1rem; }
        .sum-note { margin-top: .6rem; color: var(--ink-soft); font-size: .82rem; }

        .state { text-align: center; padding: 3.5rem 1.5rem; background: var(--white); border: 1.5px dashed var(--sky-200); border-radius: 24px; margin-bottom: 4rem; }
        .state h2 { font-size: 1.4rem; font-weight: 900; margin-bottom: .5rem; }
        .state p { color: var(--ink-soft); }
        .state .link { display: inline-block; margin-top: 1.2rem; padding: .75rem 1.8rem; border-radius: 14px; background: var(--sky-500); color: var(--white); font-weight: 700; }
        .state .order-no { display: inline-block; margin-top: 1rem; padding: .4rem 1.2rem; border-radius: 999px; background: var(--sky-100); color: var(--sky-700); font-weight: 900; }
        [hidden] { display: none !important; }

        @media (max-width: 860px) {
            .layout { grid-template-columns: 1fr; }
            .summary { position: static; order: -1; }
            .fields { grid-template-columns: 1fr; }
        }
        @media (prefers-reduced-motion: reduce) { .btn, input, textarea { transition: none; } }
    </style>
</head>
<body>

<header class="topbar">
    <div class="container">
        <a href="{{ url('/') }}" class="brand"><span class="brand-dot"></span>{{ config('app.name', 'سایت من') }}</a>
        <a href="{{ url('/') }}#products" class="back">بازگشت به فروشگاه</a>
    </div>
</header>

<main class="container">
    <h1>تکمیل سفارش</h1>

    <!-- سبد خالی -->
    <div id="empty" class="state" hidden>
        <h2>سبد خرید شما خالی است</h2>
        <p>برای ثبت سفارش، ابتدا محصولی به سبد اضافه کنید.</p>
        <a class="link" href="{{ url('/') }}#products">دیدن محصولات</a>
    </div>

    <!-- ثبت موفق -->
    <div id="success" class="state" hidden>
        <h2>سفارش شما ثبت شد</h2>
        <p>به‌زودی برای هماهنگی ارسال با شما تماس می‌گیریم.</p>
        <div class="order-no">شماره سفارش: <span id="order-no">-</span></div>
        <p id="order-total" style="margin-top:.8rem;"></p>
        <a class="link" href="{{ url('/') }}">بازگشت به صفحه اصلی</a>
    </div>

    <div id="checkout" class="layout" hidden>
        <form id="form" class="card" novalidate>
            <h2>اطلاعات تحویل</h2>
            <div class="fields">
                <div class="field" data-field="name">
                    <label for="name">نام و نام خانوادگی</label>
                    <input id="name" name="name" type="text" autocomplete="name" maxlength="100" required>
                    <p class="err"></p>
                </div>
                <div class="field" data-field="phone">
                    <label for="phone">شماره موبایل</label>
                    <input id="phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel" placeholder="09123456789" maxlength="11" dir="ltr" style="text-align:right" required>
                    <p class="err"></p>
                </div>
                <div class="field" data-field="city">
                    <label for="city">شهر</label>
                    <input id="city" name="city" type="text" autocomplete="address-level2" maxlength="100" required>
                    <p class="err"></p>
                </div>
                <div class="field" data-field="postal_code">
                    <label for="postal_code">کد پستی <small>(۱۰ رقم)</small></label>
                    <input id="postal_code" name="postal_code" type="text" inputmode="numeric" autocomplete="postal-code" maxlength="10" dir="ltr" style="text-align:right" required>
                    <p class="err"></p>
                </div>
                <div class="field full" data-field="address">
                    <label for="address">آدرس کامل</label>
                    <textarea id="address" name="address" autocomplete="street-address" maxlength="500" required></textarea>
                    <p class="err"></p>
                </div>
                <div class="field full" data-field="note">
                    <label for="note">توضیحات <small>(اختیاری)</small></label>
                    <textarea id="note" name="note" maxlength="500" style="min-height:72px"></textarea>
                    <p class="err"></p>
                </div>
            </div>

            <div id="alert" class="alert" role="alert"></div>
            <button id="submit" type="submit" class="btn">ثبت سفارش</button>
        </form>

        <aside class="card summary" aria-label="خلاصه سفارش">
            <h2>خلاصه سفارش</h2>
            <ul id="sum-list" class="sum-list"></ul>
            <div class="sum-total"><span>جمع کل</span><span id="sum-total">۰ تومان</span></div>
            <p class="sum-note">مبلغ نهایی هنگام ثبت، از روی قیمت فعلی محصولات در سرور محاسبه می‌شود.</p>
        </aside>
    </div>
</main>

<script>
    (function () {
        var KEY = 'cart_v1';
        var API = "{{ url('/api/v1/orders') }}";
        var $ = function (id) { return document.getElementById(id); };

        function fa(n) { return Number(n).toLocaleString('fa-IR'); }
        function money(n) { return fa(n) + ' تومان'; }
        function digits(s) {
            return String(s)
            .replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); })
            .replace(/[٠-٩]/g, function (d) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(d); });
        }
        function el(tag, cls, text) {
            var n = document.createElement(tag);
            if (cls) n.className = cls;
            if (text !== undefined && text !== null) n.textContent = text;
            return n;
        }

        function loadCart() {
            try {
                var data = JSON.parse(localStorage.getItem(KEY) || '[]');
                if (!Array.isArray(data)) return [];
                return data.filter(function (i) { return i && i.id && Number(i.qty) >= 1; });
            } catch (e) { return []; }
        }

        var cart = loadCart();
        var form = $('form'), alertBox = $('alert'), submitBtn = $('submit');

        // ---------- خلاصه سفارش ----------
        function renderSummary() {
            var list = $('sum-list'), total = 0;
            list.replaceChildren();
            cart.forEach(function (i) {
                var price = Number(i.price) || 0, qty = Number(i.qty);
                total += price * qty;

                var li = el('li', 'sum-item');
                var thumb = el('div', 'sum-thumb');
                if (i.image) {
                    var img = document.createElement('img');
                    img.src = i.image; img.alt = ''; img.loading = 'lazy';
                    img.addEventListener('error', function () { thumb.textContent = '🛍️'; });
                    thumb.appendChild(img);
                } else { thumb.textContent = '🛍️'; }

                var info = el('div');
                info.appendChild(el('div', 'sum-name', i.name));
                info.appendChild(el('div', 'sum-qty', fa(qty) + ' عدد × ' + money(price)));

                li.appendChild(thumb);
                li.appendChild(info);
                li.appendChild(el('div', 'sum-line', money(price * qty)));
                list.appendChild(li);
            });
            $('sum-total').textContent = money(total);
        }

        // ---------- خطاها ----------
        function setError(field, msg) {
            var box = form.querySelector('[data-field="' + field + '"]');
            if (!box) return false;
            box.classList.add('invalid');
            box.querySelector('.err').textContent = msg;
            return true;
        }
        function clearErrors() {
            form.querySelectorAll('.field').forEach(function (f) {
                f.classList.remove('invalid');
                f.querySelector('.err').textContent = '';
            });
            alertBox.classList.remove('show');
            alertBox.textContent = '';
        }
        function showAlert(msg) { alertBox.textContent = msg; alertBox.classList.add('show'); }

        function validate(d) {
            var ok = true, first = null;
            function bad(field, msg) { setError(field, msg); ok = false; if (!first) first = field; }
            if (d.name.length < 3) bad('name', 'نام و نام خانوادگی را وارد کنید.');
            if (!/^09\d{9}$/.test(d.phone)) bad('phone', 'شماره موبایل باید مثل 09123456789 باشد.');
            if (!d.city) bad('city', 'شهر را وارد کنید.');
            if (!/^\d{10}$/.test(d.postal_code)) bad('postal_code', 'کد پستی باید ۱۰ رقم باشد.');
            if (d.address.length < 10) bad('address', 'آدرس را کامل‌تر وارد کنید.');
            if (first) $(first).focus();
            return ok;
        }

        // ---------- ثبت سفارش ----------
        function setBusy(b) {
            submitBtn.disabled = b;
            submitBtn.textContent = b ? 'در حال ثبت سفارش…' : 'ثبت سفارش';
        }

        function onSuccess(order) {
            try { localStorage.removeItem(KEY); } catch (e) {}
            $('checkout').hidden = true;
            $('order-no').textContent = order.id !== undefined ? fa(order.id) : '-';
            if (order.total !== undefined) $('order-total').textContent = 'مبلغ نهایی: ' + money(order.total);
            $('success').hidden = false;
            window.scrollTo({ top: 0 });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (submitBtn.disabled) return;
            clearErrors();

            var data = {
                name: form.name.value.trim(),
                phone: digits(form.phone.value.trim()),
                city: form.city.value.trim(),
                postal_code: digits(form.postal_code.value.trim()),
                address: form.address.value.trim(),
                note: form.note.value.trim(),
                // فقط شناسه و تعداد؛ قیمت را سرور از دیتابیس می‌خواند
                items: cart.map(function (i) { return { product_id: Number(i.id), qty: Number(i.qty) }; })
            };
            if (!validate(data)) return;

            setBusy(true);
            fetch(API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (body) {
                    return { ok: res.ok, status: res.status, body: body };
                });
            })
            .then(function (r) {
                if (r.ok) { onSuccess(r.body.data || {}); return; }
                if (r.status === 422) {
                    var errors = r.body.errors || {}, itemsBad = false;
                    Object.keys(errors).forEach(function (k) {
                        if (!setError(k, errors[k][0])) itemsBad = true;
                    });
                    if (itemsBad) showAlert('یکی از محصولات سبد دیگر موجود نیست. سبد خرید را بررسی کنید.');
                    return;
                }
                if (r.status === 429) { showAlert('تعداد درخواست‌ها زیاد است. کمی بعد دوباره تلاش کنید.'); return; }
                showAlert('ثبت سفارش انجام نشد. دوباره تلاش کنید.');
            })
            .catch(function () { showAlert('اتصال به سرور برقرار نشد. اینترنت خود را بررسی کنید.'); })
            .then(function () { setBusy(false); });
        });

        // ---------- شروع ----------
        if (!cart.length) {
            $('empty').hidden = false;
        } else {
            renderSummary();
            $('checkout').hidden = false;
        }
    })();
</script>
</body>
</html>