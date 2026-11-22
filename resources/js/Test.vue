<template>

    <div
        dir="rtl"
        class="min-h-screen bg-slate-100 p-8"
    >
        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="font-semibold text-slate-800">
                تست Pusher
            </div>

            <div class="mt-2 text-sm text-slate-600">
                آخرین رویداد:
                {{ lastPusherEvent?.data?.badge_type ?? 'هنوز رویدادی دریافت نشده' }}
            </div>
        </div>
        <div
            class="mx-auto max-w-xl rounded-2xl bg-white p-6 shadow-sm"
        >

            <h1 class="mb-6 text-lg font-bold text-slate-800">
                تست سیستم فرم
            </h1>


            <!-- Date -->

            <label class="mb-2 block text-sm font-medium text-slate-700">
                تاریخ
            </label>

            <AppDatePicker
                v-model="form.date"
                mode="date"
                output="gregorian"
                placeholder="تاریخ را انتخاب کنید"
                min-date="today"
                max-date="2026-10-10"
            />


            <!-- Time -->

            <div class="mt-6">

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    ساعت
                </label>

                <AppDatePicker
                    v-model="form.time"
                    mode="time"
                    placeholder="ساعت و دقیقه را انتخاب کنید"
                />

            </div>


            <!-- Datetime -->

            <div class="mt-6">

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    تاریخ و زمان
                </label>

                <AppDatePicker
                    v-model="form.datetime"
                    mode="datetime"
                    output="gregorian"
                    :enable-seconds="true"
                    placeholder="تاریخ و زمان را انتخاب کنید"
                />

            </div>


            <!-- Toggle -->

            <div class="mt-6">

                <AppToggle
                    v-model="form.send_sms"
                    label="ارسال پیامک"
                    icon="mobile"
                    description="ارسال پیامک اطلاع‌رسانی برای کاربر"
                />

            </div>


            <!-- Upload -->

            <div class="mt-6">

                <AppUploadFile
                    v-model="form.avatar"
                    label="تصویر پروفایل"
                    accept="image/jpeg,image/png,image/webp"
                    :max-size="4 * 1024 * 1024"
                />

            </div>


            <div class="mt-6">

                <AppUploadFile
                    v-model="form.images"
                    label="تصاویر گالری"
                    accept="image/jpeg,image/png,image/webp"
                    :max-size="2 * 1024 * 1024"
                    :max-files="10"
                    multiple
                />

            </div>


            <!-- Price -->

            <div class="mt-6">

                <AppInputNumeric
                    v-model="form.price"
                    field="price"
                    label="قیمت"
                    placeholder="مثلاً 1,500,000"
                />

            </div>


            <!-- Username -->

            <div class="mt-6">

                <AppInput
                    v-model="form.username"
                    field="username"
                    label="نام کاربری"
                    placeholder="مثلاً admin"
                    autocomplete="username"
                />

            </div>


            <!-- User -->

            <div class="mt-6">

                <AppSelect
                    v-model="form.user_id"
                    field="user_id"
                    label="کاربر"
                    placeholder="کاربر را انتخاب کنید"
                    search-placeholder="نام یا شماره موبایل..."
                    :options="users"
                    value-key="id"
                    :option-label="
                        user => `${user.first_name} ${user.last_name}`
                    "
                    :search-keys="[
                        'first_name',
                        'last_name',
                        'mobile',
                    ]"
                />

            </div>


            <!-- Email -->

            <div class="mt-6">

                <AppInput
                    v-model="form.email"
                    field="email"
                    label="ایمیل"
                    type="email"
                    placeholder="example@gmail.com"
                    autocomplete="email"
                />

            </div>


            <!-- Password -->

            <div class="mt-6">

                <AppPassword
                    v-model="form.password"
                    field="password"
                    label="رمز عبور"
                    placeholder="رمز عبور"
                />

            </div>


            <!-- SMS -->

            <div
                class="mt-6 flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-all duration-200 hover:border-teal-200 hover:shadow-md"
                @click="form.send_sms = !form.send_sms"
            >

                <div class="flex items-center gap-3">

                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-all duration-300"
                        :class="
                            form.send_sms
                                ? 'bg-teal-100 text-teal-600'
                                : 'bg-slate-100 text-slate-400'
                        "
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4v-4z"
                            />

                        </svg>

                    </div>


                    <div>

                        <div class="font-semibold text-slate-800">
                            ارسال پیامک
                        </div>

                        <div class="mt-1 text-xs text-slate-500">
                            ارسال پیامک اطلاع‌رسانی برای کاربر
                        </div>

                    </div>

                </div>


                <div
                    class="relative h-7 w-12 shrink-0 rounded-full transition-colors duration-300"
                    :class="
                        form.send_sms
                            ? 'bg-teal-600'
                            : 'bg-slate-300'
                    "
                >

                    <div
                        class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-md transition-transform duration-300"
                        :class="
                            form.send_sms
                                ? 'translate-x-5'
                                : 'translate-x-0'
                        "
                    ></div>

                </div>

            </div>


            <!-- ALTCHA -->

            <div
                class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4"
            >

                <div class="mb-3">

                    <div class="font-semibold text-slate-800">
                        تأیید امنیتی
                    </div>

                    <div class="mt-1 text-xs text-slate-500">
                        برای ادامه، تأیید امنیتی را انجام دهید.
                    </div>

                </div>


                <altcha-widget
                    ref="altchaWidget"
                    challenge="/altcha-challenge"
                    name="altcha"
                    type="checkbox"
                    language="fa"
                    @statechange="onAltchaStateChange"
                />


                <div
                    v-if="altchaVerified"
                    class="mt-3 text-sm text-green-600"
                >
                    تأیید امنیتی با موفقیت انجام شد.
                </div>


                <div
                    v-if="altchaError"
                    class="mt-3 text-sm text-red-600"
                >
                    {{ altchaError }}
                </div>

            </div>


            <!-- Buttons -->

            <div class="mt-6 flex flex-wrap gap-2">

                <button
                    type="button"
                    class="rounded-xl bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700"
                    @click="validate"
                >
                    Validation
                </button>


                <button
                    type="button"
                    class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="loading"
                    @click="testApi"
                >
                    {{
                        loading
                            ? 'در حال ارسال...'
                            : 'Test API Error'
                    }}
                </button>


                <button
                    type="button"
                    class="rounded-xl bg-slate-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
                    @click="clear"
                >
                    پاک کردن
                </button>


                <button
                    type="button"
                    class="rounded-xl bg-purple-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-purple-700"
                    @click="testConfirm"
                >
                    Test Confirm
                </button>


                <button
                    type="button"
                    class="rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700"
                    @click="successNotify"
                >
                    Test Success Notify
                </button>


                <button
                    type="button"
                    class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                    @click="infoNotify"
                >
                    Test Info Notify
                </button>


                <button
                    type="button"
                    class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700"
                    @click="dangerNotify"
                >
                    Test Danger Notify
                </button>

            </div>

        </div>

    </div>

