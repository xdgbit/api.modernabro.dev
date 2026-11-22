
<?php
$bypass = is_set_developer_token();
?>

        <!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>خوش آمدید به API</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
            font-family: Tahoma, Arial, sans-serif;
        }

        .container {
            text-align: center;
            padding: 50px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 90%;
        }

        h1 {
            color: #ffffff;
            font-size: 35px;
            text-shadow: 0 0 15px #38bdf8;
        }

        p {
            color: #cbd5e1;
            margin-top: 20px;
            font-size: 16px;
        }

        .line {
            width: 100px;
            height: 4px;
            background: #38bdf8;
            margin: 20px auto;
            border-radius: 10px;
        }

        .status {
            display: inline-block;
            padding: 12px 22px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: bold;
        }

        .active {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
            border: 1px solid #22c55e;
        }

        .normal {
            background: rgba(148, 163, 184, 0.15);
            color: #cbd5e1;
            border: 1px solid #64748b;
        }

        @media (max-width: 500px) {
            h1 {
                font-size: 25px;
            }

            .container {
                padding: 35px 20px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h1>به API خوش آمدید</h1>

    <div class="line"></div>

    <?php if ($bypass): ?>

    <div class="status active">
        حالت توسعه‌دهنده فعال است
    </div>

    <p>
        شما در حال استفاده از حالت توسعه‌دهنده هستید.
    </p>

    <?php else: ?>

    <div class="status normal">
        حالت عادی
    </div>

    <p>
        همه چیز به‌صورت عادی در حال اجرا است.
    </p>

    <?php endif; ?>

</div>

</body>
</html>
