<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { Wallet, CheckCircle2, Clock3, XCircle } from 'lucide-vue-next'

const props = defineProps({
    customerId: {
        type: [Number, String],
        required: true,
    },
})

const loading = ref(false)
const error = ref(null)
const payments = ref([])

const successfulTotal = computed(() =>
    payments.value
            .filter(payment => payment.status === 'success')
            .reduce((sum, payment) => sum + Number(payment.amount || 0), 0)
)

const statusMap = {
    success: { label: 'موفق', class: 'bg-emerald-100 text-emerald-700', icon: CheckCircle2 },
    pending: { label: 'در انتظار', class: 'bg-amber-100 text-amber-700', icon: Clock3 },
    failed: { label: 'ناموفق', class: 'bg-rose-100 text-rose-700', icon: XCircle },
}

function statusOf(payment)
{
    return statusMap[payment.status] ?? {
        label: payment.status_label ?? payment.status,
        class: 'bg-slate-100 text-slate-600',
        icon: Wallet,
    }
}

function formatAmount(value)
{
    return `${Number(value ?? 0).toLocaleString('fa-IR')} تومان`
}

async function loadPayments()
{
    loading.value = true
    error.value = null

    try
    {
        const response = await axios.get(
            Routes.customer.payments(props.customerId)
        )

        payments.value = response.data.data?.payments ?? []
    }
    catch (err)
    {
        console.error('خطا در دریافت پرداخت‌های مشتری:', err)

        error.value =
            err?.response?.data?.message ??
            'خطا در دریافت پرداخت‌های مشتری.'
    }
    finally
    {
        loading.value = false
    }
}

onMounted(loadPayments)
</script>


<template>

    <div
        class="mx-auto max-w-4xl space-y-5"
        dir="rtl"
    >

        <div
            v-if="loading"
            class="flex min-h-[300px] items-center justify-center"
        >
            <i class="fa-solid fa-spinner fa-spin text-xl text-teal-600"></i>
        </div>

        <template v-else>

            <div
                v-if="error"
                class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
            >
                {{ error }}
            </div>

            <!-- Summary -->

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">مجموع پرداخت‌های موفق</span>
                    <span class="text-lg font-bold text-teal-700">
                        {{ formatAmount(successfulTotal) }}
                    </span>
                </div>

            </div>

            <!-- Table -->

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div
                    v-if="!payments.length"
                    class="px-4 py-12 text-center text-sm text-slate-400"
                >
                    پرداختی برای این مشتری ثبت نشده است.
                </div>

                <div
                    v-else
                    class="overflow-x-auto"
                >

                    <table class="w-full text-right text-sm">

                        <thead class="bg-slate-50 text-xs text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold">کد تراکنش</th>
                            <th class="px-4 py-3 font-semibold">سفارش</th>
                            <th class="px-4 py-3 font-semibold">روش</th>
                            <th class="px-4 py-3 font-semibold">تاریخ</th>
                            <th class="px-4 py-3 font-semibold">مبلغ</th>
                            <th class="px-4 py-3 font-semibold">وضعیت</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                        <tr
                            v-for="payment in payments"
                            :key="payment.id"
                        >
                            <td dir="ltr" class="px-4 py-3 text-xs text-slate-600">
                                {{ payment.reference_code ?? '—' }}
                            </td>
                            <td dir="ltr" class="px-4 py-3 font-medium text-slate-800">
                                {{ payment.order_code ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ payment.payment_method_title ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ payment.jalali_paid_at ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-800">
                                {{ formatAmount(payment.amount) }}
                            </td>
                            <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="statusOf(payment).class"
                                    >
                                        <component :is="statusOf(payment).icon" :size="12"/>
                                        {{ statusOf(payment).label }}
                                    </span>
                            </td>
                        </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </template>

    </div>

</template>