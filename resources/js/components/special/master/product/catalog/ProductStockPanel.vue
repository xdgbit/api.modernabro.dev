<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import {
    PackagePlus,
    PackageMinus,
    PackageCheck,
    AlertTriangle,
    PackageX,
} from 'lucide-vue-next'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    productId: {
        type: [Number, String],
        required: true,
    },

    jsPanelVariableName: {
        type: String,
        required: true,
    },

    onSaved: {
        type: Function,
        default: null,
    },
})


/*
|--------------------------------------------------------------------------
| Constants
|--------------------------------------------------------------------------
*/

const ACTIONS = [
    { value: 'increase', label: 'افزایش موجودی', icon: PackagePlus },
    { value: 'decrease', label: 'کاهش موجودی', icon: PackageMinus },
    { value: 'set', label: 'تعیین موجودی', icon: PackageCheck },
]

const inputClass =
    'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100 disabled:cursor-not-allowed disabled:bg-slate-100'


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const errors = ref({})

const product = ref(null)

const form = ref({
    action: 'increase',
    quantity: 1,
    low_stock_threshold: 0,
})


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const currentStock = computed(() => Number(product.value?.stock ?? 0))

const threshold = computed(() =>
    Number(form.value.low_stock_threshold || 0)
)

// موجودی بعد از اعمال تغییر، فقط برای نمایش پیش‌نمایش
const projectedStock = computed(() =>
{
    const quantity = Number(form.value.quantity || 0)

    switch (form.value.action)
    {
        case 'increase':
            return currentStock.value + quantity
        case 'decrease':
            return currentStock.value - quantity
        case 'set':
            return quantity
        default:
            return currentStock.value
    }
})

const projectedStatus = computed(() =>
{
    if (projectedStock.value < 0)
    {
        return { label: 'نامعتبر', class: 'bg-rose-100 text-rose-700', icon: PackageX }
    }

    if (projectedStock.value === 0)
    {
        return { label: 'ناموجود', class: 'bg-rose-100 text-rose-700', icon: PackageX }
    }

    if (projectedStock.value <= threshold.value)
    {
        return { label: 'کم‌موجودی', class: 'bg-amber-100 text-amber-700', icon: AlertTriangle }
    }

    return { label: 'موجود', class: 'bg-emerald-100 text-emerald-700', icon: PackageCheck }
})

const isDecreaseTooMuch = computed(() =>
    form.value.action === 'decrease' &&
    Number(form.value.quantity || 0) > currentStock.value
)

const hasChanged = computed(() =>
{
    if (form.value.action === 'set')
    {
        return projectedStock.value !== currentStock.value ||
            threshold.value !== Number(product.value?.low_stock_threshold ?? 0)
    }

    return Number(form.value.quantity || 0) > 0 ||
        threshold.value !== Number(product.value?.low_stock_threshold ?? 0)
})

const canSave = computed(() =>
    !saving.value &&
    !loading.value &&
    !!product.value &&
    !isDecreaseTooMuch.value &&
    projectedStock.value >= 0 &&
    hasChanged.value
)

const quantityLabel = computed(() =>
{
    if (form.value.action === 'set')
    {
        return 'موجودی جدید'
    }

    return 'تعداد'
})


/*
|--------------------------------------------------------------------------
| Format
|--------------------------------------------------------------------------
*/

function formatNumber(value)
{
    return Number(value ?? 0).toLocaleString('fa-IR')
}


/*
|--------------------------------------------------------------------------
| Load Product
|--------------------------------------------------------------------------
*/

