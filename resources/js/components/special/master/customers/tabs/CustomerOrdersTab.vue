<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { ShoppingCart } from 'lucide-vue-next'

const props = defineProps({
    customerId: {
        type: [Number, String],
        required: true,
    },
})

const loading = ref(false)
const error = ref(null)
const orders = ref([])

const totalSpent = computed(() =>
    orders.value
          .filter(order => !['cancelled', 'returned'].includes(order.status))
          .reduce((sum, order) => sum + Number(order.total_amount || 0), 0)
)

function formatAmount(value)
{
    return `${Number(value ?? 0).toLocaleString('fa-IR')} تومان`
}

async function loadOrders()
{
    loading.value = true
    error.value = null

    try
    {
        const response = await axios.get(
            Routes.customer.orders(props.customerId)
        )

        orders.value = response.data.data?.orders ?? []
    }
    catch (err)
    {
        console.error('خطا در دریافت سفارشات مشتری:', err)

        error.value =
            err?.response?.data?.message ??
            'خطا در دریافت سفارش‌های مشتری.'
    }
    finally
    {
        loading.value = false
    }
}

onMounted(loadOrders)
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

            <div class="grid grid-cols-2 gap-3">

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <ShoppingCart :size="14"/>
                        تعداد سفارش‌ها
                    </div>
                    <div class="mt-2 text-lg font-bold text-slate-800">
                        {{ orders.length.toLocaleString('fa-IR') }}
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="text-xs text-slate-400">مجموع خرید (بدون لغو و مرجوعی)</div>
                    <div class="mt-2 text-lg font-bold text-teal-700">
                        {{ formatAmount(totalSpent) }}
                    </div>
                </div>

            </div>

            <!-- Table -->

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div
                    v-if="!orders.length"
                    class="px-4 py-12 text-center text-sm text-slate-400"
                >
                    این مشتری سفارشی ثبت نکرده است.
                </div>

                <div
                    v-else
                    class="overflow-x-auto"
                >

                    <table class="w-full text-right text-sm">

                        <thead class="bg-slate-50 text-xs text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold">شماره سفارش</th>
                            <th class="px-4 py-3 font-semibold">تاریخ</th>
                            <th class="px-4 py-3 text-center font-semibold">اقلام</th>
                            <th class="px-4 py-3 font-semibold">مبلغ</th>
                            <th class="px-4 py-3 font-semibold">وضعیت</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                        <tr
                            v-for="order in orders"
                            :key="order.id"
                        >
                            <td dir="ltr" class="px-4 py-3 font-medium text-slate-800">
                                {{ order.code }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ order.jalali_created_at ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600">
                                {{ Number(order.items_count ?? 0).toLocaleString('fa-IR') }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-800">
                                {{ formatAmount(order.total_amount) }}
                            </td>
                            <td class="px-4 py-3">
                                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                        {{ order.status_label ?? order.status }}
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