
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تست احراز هویت مشتری</title>

    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Tahoma, sans-serif;
            background: #f3f4f6;
            padding: 30px 15px;
            margin: 0;
        }

        .container {
            max-width: 480px;
            margin: auto;
        }

        .card {
            background: white;
            padding: 22px;
            margin-bottom: 18px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }

        h2 { margin-top: 0; font-size: 20px; }
        h3 { font-size: 16px; }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            margin: 8px 0;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 12px;
            border: 0;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            cursor: pointer;
            margin-top: 8px;
            font-size: 14px;
        }

        button.secondary { background: #374151; }

        pre {
            direction: ltr;
            text-align: left;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            background: #111827;
            color: #d1fae5;
            padding: 15px;
            border-radius: 6px;
            min-height: 80px;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>تست احراز هویت مشتری</h2>

    <div class="card">
        <h3>۱. ارسال کد تأیید</h3>

        <input
                type="text"
                id="mobile"
                placeholder="شماره موبایل"
                value="09121234567"
        >

        <button onclick="sendOtp()">ارسال کد OTP</button>
    </div>

    <div class="card">
        <h3>۲. تأیید کد</h3>

        <input
                type="text"
                id="otp"
                placeholder="کد شش رقمی"
                maxlength="6"
        >

        <button onclick="verifyOtp()">تأیید و ورود</button>
    </div>

    <div class="card">
        <h3>۳. ورود با رمز عبور</h3>

        <input
                type="password"
                id="password"
                placeholder="رمز عبور"
        >

        <button onclick="loginPassword()">ورود</button>
    </div>

    <div class="card">
        <h3>۴. وضعیت حساب</h3>

        <button class="secondary" onclick="getMe()">
            دریافت اطلاعات مشتری
        </button>

        <button class="secondary" onclick="logout()">
            خروج از حساب
        </button>
    </div>

    <div class="card">
        <h3>نتیجه درخواست</h3>
        <pre id="result">آماده تست</pre>
    </div>

</div>

<script>
    const API = '/api/v1/customer/auth';

    let token = localStorage.getItem('customer_token');

    async function request(url, data = null, method = 'POST') {
        try {
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            };

            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
            }

            const response = await fetch(url, {
                method,
                headers,
                body: data ? JSON.stringify(data) : null
            });

            const result = await response.json();

            document.getElementById('result').textContent =
                JSON.stringify(result, null, 2);

            return { response, result };

        } catch (error) {
            document.getElementById('result').textContent = error.message;
            return { response: null, result: null };
        }
    }

    function getMobile() {
        return document.getElementById('mobile').value;
    }

    async function sendOtp() {
        await request(API + '/send-otp', {
            mobile_number: getMobile()
        });
    }

    async function verifyOtp() {
        const { response, result } = await request(
            API + '/verify-otp',
            {
                mobile_number: getMobile(),
                verify_code: document.getElementById('otp').value
            }
        );

        if (response?.ok && result?.token) {
            token = result.token;
            localStorage.setItem('customer_token', token);
        }
    }

    async function loginPassword() {
        const { response, result } = await request(
            API + '/login-password',
            {
                mobile_number: getMobile(),
                password: document.getElementById('password').value
            }
        );

        if (response?.ok && result?.token) {
            token = result.token;
            localStorage.setItem('customer_token', token);
        }
    }

    async function getMe() {
        await request('/api/user', null, 'GET');
    }

    async function logout() {
        const { response } = await request(API + '/logout');

        if (response?.ok) {
            token = null;
            localStorage.removeItem('customer_token');
        }
    }
</script>

</body>
</html>
