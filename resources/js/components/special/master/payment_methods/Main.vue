<script setup>
import {onMounted, ref} from 'vue'
import {
    CreditCard,
    Landmark,
    Globe,
    Save,
    Plus,
    Trash2,
    Building2,
    CircleDollarSign,
    ShieldCheck,
    CheckCircle2,
    AlertCircle,
    Loader2,
} from 'lucide-vue-next'
import axios from 'axios'

// ======================================================
// State
// ======================================================

const activeTab = ref('bank')
const loading = ref(false)
const saving = ref(false)

// پیام نتیجه (موفق / خطا)
const message = ref({type: '', text: ''})
const errors = ref({})

const bankMethods = ref([
    {
        id: 1,
        title: 'کارت‌به‌کارت',
        enabled: true,
        description: 'انتقال وجه از طریق شماره کارت بانکی',
    },
])

const bankInfo = ref({
    bankName: '',
    accountOwner: '',
    cardNumber: '',
    accountNumber: '',
    sheba: '',
})

const gateways = ref([
    {
        id: 1,
        title: 'درگاه پرداخت آنلاین',
        provider: '',
        merchantId: '',
        terminalId: '',
        enabled: false,
        description: 'پرداخت آنلاین از طریق درگاه بانکی',
    },
])

// ======================================================
// Helpers
// ======================================================

// تبدیل اعداد فارسی/عربی به انگلیسی
function toEnglishDigits(value)
{
    return String(value ?? '')
    .replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
    .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
}

function onlyDigits(value)
{
    return toEnglishDigits(value).replace(/\D/g, '')
}

// 6037991234567890 => 6037-9912-3456-7890
function formatCardNumber(value)
{
    return onlyDigits(value)
    .slice(0, 16)
    .replace(/(\d{4})(?=\d)/g, '$1-')
}

function onCardInput(e)
{
    bankInfo.value.cardNumber = formatCardNumber(e.target.value)
}

function showMessage(type, text)
{
    message.value = {type, text}

    if (type === 'success')
    {
        setTimeout(() =>
        {
            message.value = {type: '', text: ''}
        }, 4000)
    }
}

// ======================================================
// Get Data
// ======================================================

async function getData()
{
    loading.value = true

    try
    {
        const response = await axios.get(
            Routes.admin.paymentMethod.get_data
        )

        if (response.data.success)
        {
            const data = response.data.data ?? {}

            bankInfo.value = {
                bankName: data.bank_name ?? '',
                accountOwner: data.account_holder ?? '',
                cardNumber: formatCardNumber(data.card_number ?? ''),
                accountNumber: data.account_number ?? '',
                sheba: data.iban ?? '',
            }

            // اگر از سمت سرور روش‌های انتقال آمده بود جایگزین پیش‌فرض شود
            if (Array.isArray(data.bank_methods) && data.bank_methods.length)
            {
                bankMethods.value = data.bank_methods.map((m, i) => ({
                    id: m.id ?? Date.now() + i,
                    title: m.title ?? '',
                    description: m.description ?? '',
                    enabled: !!m.enabled,
                }))
            }
        }
        else
        {
            showMessage('error', response.data.message ?? 'خطا در دریافت اطلاعات')
        }
    }
    catch (error)
    {
        console.error('خطا در دریافت روش‌های پرداخت:', error)
        showMessage('error', 'خطا در دریافت اطلاعات روش‌های پرداخت')
    }
    finally
    {
        loading.value = false
    }
}

// ======================================================
// Validation
// ======================================================

function validate()
{
    const e = {}
    const info = bankInfo.value

    const card = onlyDigits(info.cardNumber)
    const account = onlyDigits(info.accountNumber)
    const sheba = toEnglishDigits(info.sheba).replace(/\s/g, '').toUpperCase()

    if (!info.bankName.trim())
        e.bankName = 'نام بانک را وارد کنید'

    if (!info.accountOwner.trim())
        e.accountOwner = 'نام صاحب حساب را وارد کنید'

    if (card && card.length !== 16)
        e.cardNumber = 'شماره کارت باید ۱۶ رقم باشد'

    if (account && account.length < 6)
        e.accountNumber = 'شماره حساب معتبر نیست'

    if (sheba && !/^IR\d{24}$/.test(sheba))
        e.sheba = 'شماره شبا باید با IR شروع شود و ۲۶ کاراکتر باشد'

    if (!card && !account && !sheba)
        e.cardNumber = 'حداقل یکی از شماره کارت، شماره حساب یا شبا را وارد کنید'

    if (bankMethods.value.some(m => !m.title.trim()))
        e.bankMethods = 'عنوان همه روش‌های انتقال باید پر شود'

    errors.value = e

    return Object.keys(e).length === 0
}