async function loadProduct()
{
    loading.value = true
    error.value = null

    try
    {
        const response = await axios.post(
            Routes.product.jspanel,
            {
                product_id: props.productId,
            }
        )

        const data = response.data.data ?? {}

        product.value = data.product ?? null

        if (product.value)
        {
            form.value.low_stock_threshold =
                Number(product.value.low_stock_threshold ?? 0)
        }
    }
    catch (err)
    {
        console.error('خطا در دریافت اطلاعات محصول:', err)

        error.value =
            err?.response?.data?.message ??
            'خطا در دریافت اطلاعات محصول.'
    }
    finally
    {
        loading.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function validate()
{
    error.value = null

    if (!form.value.action)
    {
        error.value = 'نوع تغییر موجودی را انتخاب کنید.'
        return false
    }

    if (form.value.quantity === '' || Number(form.value.quantity) < 0)
    {
        error.value = 'مقدار وارد شده معتبر نیست.'
        return false
    }

    if (isDecreaseTooMuch.value)
    {
        error.value = 'مقدار کاهش نمی‌تواند از موجودی فعلی بیشتر باشد.'
        return false
    }

    if (projectedStock.value < 0)
    {
        error.value = 'موجودی نمی‌تواند منفی شود.'
        return false
    }

    if (Number(form.value.low_stock_threshold) < 0)
    {
        error.value = 'حداقل موجودی هشدار نمی‌تواند منفی باشد.'
        return false
    }

    return true
}


/*
|--------------------------------------------------------------------------
| Save Stock
|--------------------------------------------------------------------------
*/

async function saveStock()
{
    if (!canSave.value || !validate())
    {
        return
    }

    saving.value = true
    error.value = null
    errors.value = {}

    try
    {
        const payload = {
            action: form.value.action,
            quantity: Number(form.value.quantity),
            low_stock_threshold: Number(form.value.low_stock_threshold),
        }

        const response = await axios.patch(
            Routes.product.stock(props.productId),
            payload
        )

        window.$toast?.success('موجودی با موفقیت به‌روزرسانی شد.')

        props.onSaved?.(response.data)

        window[props.jsPanelVariableName]?.close()
    }
    catch (err)
    {
        console.error('خطا در به‌روزرسانی موجودی:', err)

        if (err?.response?.status === 422)
        {
            errors.value = err.response.data.errors ?? {}
            error.value =
                Object.values(errors.value)[0]?.[0] ??
                err.response.data.message ??
                'اطلاعات واردشده معتبر نیست.'
        }
        else
        {
            error.value =
                err?.response?.data?.message ??
                'به‌روزرسانی موجودی انجام نشد.'
        }
    }
    finally
    {
        saving.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

function closePanel()
{
    window[props.jsPanelVariableName]?.close()
}


/*
|--------------------------------------------------------------------------
| Mount
|--------------------------------------------------------------------------
*/

onMounted(loadProduct)
</script>


<template>

    <div
        class="min-h-full bg-slate-50 p-5"
        dir="rtl"
    >

        <div class="mx-auto max-w-2xl">

            <!-- Loading -->

            <div
                v-if="loading"
                class="flex min-h-[300px] items-center justify-center"
            >
                <i class="fa-solid fa-spinner fa-spin ml-2 text-xl text-teal-600"></i>
                <span class="text-sm text-slate-500">در حال بارگذاری...</span>
            </div>


            <div
                v-else-if="product"
                class="space-y-5"
            >

                <!-- Product Header -->

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-start justify-between gap-4">

                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-bold text-slate-800">
                                {{ product.name }}
                            </h2>
                            <p dir="ltr" class="mt-1 text-xs font-semibold text-slate-500">
                                {{ product.sku }}
                            </p>
                        </div>

                        <span
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold"
                            :class="projectedStatus.class"
                        >
                            <component :is="projectedStatus.icon" :size="13"/>
                            {{ projectedStatus.label }}
                        </span>

                    </div>


                    <div class="mt-5 grid grid-cols-2 gap-3">

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="text-xs text-slate-400">موجودی فعلی</div>
                            <div class="mt-1 text-xl font-extrabold text-slate-800">
                                {{ formatNumber(currentStock) }}
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="text-xs text-slate-400">حداقل موجودی هشدار</div>
                            <div class="mt-1 text-xl font-extrabold text-slate-800">
                                {{ formatNumber(product.low_stock_threshold) }}
                            </div>
                        </div>

                    </div>

                </div>


                <!-- Error -->

                <div
                    v-if="error"
                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
                >
                    {{ error }}
                </div>


                <!-- Action -->

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h3 class="mb-4 font-bold text-slate-800">
                        نوع تغییر
                    </h3>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                        <button
                            v-for="item in ACTIONS"
                            :key="item.value"
                            type="button"
                            class="flex items-center gap-2 rounded-xl border p-3 text-right text-sm transition"
                            :class="
                                form.action === item.value
                                    ? 'border-teal-500 bg-teal-50 font-semibold text-teal-700 ring-2 ring-teal-100'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-teal-300 hover:bg-slate-50'
                            "
                            @click="form.action = item.value"
                        >
                            <component :is="item.icon" :size="17"/>
                            {{ item.label }}
                        </button>

                    </div>


                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                {{ quantityLabel }}
                            </label>
                            <input
                                v-model.number="form.quantity"
                                type="number"
                                min="0"
                                dir="ltr"
                                :disabled="saving"
                                :class="inputClass"
                            />
                            <p
                                v-if="isDecreaseTooMuch"
                                class="mt-1 text-xs text-rose-600"
                            >
                                بیشتر از موجودی فعلی است.
                            </p>
                            <p
                                v-if="errors.quantity"
                                class="mt-1 text-xs text-rose-600"
                            >
                                {{ errors.quantity[0] }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                حداقل موجودی هشدار
                            </label>
                            <input
                                v-model.number="form.low_stock_threshold"
                                type="number"
                                min="0"
                                dir="ltr"
                                :disabled="saving"
                                :class="inputClass"
                            />
                            <p
                                v-if="errors.low_stock_threshold"
                                class="mt-1 text-xs text-rose-600"
                            >
                                {{ errors.low_stock_threshold[0] }}
                            </p>
                        </div>

                    </div>

                </div>


                <!-- Preview -->

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h3 class="mb-4 font-bold text-slate-800">
                        پیش‌نمایش
                    </h3>

                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm">
                        <span class="text-slate-500">موجودی بعد از ثبت</span>
                        <span
                            class="text-lg font-extrabold"
                            :class="projectedStock < 0 ? 'text-rose-600' : 'text-slate-800'"
                        >
                            {{ formatNumber(projectedStock) }}
                        </span>
                    </div>

                </div>


                <!-- Footer -->

                <div class="sticky bottom-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-lg">

                    <div class="flex gap-3">

                        <button
                            type="button"
                            :disabled="saving"
                            class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:opacity-50"
                            @click="closePanel"
                        >
                            انصراف
                        </button>

                        <button
                            type="button"
                            :disabled="!canSave"
                            class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="saveStock"
                        >
                            <i
                                v-if="saving"
                                class="fa-solid fa-spinner fa-spin ml-2"
                            ></i>
                            {{ saving ? 'در حال ذخیره...' : 'ثبت موجودی' }}
                        </button>

                    </div>

                </div>

            </div>


            <!-- Not found / error -->

            <div
                v-else
                class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-12 text-center text-sm text-slate-400"
            >
                {{ error ?? 'محصولی برای نمایش وجود ندارد.' }}
            </div>

        </div>

    </div>

</template>