</template>


<script setup>

import {
    reactive,
    ref,
    onMounted,
    onBeforeUnmount
} from 'vue'

import 'altcha'

import axios from 'axios'

import {modal} from '@/modal'

import {useFormErrors} from '@/composables/useFormErrors'


import {subscribeExpertBadgeUpdates} from '@/services/pushEvent'
/*
|--------------------------------------------------------------------------
| ALTCHA
|--------------------------------------------------------------------------
*/
let unsubscribeBadgeUpdates = null

const lastPusherEvent = ref(null)

const altchaWidget = ref(null)

const altchaPayload = ref(null)

const altchaVerified = ref(false)

const altchaError = ref(null)


onMounted(() =>
{
    console.log('Vue component mounted')
    console.log('Expert channel:', window.expert_common_channel)
    console.log('Pusher connection:', window.pusher_connection)

    unsubscribeBadgeUpdates = subscribeExpertBadgeUpdates((res) => {
        console.log('ExpertBadgeUpdate received:', res)

        lastPusherEvent.value = res

        $toast.info('رویداد دریافت شد .')
    })
})

onBeforeUnmount(() =>
{
    unsubscribeBadgeUpdates?.()
})

const onAltchaStateChange = (event) =>
{
    const detail = event.detail

    console.log('ALTCHA state:', detail)


    if (detail.state === 'verified')
    {
        altchaPayload.value = detail.payload

        altchaVerified.value = true

        altchaError.value = null

        return
    }


    altchaPayload.value = null

    altchaVerified.value = false


    if (detail.state === 'error')
    {
        altchaError.value =
            detail.error ||
            'Verification failed. Try again later.'
    }
}


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = reactive({

    date: '',

    time: '',

    datetime: '',

    username: '',

    email: '',

    password: '',

    user_id: null,

    avatar: null,

    images: [],

    price: null,

    send_sms: false,

    expired_at: '',

})