// ======================================================
// Save
// ======================================================

async function saveSettings()
{
    message.value = {type: '', text: ''}

    if (!validate())
    {
        showMessage('error', 'لطفاً خطاهای فرم را برطرف کنید')
        activeTab.value = 'bank'
        return
    }

    saving.value = true

    try
    {
        const payload = {
            bank_name: bankInfo.value.bankName.trim(),
            account_holder: bankInfo.value.accountOwner.trim(),
            card_number: onlyDigits(bankInfo.value.cardNumber),
            account_number: onlyDigits(bankInfo.value.accountNumber),
            iban: toEnglishDigits(bankInfo.value.sheba).replace(/\s/g, '').toUpperCase(),
            bank_methods: bankMethods.value.map(m => ({
                title: m.title.trim(),
                description: m.description.trim(),
                enabled: m.enabled,
            })),
        }

        const response = await axios.post(
            Routes.admin.paymentMethod.save,
            payload
        )

        if (response.data.success)
        {
            showMessage('success', response.data.message ?? 'تنظیمات با موفقیت ذخیره شد')
            await getData()
        }
        else
        {
            showMessage('error', response.data.message ?? 'ذخیره تنظیمات انجام نشد')
        }
    }
    catch (error)
    {
        console.error('خطا در ذخیره روش‌های پرداخت:', error)

        // خطاهای اعتبارسنجی Laravel (422)
        if (error.response?.status === 422)
        {
            const serverErrors = error.response.data.errors ?? {}
            const map = {
                bank_name: 'bankName',
                account_holder: 'accountOwner',
                card_number: 'cardNumber',
                account_number: 'accountNumber',
                iban: 'sheba',
            }

            const e = {}
            Object.keys(serverErrors).forEach(key =>
            {
                e[map[key] ?? key] = serverErrors[key][0]
            })
            errors.value = e

            showMessage('error', error.response.data.message ?? 'اطلاعات واردشده معتبر نیست')
        }
        else
        {
            showMessage('error', error.response?.data?.message ?? 'خطا در ارتباط با سرور')
        }
    }
    finally
    {
        saving.value = false
    }
}

// ======================================================
// Bank Methods
// ======================================================

function addBankMethod()
{
    bankMethods.value.push({
        id: Date.now(),
        title: '',
        enabled: true,
        description: '',
    })
}

function removeBankMethod(id)
{
    bankMethods.value = bankMethods.value.filter(
        method => method.id !== id
    )
}

// ======================================================
// Gateways (فعلاً غیرفعال)
// ======================================================

function addGateway()
{
    gateways.value.push({
        id: Date.now(),
        title: 'درگاه پرداخت جدید',
        provider: '',
        merchantId: '',
        terminalId: '',
        enabled: false,
        description: '',
    })
}

function removeGateway(id)
{
    gateways.value = gateways.value.filter(
        gateway => gateway.id !== id
    )
}

// ======================================================
// Mounted
// ======================================================

onMounted(() =>
{
    getData()
})
</script>

