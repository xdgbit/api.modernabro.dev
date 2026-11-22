<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import {
    ShoppingCart,
    Wallet,
    Package,
    AlertTriangle,
    Plus,
    Boxes,
    Users,
    Tag,
} from 'lucide-vue-next'


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading = ref(false)
const error = ref(null)

const stats = ref({
    today_orders: 0,
    today_revenue: 0,
    unpaid_orders: 0,
    low_stock: 0,
})

const latestOrders = ref([])


/*
|--------------------------------------------------------------------------
| Quick Links
|--------------------------------------------------------------------------
*/

const quickLinks = [
    { title: 'سفارش‌ها', description: 'مشاهده و پیگیری سفارش‌ها', icon: ShoppingCart, href: Routes.orders.index },
    { title: 'محصول جدید', description: 'افزودن محصول به فروشگاه', icon: Plus, href: Routes.product.index },
    { title: 'موجودی انبار', description: 'بررسی و به‌روزرسانی موجودی', icon: Boxes, href: Routes.inventory.index },
    { title: 'مشتریان', description: 'مدیریت مشتریان و آدرس‌ها', icon: Users, href: Routes.customer.index },
]


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function formatNumber(value)
{
    return Number(value ?? 0).toLocaleString('fa-IR')
}

function formatAmount(value)
{
    return `${Number(value ?? 0).toLocaleString('fa-IR')} تومان`
}

const orderStatusClass = {
    pending: 'bg-slate-100 text-slate-600',
    paid: 'bg-blue-100 text-blue-700',
    processing: 'bg-amber-100 text-amber-700',
    shipped: 'bg-indigo-100 text-indigo-700',
    delivered: 'bg-emerald-100 text-emerald-700',
    cancelled: 'bg-rose-100 text-rose-700',
    returned: 'bg-violet-100 text-violet-700',
}


/*
|--------------------------------------------------------------------------
| Load Data
|--------------------------------------------------------------------------
*/

async function loadDashboard()
{
    loading.value = true
    error.value = null

    try
    {
        const response = await axios.get(Routes.admin.dashboard.get_data)
        const data = response.data.data ?? {}

        stats.value = {
            today_orders: data.stats?.today_orders ?? 0,
            today_revenue: data.stats?.today_revenue ?? 0,
            unpaid_orders: data.stats?.unpaid_orders ?? 0,
            low_stock: data.stats?.low_stock ?? 0,
        }

        latestOrders.value = data.latest_orders ?? []
    }
    catch (err)
    {
        console.error('خطا در دریافت اطلاعات داشبورد:', err)

        error.value =
            err?.response?.data?.message ??
            'خطا در دریافت اطلاعات داشبورد.'
    }
    finally
    {
        loading.value = false
    }
}

onMounted(loadDashboard)
</script>


<template>

    <div class="space-y-6" dir="rtl">

        <!-- Header -->

        <div>
            <h1 class="text-2xl font-bold text-slate-800">داشبورد</h1>
            <p class="mt-1 text-sm text-slate-400">خلاصه وضعیت فروشگاه امروز</p>
        </div>


        <!-- Error -->

        <div
            v-if="error"
            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
        >
            {{ error }}
        </div>


        <!-- Stats -->

        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <ShoppingCart :size="15" />
                    سفارش‌های امروز
                </div>
                <div class="mt-3 text-2xl font-bold text-slate-800">
                    {{ formatNumber(stats.today_orders) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <Wallet :size="15" />
                    فروش امروز
                </div>
                <div class="mt-3 text-2xl font-bold text-teal-700">
                    {{ formatAmount(stats.today_revenue) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <AlertTriangle :size="15" class="text-amber-500" />
                    پرداخت نشده
                </div>
                <div class="mt-3 text-2xl font-bold text-amber-600">
                    {{ formatNumber(stats.unpaid_orders) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <Package :size="15" class="text-rose-500" />
                    کم‌موجودی یا ناموجود
                </div>
                <div class="mt-3 text-2xl font-bold text-rose-600">
                    {{ formatNumber(stats.low_stock) }}
                </div>
            </div>

        </div>


        <!-- Latest orders -->

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-bold text-slate-800">آخرین سفارش‌ها</h2>
            </div>

            <div v-if="loading" class="px-5 py-10 text-center text-sm text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-teal-600"></i>
            </div>

            <div v-else-if="!latestOrders.length" class="px-5 py-10 text-center text-sm text-slate-400">
                سفارشی ثبت نشده است.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">شماره سفارش</th>
                        <th class="px-5 py-3 font-semibold">گیرنده</th>
                        <th class="px-5 py-3 font-semibold">مبلغ</th>
                        <th class="px-5 py-3 font-semibold">وضعیت</th>
                        <th class="px-5 py-3 font-semibold">تاریخ</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <tr v-for="order in latestOrders" :key="order.id">
                        <td dir="ltr" class="px-5 py-3 font-medium text-slate-800">{{ order.code }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ order.receiver_name }}</td>
                        <td class="px-5 py-3 font-semibold text-slate-800">{{ formatAmount(order.total_amount) }}</td>
                        <td class="px-5 py-3">
                                <span
                                    class="rounded-lg px-2.5 py-1 text-xs font-semibold"
                                    :class="orderStatusClass[order.status] ?? 'bg-slate-100 text-slate-600'"
                                >
                                    {{ order.status_label ?? order.status }}
                                </span>
                        </td>
                        <td class="px-5 py-3 text-slate-500">{{ order.jalali_created_at ?? '—' }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>


        <!-- Quick links -->

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <h2 class="mb-4 font-bold text-slate-800">دسترسی سریع</h2>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">

                <a
                    v-for="link in quickLinks"
                    :key="link.title"
                    :href="link.href"
                    class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-teal-300 hover:bg-teal-50/40"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <component :is="link.icon" :size="18" />
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-700">{{ link.title }}</div>
                        <div class="mt-0.5 text-xs text-slate-400">{{ link.description }}</div>
                    </div>
                </a>

            </div>

        </div>

    </div>

</template>