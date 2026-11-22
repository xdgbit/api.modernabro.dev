<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'

/*
|--------------------------------------------------------------------------
| Props & Emits
|--------------------------------------------------------------------------
*/

const props = defineProps({
    orderId: {
        type: [Number, String],
        required: true,
    },

    order: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['saved', 'close'])


/*
|--------------------------------------------------------------------------
| Constants
|--------------------------------------------------------------------------
*/

const statusLabels = {
    pending: 'در انتظار',
    paid: 'پرداخت شده',
    processing: 'در حال پردازش',
    shipped: 'ارسال شده',
    delivered: 'تحویل شده',
    cancelled: 'لغو شده',
    returned: 'مرجوع شده',
}

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

// وضعیت‌های مجاز از بک‌اند (بر اساس وضعیت فعلی سفارش)
const allowedStatuses = ref([])

const form = ref({
    status: '',
    tracking_code: '',
    admin_note: '',
})


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const needsTrackingCode = computed(() =>
    form.value.status === 'shipped'
)

const isCancelling = computed(() =>
    form.value.status === 'cancelled'
)

const isReturning = computed(() =>
    form.value.status === 'returned'
)

const requiresNote = computed(() =>
    isCancelling.value || isReturning.value
)

const hasChanged = computed(() =>
    !!form.value.status && form.value.status !== props.order.status
)

const canSave = computed(() =>
    !saving.value &&
    !loading.value &&
    hasChanged.value
)


/*
|--------------------------------------------------------------------------
| Load Allowed Statuses
|--------------------------------------------------------------------------
*/

async function loadAllowedStatuses()
{
    loading.value = true
    error.value = null

    try
    {
        const response = await axios.post(
            Routes.orders.statusPanel,
            {
                order_id: props.orderId,
            }
        )

        const data = response.data.data ?? {}

        allowedStatuses.value = data.allowed_statuses ?? []

        form.value.tracking_code = props.order.tracking_code ?? ''
    }
    catch (err)
    {
        console.error('خطا در دریافت وضعیت‌های مجاز:', err)

        error.value =
            err?.response?.data?.message ??
            'خطا در دریافت وضعیت‌های مجاز.'
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

    if (!form.value.status)
    {
        error.value = 'وضعیت جدید را انتخاب کنید.'
        return false
    }

    if (!hasChanged.value)
    {
        error.value = 'وضعیت جدید با وضعیت فعلی یکسان است.'
        return false
    }

    if (needsTrackingCode.value && !form.value.tracking_code.trim())
    {
        error.value = 'برای ارسال سفارش، کد رهگیری را وارد کنید.'
        return false
    }

    if (requiresNote.value && !form.value.admin_note.trim())
    {
        error.value = isCancelling.value
            ? 'برای لغو سفارش، ذکر دلیل در یادداشت الزامی است.'
            : 'برای مرجوعی سفارش، ذکر دلیل در یادداشت الزامی است.'
        return false
    }

    return true
}


/*
|--------------------------------------------------------------------------
| Save Status
|--------------------------------------------------------------------------
*/

async function saveStatus()
{
    if (!validate())
    {
        return
    }

    saving.value = true
    error.value = null

    try
    {
        const payload = {
            status: form.value.status,
            tracking_code: needsTrackingCode.value
                ? form.value.tracking_code.trim()
                : null,
            admin_note: form.value.admin_note.trim() || null,
        }

        const response = await axios.patch(
            Routes.orders.changeStatus(props.orderId),
            payload
        )

        // پس از موفقیت، والد (OrderPanel) داده سفارش را دوباره می‌خواند
        emit('saved', response.data)
    }
    catch (err)
    {
        console.error('خطا در تغییر وضعیت سفارش:', err)

        const errors = err?.response?.data?.errors

        const firstError = errors
            ? Object.values(errors)[0]?.[0]
            : null

        error.value =
            firstError ??
            err?.response?.data?.message ??
            'تغییر وضعیت سفارش انجام نشد.'
    }
    finally
    {
        saving.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Watch
|--------------------------------------------------------------------------
*/

// اگر وضعیت جدید به حالت نیازمند کد رهگیری نیست، کد را پاک نکن ولی خطا نده
watch(() => form.value.status, () =>
{
    error.value = null
})

// بعد از تغییر وضعیت سفارش، فرم ریست می‌شود و وضعیت‌های مجاز دوباره خوانده می‌شوند
watch(() => props.order.status, async () =>
{
    form.value = {
        status: '',
        tracking_code: '',
        admin_note: '',
    }

    await loadAllowedStatuses()
})


/*
|--------------------------------------------------------------------------
| Mount
|--------------------------------------------------------------------------
*/

loadAllowedStatuses()
</script>


<template>

    <div
        class="mx-auto max-w-2xl space-y-5"
        dir="rtl"
    >

        <!-- Loading -->

        <div
            v-if="loading"
            class="flex min-h-[200px] items-center justify-center"
        >
            <i class="fa-solid fa-spinner fa-spin ml-2 text-xl text-teal-600"></i>
            <span class="text-sm text-slate-500">در حال بارگذاری...</span>
        </div>


        <template v-else>

            <!-- Error -->

            <div
                v-if="error"
                class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
            >
                {{ error }}
            </div>


            <!-- Current status -->

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-center justify-between">

                    <span class="text-sm text-slate-500">وضعیت فعلی</span>

                    <span class="rounded-lg bg-slate-100 px-3 py-1 text-sm font-bold text-slate-700">
                        {{ statusLabels[order.status] ?? order.status }}
                    </span>

                </div>

            </div>


            <!-- New status -->

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <h3 class="mb-5 font-bold text-slate-800">
                    وضعیت جدید
                </h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            وضعیت سفارش
                        </label>
                        <select
                            v-model="form.status"
                            :class="inputClass"
                        >
                            <option value="" disabled>انتخاب وضعیت</option>
                            <option
                                v-for="status in allowedStatuses"
                                :key="status"
                                :value="status"
                            >
                                {{ statusLabels[status] ?? status }}
                            </option>
                        </select>
                    </div>

                    <div v-if="needsTrackingCode">
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            کد رهگیری پستی
                        </label>
                        <input
                            v-model="form.tracking_code"
                            type="text"
                            dir="ltr"
                            placeholder="کد رهگیری را وارد کنید"
                            :class="inputClass"
                        />
                    </div>

                </div>

                <p
                    v-if="!allowedStatuses.length"
                    class="mt-4 text-xs text-amber-600"
                >
                    برای این سفارش در حال حاضر تغییر وضعیتی مجاز نیست.
                </p>

            </div>


            <!-- Note -->

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <h3 class="mb-5 font-bold text-slate-800">
                    یادداشت داخلی
                </h3>

                <textarea
                    v-model="form.admin_note"
                    rows="3"
                    placeholder="یادداشت برای همکاران (اختیاری)"
                    :class="inputClass"
                ></textarea>

                <p
                    v-if="requiresNote"
                    class="mt-3 text-xs"
                    :class="isCancelling ? 'text-rose-600' : 'text-violet-600'"
                >
                    {{ isCancelling
                    ? 'برای لغو سفارش، ذکر دلیل الزامی است.'
                    : 'برای مرجوعی سفارش، ذکر دلیل الزامی است.'
                    }}
                </p>

            </div>


            <!-- Footer -->

            <div class="sticky bottom-0 z-40 rounded-2xl border border-slate-200 bg-white p-4 shadow-lg">

                <div class="flex justify-end">

                    <button
                        type="button"
                        :disabled="!canSave"
                        class="inline-flex h-11 min-w-40 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="saveStatus"
                    >
                        <i :class="saving ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-floppy-disk'"></i>
                        <span>{{ saving ? 'در حال ذخیره...' : 'ثبت وضعیت' }}</span>
                    </button>

                </div>

            </div>

        </template>

    </div>

</template>