<template>
    <div dir="rtl" class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-800">
                    روش‌های پرداخت
                </h1>

                <p class="mt-2 text-sm text-slate-400">
                    مدیریت روش‌های دریافت وجه و تنظیمات پرداخت
                </p>
            </div>

            <button
                type="button"
                :disabled="saving || loading"
                @click="saveSettings"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <Loader2 v-if="saving" :size="18" class="animate-spin"/>
                <Save v-else :size="18"/>
                {{ saving ? 'در حال ذخیره ...' : 'ذخیره تنظیمات' }}
            </button>
        </div>

        <!-- Message -->
        <div
            v-if="message.text"
            :class="[
                'flex items-center gap-3 rounded-xl p-4 text-sm',
                message.type === 'success'
                    ? 'bg-emerald-50 text-emerald-800'
                    : 'bg-red-50 text-red-800'
            ]"
        >
            <CheckCircle2 v-if="message.type === 'success'" :size="20" class="shrink-0"/>
            <AlertCircle v-else :size="20" class="shrink-0"/>
            <span>{{ message.text }}</span>
        </div>

        <!-- Tabs -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap gap-1 border-b border-slate-200 bg-slate-50 p-2">
                <button
                    type="button"
                    @click="activeTab = 'bank'"
                    :class="[
                        'inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition',
                        activeTab === 'bank'
                            ? 'bg-white text-teal-700 shadow-sm ring-1 ring-slate-200'
                            : 'text-slate-500 hover:bg-white hover:text-slate-800'
                    ]"
                >
                    <Landmark :size="18"/>
                    انتقال بانکی
                </button>

                <button
                    type="button"
                    @click="activeTab = 'gateway'"
                    :class="[
                        'inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition',
                        activeTab === 'gateway'
                            ? 'bg-white text-teal-700 shadow-sm ring-1 ring-slate-200'
                            : 'text-slate-500 hover:bg-white hover:text-slate-800'
                    ]"
                >
                    <Globe :size="18"/>
                    <span>درگاه پرداخت آنلاین</span>

                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-600">
                        غیرفعال
                    </span>
                </button>
            </div>

            <div class="relative p-5 sm:p-6">

                <!-- Loading overlay -->
                <div
                    v-if="loading"
                    class="absolute inset-0 z-20 flex items-center justify-center bg-white/70 backdrop-blur-[1px]"
                >
                    <div class="flex items-center gap-2 text-sm text-slate-500">
                        <Loader2 :size="20" class="animate-spin"/>
                        در حال بارگذاری ...
                    </div>
                </div>

                <!-- Bank Transfer Tab -->
                <div v-if="activeTab === 'bank'" class="space-y-6">

                    <div>
                        <h2 class="text-lg font-bold text-slate-800">
                            تنظیمات انتقال بانکی
                        </h2>
                        <p class="mt-1 text-sm text-slate-400">
                            اطلاعات حساب بانکی و روش‌های انتقال وجه را مدیریت کنید.
                        </p>
                    </div>

                    <!-- Bank Account Information -->
                    <div class="rounded-xl border border-slate-200 p-4 sm:p-5">
                        <div class="mb-5 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <Building2 :size="20"/>
                            </div>

                            <div>
                                <h3 class="font-semibold text-slate-800">
                                    اطلاعات حساب بانکی
                                </h3>
                                <p class="mt-1 text-xs text-slate-400">
                                    اطلاعاتی که برای واریز وجه به مشتری نمایش داده می‌شود
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">
                                    نام بانک
                                </label>
                                <input
                                    v-model="bankInfo.bankName"
                                    type="text"
                                    placeholder="مثلاً بانک ملت"
                                    :class="[
                                        'w-full rounded-xl border px-3 py-2.5 text-sm outline-none transition focus:ring-2',
                                        errors.bankName
                                            ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
                                            : 'border-slate-200 focus:border-teal-500 focus:ring-teal-100'
                                    ]"
                                />
                                <p v-if="errors.bankName" class="mt-1 text-xs text-red-600">{{ errors.bankName }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">
                                    نام صاحب حساب
                                </label>
                                <input
                                    v-model="bankInfo.accountOwner"
                                    type="text"
                                    placeholder="نام و نام خانوادگی یا نام شرکت"
                                    :class="[
                                        'w-full rounded-xl border px-3 py-2.5 text-sm outline-none transition focus:ring-2',
                                        errors.accountOwner
                                            ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
                                            : 'border-slate-200 focus:border-teal-500 focus:ring-teal-100'
                                    ]"
                                />
                                <p v-if="errors.accountOwner" class="mt-1 text-xs text-red-600">{{ errors.accountOwner }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">
                                    شماره کارت
                                </label>
                                <input
                                    :value="bankInfo.cardNumber"
                                    @input="onCardInput"
                                    type="text"
                                    dir="ltr"
                                    inputmode="numeric"
                                    maxlength="19"
                                    placeholder="6037-0000-0000-0000"
                                    :class="[
                                        'w-full rounded-xl border px-3 py-2.5 text-left text-sm outline-none transition focus:ring-2',
                                        errors.cardNumber
                                            ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
                                            : 'border-slate-200 focus:border-teal-500 focus:ring-teal-100'
                                    ]"
                                />
                                <p v-if="errors.cardNumber" class="mt-1 text-xs text-red-600">{{ errors.cardNumber }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">
                                    شماره حساب
                                </label>
                                <input
                                    v-model="bankInfo.accountNumber"
                                    type="text"
                                    dir="ltr"
                                    inputmode="numeric"
                                    placeholder="شماره حساب بانکی"
                                    :class="[
                                        'w-full rounded-xl border px-3 py-2.5 text-left text-sm outline-none transition focus:ring-2',
                                        errors.accountNumber
                                            ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
                                            : 'border-slate-200 focus:border-teal-500 focus:ring-teal-100'
                                    ]"
                                />
                                <p v-if="errors.accountNumber" class="mt-1 text-xs text-red-600">{{ errors.accountNumber }}</p>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-slate-700">
                                    شماره شبا
                                </label>
                                <input
                                    v-model="bankInfo.sheba"
                                    type="text"
                                    dir="ltr"
                                    maxlength="26"
                                    placeholder="IR000000000000000000000000"
                                    :class="[
                                        'w-full rounded-xl border px-3 py-2.5 text-left text-sm outline-none transition focus:ring-2',
                                        errors.sheba
                                            ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
                                            : 'border-slate-200 focus:border-teal-500 focus:ring-teal-100'
                                    ]"
                                />
                                <p v-if="errors.sheba" class="mt-1 text-xs text-red-600">{{ errors.sheba }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Transfer Methods -->
<!--                    <div class="space-y-4">-->
<!--                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">-->
<!--                            <div>-->
<!--                                <h3 class="font-semibold text-slate-800">-->
<!--                                    روش‌های انتقال وجه-->
<!--                                </h3>-->
<!--                                <p class="mt-1 text-sm text-slate-400">-->
<!--                                    روش‌های قابل استفاده برای پرداخت بانکی-->
<!--                                </p>-->
<!--                            </div>-->

<!--                            <button-->
<!--                                type="button"-->
<!--                                @click="addBankMethod"-->
<!--                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"-->
<!--                            >-->
<!--                                <Plus :size="17"/>-->
<!--                                افزودن روش-->
<!--                            </button>-->
<!--                        </div>-->

<!--                        <p v-if="errors.bankMethods" class="text-xs text-red-600">{{ errors.bankMethods }}</p>-->

<!--                        <div-->
<!--                            v-for="method in bankMethods"-->
<!--                            :key="method.id"-->
<!--                            class="rounded-xl border border-slate-200 p-4"-->
<!--                        >-->
<!--                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">-->
<!--                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">-->
<!--                                    <CreditCard :size="20"/>-->
<!--                                </div>-->

<!--                                <div class="min-w-0 flex-1 space-y-3">-->
<!--                                    <div class="grid gap-3 sm:grid-cols-2">-->
<!--                                        <div>-->
<!--                                            <label class="mb-1.5 block text-xs font-medium text-slate-500">-->
<!--                                                عنوان روش-->
<!--                                            </label>-->
<!--                                            <input-->
<!--                                                v-model="method.title"-->
<!--                                                type="text"-->
<!--                                                placeholder="عنوان روش انتقال"-->
<!--                                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-teal-500"-->
<!--                                            />-->
<!--                                        </div>-->

<!--                                        <div>-->
<!--                                            <label class="mb-1.5 block text-xs font-medium text-slate-500">-->
<!--                                                توضیحات-->
<!--                                            </label>-->
<!--                                            <input-->
<!--                                                v-model="method.description"-->
<!--                                                type="text"-->
<!--                                                placeholder="توضیحات روش انتقال"-->
<!--                                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-teal-500"-->
<!--                                            />-->
<!--                                        </div>-->
<!--                                    </div>-->

<!--                                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-600">-->
<!--                                        <input-->
<!--                                            v-model="method.enabled"-->
<!--                                            type="checkbox"-->
<!--                                            class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500"-->
<!--                                        />-->
<!--                                        فعال بودن روش-->
<!--                                    </label>-->
<!--                                </div>-->

<!--                                <button-->
<!--                                    type="button"-->
<!--                                    @click="removeBankMethod(method.id)"-->
<!--                                    class="inline-flex items-center justify-center self-start rounded-lg p-2 text-red-500 transition hover:bg-red-50"-->
<!--                                    title="حذف روش"-->
<!--                                >-->
<!--                                    <Trash2 :size="18"/>-->
<!--                                </button>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

                    <div class="flex items-start gap-3 rounded-xl bg-amber-50 p-4 text-amber-800">
                        <ShieldCheck :size="20" class="mt-0.5 shrink-0"/>
                        <p class="text-sm leading-6">
                            برای انتقال بانکی، تأیید پرداخت باید بر اساس رسید معتبر و بررسی تراکنش انجام شود.
                            فعال‌کردن یک روش به‌تنهایی پرداخت را تأیید نمی‌کند.
                        </p>
                    </div>
                </div>

                <!-- Online Gateway Tab (غیرفعال) -->
                <div
                    v-else-if="activeTab === 'gateway'"
                    class="relative min-h-[420px] overflow-hidden rounded-xl"
                >
                    <!-- محتوای غیرفعال -->
                    <div
                        class="pointer-events-none select-none space-y-6 blur-md opacity-40"
                        aria-hidden="true"
                        inert
                    >
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">
                                تنظیمات درگاه پرداخت آنلاین
                            </h2>
                            <p class="mt-1 text-sm text-slate-400">
                                مشخصات درگاه‌های بانکی مورد استفاده در سایت را مدیریت کنید.
                            </p>
                        </div>

                        <div class="flex items-start gap-3 rounded-xl bg-blue-50 p-4 text-blue-800">
                            <Globe :size="20" class="mt-0.5 shrink-0"/>
                            <p class="text-sm leading-6">
                                برای اتصال واقعی، باید درگاه بانکی انتخاب شود و اطلاعات پذیرنده از طریق API سمت سرور اعتبارسنجی و استفاده شود.
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="font-semibold text-slate-800">
                                    فهرست درگاه‌ها
                                </h3>
                                <p class="mt-1 text-sm text-slate-400">
                                    امکان تعریف و فعال‌سازی درگاه‌های مختلف
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="addGateway"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                            >
                                <Plus :size="17"/>
                                افزودن درگاه
                            </button>
                        </div>

                        <div
                            v-for="gateway in gateways"
                            :key="gateway.id"
                            class="rounded-xl border border-slate-200 p-4 sm:p-5"
                        >
                            <div class="mb-5 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                                        <CircleDollarSign :size="21"/>
                                    </div>

                                    <div>
                                        <h3 class="font-semibold text-slate-800">
                                            {{ gateway.title }}
                                        </h3>
                                        <p class="mt-1 text-xs text-slate-400">
                                            تنظیمات اتصال درگاه
                                        </p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="removeGateway(gateway.id)"
                                    class="rounded-lg p-2 text-red-500 transition hover:bg-red-50"
                                    title="حذف درگاه"
                                >
                                    <Trash2 :size="18"/>
                                </button>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        نام درگاه
                                    </label>
                                    <input
                                        v-model="gateway.title"
                                        type="text"
                                        placeholder="مثلاً درگاه بانکی"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                                    />
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        ارائه‌دهنده درگاه
                                    </label>
                                    <input
                                        v-model="gateway.provider"
                                        type="text"
                                        placeholder="نام شرکت ارائه‌دهنده"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                                    />
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        شناسه پذیرنده
                                    </label>
                                    <input
                                        v-model="gateway.merchantId"
                                        type="text"
                                        dir="ltr"
                                        placeholder="Merchant ID"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-left text-sm outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                                    />
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        شماره ترمینال
                                    </label>
                                    <input
                                        v-model="gateway.terminalId"
                                        type="text"
                                        dir="ltr"
                                        placeholder="Terminal ID"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-left text-sm outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                                    />
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        توضیحات
                                    </label>
                                    <input
                                        v-model="gateway.description"
                                        type="text"
                                        placeholder="توضیحات درگاه"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                                    />
                                </div>
                            </div>

                            <div class="mt-5 border-t border-slate-100 pt-4">
                                <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700">
                                    <input
                                        v-model="gateway.enabled"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                                    />
                                    فعال بودن درگاه
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- پیام غیرفعال -->
                    <div class="absolute inset-0 z-10 flex items-center justify-center">
                        <div class="rounded-2xl border border-slate-200 bg-white/95 px-8 py-6 text-center shadow-lg backdrop-blur-sm">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                                <Globe :size="28"/>
                            </div>

                            <h3 class="mt-4 text-lg font-bold text-slate-800">
                                درگاه پرداخت آنلاین
                            </h3>

                            <span class="mt-3 inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                غیرفعال
                            </span>

                            <p class="mt-3 text-sm text-slate-500">
                                این بخش بعداز اینماد گرفتن فعال خواهد شد
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>