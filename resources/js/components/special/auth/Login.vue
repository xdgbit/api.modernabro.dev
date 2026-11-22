<template>

    <div class="admin-login">

        <div class="login-container">

            <div class="login-card">

                <!-- Header -->

                <div class="login-header">

                    <h1>
                        ورود به پنل مدیریت
                    </h1>

                    <p>
                        برای ورود اطلاعات حساب کاربری خود را وارد کنید.
                    </p>

                </div>


                <!-- Form -->

                <form @submit.prevent="login">

                    <!-- Username -->

                    <div class="form-group">

                        <label for="username">
                            نام کاربری
                        </label>

                        <input
                            id="username"
                            v-model="form.username"
                            type="text"
                            class="form-control"
                            autocomplete="username"
                            :disabled="loading"
                        >

                    </div>


                    <!-- Password -->

                    <div class="form-group">

                        <label for="password">
                            رمز عبور
                        </label>

                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="form-control"
                            autocomplete="current-password"
                            :disabled="loading"
                        >

                    </div>


                    <!-- Remember -->

                    <label class="remember">

                        <input
                            v-model="form.remember"
                            type="checkbox"
                            :disabled="loading"
                        >

                        <span>
                            مرا به خاطر بسپار
                        </span>

                    </label>


                    <!-- ALTCHA -->

                    <div class="altcha-wrapper">

                        <div class="altcha-title">
                            تأیید امنیتی
                        </div>

                        <div class="altcha-description">
                            برای ورود، تأیید امنیتی را انجام دهید.
                        </div>


                        <altcha-widget
                            ref="altchaWidget"
                            challenge="/altcha-challenge"
                            name="altcha"
                            @statechange="onAltchaStateChange"
                        />


                        <div
                            v-if="altchaVerified"
                            class="altcha-success"
                        >
                            تأیید امنیتی با موفقیت انجام شد.
                        </div>


                        <div
                            v-if="altchaError"
                            class="altcha-error"
                        >
                            {{ altchaError }}
                        </div>

                    </div>


                    <!-- Login Error -->

                    <div
                        v-if="error"
                        class="login-error"
                    >
                        {{ error }}
                    </div>


                    <!-- Login Button -->

                    <button
                        type="submit"
                        class="login-button"
                        :disabled="loading || !altchaVerified"
                    >

                        <span v-if="loading">
                            در حال ورود...
                        </span>

                        <span v-else>
                            ورود
                        </span>

                    </button>

                </form>

            </div>

        </div>

    </div>

</template>


<script>
import 'altcha'
import axios from 'axios'
import {useRouter} from 'vue-router'

export default {
    name: 'AdminLogin',

    setup()
    {
        const router = useRouter()

        return {
            router,
        }
    },

    data()
    {
        return {
            loading: false,
            error: null,

            altchaPayload: null,
            altchaVerified: false,
            altchaError: null,

            form: {
                username: '',
                password: '',
                remember: false,
            },
        }
    },

    methods: {

        /* =========================================
           ALTCHA State
        ========================================= */

        onAltchaStateChange(event)
        {
            const detail = event.detail

            if (detail.state === 'verified')
            {
                this.altchaPayload = detail.payload
                this.altchaVerified = true
                this.altchaError = null
                this.error = null

                return
            }

            this.altchaPayload = null
            this.altchaVerified = false

            if (detail.state === 'error')
            {
                this.altchaError =
                    detail.error ||
                    'تأیید امنیتی انجام نشد. دوباره تلاش کنید.'
            }
        },


        /* =========================================
           Login
        ========================================= */

        async login()
        {
            this.error = null

            if (this.loading)
            {
                return
            }

            // Username
            if (!this.form.username.trim())
            {
                this.error = 'نام کاربری را وارد کنید.'
                return
            }

            // Password
            if (!this.form.password)
            {
                this.error = 'رمز عبور را وارد کنید.'
                return
            }

            // ALTCHA
            if (!this.altchaVerified || !this.altchaPayload)
            {
                this.error = 'لطفاً ابتدا تأیید امنیتی را انجام دهید.'
                return
            }

            this.loading = true

            try
            {
                const response = await axios.post(
                    Routes.auth.admin.login.attempt,
                    {
                        username: this.form.username.trim(),
                        password: this.form.password,
                        remember: this.form.remember,
                        altcha: this.altchaPayload,
                    }
                )

                if (response.data.success)
                {
                    window.location.href = '/admin/dashboard'
                }

            } catch (error)
            {

                const status = error.response?.status
                const errors = error.response?.data?.errors

                if (status === 422)
                {

                    if (errors?.altcha)
                    {
                        this.error = errors.altcha[0]
                    }
                    else if (errors?.username)
                    {
                        this.error = errors.username[0]
                    }
                    else if (errors?.password)
                    {
                        this.error = errors.password[0]
                    }
                    else if (errors?.credential)
                    {
                        this.error = errors.credential[0]
                    }
                    else
                    {
                        this.error = 'نام کاربری یا رمز عبور صحیح نیست.'
                    }

                    this.resetAltcha()
                    return
                }

                if (status === 401)
                {
                    this.error = 'نام کاربری یا رمز عبور صحیح نیست.'
                    this.resetAltcha()
                    return
                }

                if (status === 429)
                {
                    this.error =
                        'تعداد تلاش‌های ورود بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.'

                    this.resetAltcha()
                    return
                }

                this.error = 'خطایی در ارتباط با سرور رخ داد.'

                console.error('Login error:', error)

            } finally
            {
                this.loading = false
            }
        },


        /* =========================================
           Reset ALTCHA
        ========================================= */

        resetAltcha()
        {
            this.altchaPayload = null
            this.altchaVerified = false
            this.altchaError = null

            this.$nextTick(() =>
            {
                this.$refs.altchaWidget?.reset()
            })
        },

    },
}
</script>