/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
*/

const users = [

    {
        id: 1,
        first_name: 'علی',
        last_name: 'رضایی',
        mobile: '09121234567',
    },

    {
        id: 2,
        first_name: 'محمد',
        last_name: 'احمدی',
        mobile: '09351234567',
    },

    {
        id: 3,
        first_name: 'رضا',
        last_name: 'کریمی',
        mobile: '09199876543',
    },

    {
        id: 4,
        first_name: 'امیر',
        last_name: 'محمدی',
        mobile: '09211234567',
    },

]


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loading = ref(false)


/*
|--------------------------------------------------------------------------
| Form Errors
|--------------------------------------------------------------------------
*/

const {
    setValidationError,
    setApiErrors,
    clearAll,
} = useFormErrors()


/*
|--------------------------------------------------------------------------
| Notify
|--------------------------------------------------------------------------
*/

const successNotify = () =>
{
    $toast.success(
        'اطلاعات با موفقیت ذخیره شد'
    )
}


const infoNotify = () =>
{
    $toast.info(
        'عملیات با موفقیت انجام شد'
    )
}


const dangerNotify = () =>
{
    $toast.error(
        'عملیات با خطا مواجه شد'
    )
}


/*
|--------------------------------------------------------------------------
| Confirm
|--------------------------------------------------------------------------
*/

const testConfirm = async () =>
{
    const result = await modal.confirm({

        title: 'حذف اطلاعات',

        message: 'آیا از انجام این عملیات مطمئن هستید؟',

    })


    if (result)
    {
        $toast.success(
            'عملیات تأیید شد'
        )

        return
    }


    $toast.info(
        'عملیات لغو شد'
    )
}


/*
|--------------------------------------------------------------------------
| Local Validation
|--------------------------------------------------------------------------
*/

const validate = () =>
{
    clearAll()

    let hasError = false


    if (!form.username.trim())
    {
        setValidationError(
            'username',
            'نام کاربری الزامی است.'
        )

        hasError = true
    }


    if (!form.email.trim())
    {
        setValidationError(
            'email',
            'ایمیل الزامی است.'
        )

        hasError = true
    }


    if (!form.password)
    {
        setValidationError(
            'password',
            'رمز عبور الزامی است.'
        )

        hasError = true
    }


    if (hasError)
    {
        $toast.error(
            'لطفاً خطاهای فرم را برطرف کنید.'
        )

        return
    }


    $toast.success(
        'فرم معتبر است.'
    )
}


/*
|--------------------------------------------------------------------------
| API Test
|--------------------------------------------------------------------------
*/

const testApi = async () =>
{
    clearAll()


    if (!altchaPayload.value)
    {
        $toast.error(
            'لطفاً ابتدا تأیید امنیتی را انجام دهید.'
        )

        return
    }


    loading.value = true


    try
    {
        await axios.post(
            '/test/validation',
            {
                ...form,

                altcha: altchaPayload.value,
            }
        )


        $toast.success(
            'اطلاعات صحیح است.'
        )
    } catch (error)
    {
        console.error(
            'API error:',
            error
        )


        if (error.response?.status === 422)
        {
            setApiErrors(
                error.response.data.errors ?? {}
            )

            $toast.error(
                'اطلاعات وارد شده صحیح نیست.'
            )

            return
        }


        $toast.error(
            'خطایی در ارتباط با سرور رخ داد.'
        )
    } finally
    {
        loading.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Clear
|--------------------------------------------------------------------------
*/

const clear = () =>
{
    form.date = ''

    form.time = ''

    form.datetime = ''

    form.username = ''

    form.email = ''

    form.password = ''

    form.user_id = null

    form.avatar = null

    form.images = []

    form.price = null

    form.send_sms = false

    form.expired_at = ''


    clearAll()


    altchaPayload.value = null

    altchaVerified.value = false

    altchaError.value = null


    altchaWidget.value?.reset()


    $toast.info(
        'فرم پاک شد.'
    )
}

</script>