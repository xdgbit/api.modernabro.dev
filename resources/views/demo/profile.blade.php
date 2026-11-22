<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>پروفایل مشتری</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Tahoma, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 30px 15px;
        }

        .container {
            max-width: 550px;
            margin: auto;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-top: 20px;
        }

        h2 {
            margin-top: 0;
        }

        .item {
            padding: 13px 0;
            border-bottom: 1px solid #eee;
        }

        .item span {
            color: #777;
            display: inline-block;
            width: 140px;
        }

        button {
            width: 100%;
            padding: 13px;
            background: #dc2626;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 20px;
        }

        #loading {
            text-align: center;
            padding: 40px;
        }
    </style>
</head>

<body>

<div class="container" id="profile" style="display:none">

    <div class="card">
        <h2>پروفایل مشتری</h2>

        <div class="item">
            <span>شناسه مشتری:</span>
            <strong id="id"></strong>
        </div>

        <div class="item">
            <span>نام:</span>
            <strong id="first_name"></strong>
        </div>

        <div class="item">
            <span>نام خانوادگی:</span>
            <strong id="last_name"></strong>
        </div>

        <div class="item">
            <span>شماره موبایل:</span>
            <strong id="mobile_number"></strong>
        </div>

        <div class="item">
            <span>ایمیل:</span>
            <strong id="email"></strong>
        </div>

        <div class="item">
            <span>وضعیت حساب:</span>
            <strong id="is_active"></strong>
        </div>

        <div class="item">
            <span>وضعیت موبایل:</span>
            <strong id="mobile_verified"></strong>
        </div>

        <button onclick="logout()">خروج از حساب</button>
    </div>

</div>

<div id="loading">در حال بررسی وضعیت ورود...</div>

<script>
    const API = '/api/user';
    const LOGIN_PAGE = '/demo/customer-auth';

    async function loadProfile()
    {
        const token = localStorage.getItem('customer_token');

        if (!token)
        {
            window.location.replace(LOGIN_PAGE);
            return;
        }

        try
        {
            const response = await fetch(API, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });

            if (!response.ok)
            {
                localStorage.removeItem('customer_token');
                window.location.replace(LOGIN_PAGE);
                return;
            }

            const customer = await response.json();

            document.getElementById('id').textContent = customer.id ?? '-';
            document.getElementById('first_name').textContent = customer.first_name || '-';
            document.getElementById('last_name').textContent = customer.last_name || '-';
            document.getElementById('mobile_number').textContent = customer.mobile_number || '-';
            document.getElementById('email').textContent = customer.email || '-';

            document.getElementById('is_active').textContent =
                customer.is_active ? 'فعال' : 'غیرفعال';

            document.getElementById('mobile_verified').textContent =
                customer.mobile_verified_at ? 'تأیید شده' : 'تأیید نشده';

            document.getElementById('loading').style.display = 'none';
            document.getElementById('profile').style.display = 'block';

        } catch (error)
        {
            document.getElementById('loading').textContent =
                'خطا در ارتباط با سرور';
        }
    }

    function logout()
    {
        localStorage.removeItem('customer_token');
        window.location.replace(LOGIN_PAGE);
    }

    loadProfile();
</script>

</body>
</html>