<style scoped>

.admin-login {

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    direction: rtl;

    background: #f5f7fb;

    padding: 30px;

}


.login-container {

    width: 100%;

    max-width: 430px;

}


.login-card {

    background: #ffffff;

    border-radius: 16px;

    padding: 40px;

    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);

    border: 1px solid #e9edf3;

}


.login-header {

    text-align: center;

    margin-bottom: 32px;

}


.login-header h1 {

    margin: 0 0 10px;

    font-size: 26px;

    font-weight: 700;

    color: #1f2937;

}


.login-header p {

    margin: 0;

    font-size: 14px;

    line-height: 1.8;

    color: #6b7280;

}


.form-group {

    margin-bottom: 22px;

}


.form-group label {

    display: block;

    margin-bottom: 8px;

    font-size: 14px;

    font-weight: 600;

    color: #374151;

}


.form-control {

    width: 100%;

    height: 48px;

    box-sizing: border-box;

    border: 1px solid #d9dee7;

    border-radius: 10px;

    padding: 0 14px;

    font-family: inherit;

    font-size: 14px;

    color: #1f2937;

    background: #ffffff;

    outline: none;

    transition: border-color 0.2s ease,
    box-shadow 0.2s ease;

}


.form-control:focus {

    border-color: #4f46e5;

    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.10);

}


.form-control:disabled {

    background: #f3f4f6;

    cursor: not-allowed;

}


.remember {

    display: flex;

    align-items: center;

    gap: 8px;

    margin-bottom: 22px;

    cursor: pointer;

    font-size: 13px;

    color: #6b7280;

}


.remember input {

    width: 16px;

    height: 16px;

    cursor: pointer;

}


/*
|--------------------------------------------------------------------------
| ALTCHA
|--------------------------------------------------------------------------
*/

.altcha-wrapper {

    margin-bottom: 22px;

    padding: 16px;

    border-radius: 12px;

    border: 1px solid #e5e7eb;

    background: #f8fafc;

}


.altcha-title {

    margin-bottom: 4px;

    font-size: 14px;

    font-weight: 600;

    color: #374151;

}


.altcha-description {

    margin-bottom: 12px;

    font-size: 12px;

    line-height: 1.8;

    color: #6b7280;

}


.altcha-success {

    margin-top: 8px;

    font-size: 12px;

    color: #16a34a;

}


.altcha-error {

    margin-top: 8px;

    font-size: 12px;

    line-height: 1.7;

    color: #dc2626;

}


/*
|--------------------------------------------------------------------------
| Error
|--------------------------------------------------------------------------
*/

.login-error {

    margin-bottom: 18px;

    padding: 12px 14px;

    border-radius: 8px;

    background: #fef2f2;

    border: 1px solid #fecaca;

    color: #dc2626;

    font-size: 13px;

    line-height: 1.8;

}


/*
|--------------------------------------------------------------------------
| Button
|--------------------------------------------------------------------------
*/

.login-button {

    width: 100%;

    height: 48px;

    border: 0;

    border-radius: 10px;

    background: #4f46e5;

    color: #ffffff;

    font-family: inherit;

    font-size: 15px;

    font-weight: 600;

    cursor: pointer;

    transition: background 0.2s ease,
    transform 0.1s ease;

}


.login-button:hover {

    background: #4338ca;

}


.login-button:active {

    transform: translateY(1px);

}


.login-button:disabled {

    opacity: 0.65;

    cursor: not-allowed;

}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 576px) {

    .admin-login {

        padding: 20px;

    }


    .login-card {

        padding: 28px 22px;

        border-radius: 12px;

    }


    .login-header h1 {

        font-size: 22px;

    }

}

</style>
