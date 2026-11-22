<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

import OrderDatatable from './OrderDatatable.vue'
import OrderFilters from './OrderFilters.vue'
import OrderPanel from './OrderPanel.vue'
import {
    ShoppingCart,
    Truck,
    CheckCircle2,
    Wallet,
    PackageCheck,
    AlertTriangle,
} from 'lucide-vue-next'


/*
|--------------------------------------------------------------------------
| Filters & Datatable
|--------------------------------------------------------------------------
*/

const orderFilters = ref({})

function applyOrderFilters(filters)
{
    orderFilters.value = filters
}

const filteredDatatableUrl = computed(() =>
{
    const params = new URLSearchParams()

    Object.entries(orderFilters.value).forEach(([key, value]) =>
    {
        params.append(key, value)
    })

    const query = params.toString()

    return query
        ? `${Routes.orders.datatable}?${query}`
        : Routes.orders.datatable
})

const order_datatable_ref = ref(null)


/*
|--------------------------------------------------------------------------
| Stats
|--------------------------------------------------------------------------
*/

const stats = ref({
    total: 0,
    pending: 0,
    processing: 0,
    shipped: 0,
    delivered: 0,
    cancelled: 0,
    unpaid: 0,
    revenue: 0,
    attention_count: 0,
    fa_attention_count: 0,
})

const attentionOrders = ref([])

async function loadStats()
{
    try
    {
        const response = await axios.get(Routes.orders.stats)

        if (response.data.success)
        {
            const data = response.data.data ?? {}

            stats.value = {
                total: data.total ?? 0,
                pending: data.pending ?? 0,
                processing: data.processing ?? 0,
                shipped: data.shipped ?? 0,
                delivered: data.delivered ?? 0,
                cancelled: data.cancelled ?? 0,
                unpaid: data.unpaid ?? 0,
                revenue: data.revenue ?? 0,
                attention_count: data.attention_count ?? 0,
                fa_attention_count: data.fa_attention_count ?? 0,
            }

            attentionOrders.value = data.attention_orders ?? []
        }
    }
    catch (error)
    {
        console.error('خطا در دریافت آمار سفارش‌ها:', error)
    }
}


/*
|--------------------------------------------------------------------------
| Format Helpers
|--------------------------------------------------------------------------
*/

function formatNumber(value)
{
    return Number(value ?? 0).toLocaleString('fa-IR')
}

function formatAmount(value)
{
    return Number(value ?? 0).toLocaleString('fa-IR')
}


/*
|--------------------------------------------------------------------------
| Attention Status
|--------------------------------------------------------------------------
*/

function attentionClass(order)
{
    if (order.payment_status === 'unpaid')
    {
        return {
            wrapper: 'border-rose-100 bg-rose-50',
            icon: 'bg-white text-rose-600',
            badge: 'bg-rose-100 text-rose-700',
            iconComponent: Wallet,
        }
    }

    return {
        wrapper: 'border-amber-100 bg-amber-50',
        icon: 'bg-white text-amber-600',
        badge: 'bg-amber-100 text-amber-700',
        iconComponent: AlertTriangle,
    }
}

function attentionLabel(order)
{
    return order.payment_status === 'unpaid'
        ? 'پرداخت نشده'
        : 'در انتظار پردازش'
}


/*
|--------------------------------------------------------------------------
| Open Panel (از کارت‌های نیازمند پیگیری)
|--------------------------------------------------------------------------
*/

function openOrderPanel(orderId)
{
    if (!orderId) return

    const name = `jsPanelVariableOrder${orderId}`

    window.JsPanelV4({
        component: OrderPanel,
        modal: false,
        reload: true,
        variableName: name,
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'در حال بارگذاری ...',
        headerTitle: 'جزئیات سفارش',
        ajaxData: {},
        props: {
            orderId,
            readOnly: false,
            jsPanelVariableName: name,
            onSaved: () =>
            {
                order_datatable_ref.value?.refreshGrid()
                loadStats()
            },
        },
        onclose_function: false,
        just_one_panel: true,
        container: 'window',
    })
}


/*
|--------------------------------------------------------------------------
| Mount
|--------------------------------------------------------------------------
*/

onMounted(() =>
{
    loadStats()
})
</script>


<template>

    <div class="space-y-5">

        <!-- Header -->

        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-800 lg:text-2xl">
                مدیریت سفارش‌ها
            </h1>
            <p class="mt-1 text-sm text-slate-400">
                پیگیری سفارش‌های مشتریان، وضعیت پرداخت و ارسال
            </p>
        </div>


        <!-- Stats -->

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <ShoppingCart :size="15"/>
                    کل سفارش‌ها
                </div>
                <div class="mt-2 text-lg font-bold text-slate-800">
                    {{ formatNumber(stats.total) }}
                </div>
                <div class="mt-1 text-[11px] text-slate-400">
                    در انتظار: {{ formatNumber(stats.pending) }} | در حال پردازش: {{ formatNumber(stats.processing) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <Truck :size="15" class="text-indigo-500"/>
                    ارسال شده
                </div>
                <div class="mt-2 text-lg font-bold text-indigo-600">
                    {{ formatNumber(stats.shipped) }}
                </div>
                <div class="mt-1 text-[11px] text-slate-400">
                    تحویل شده: {{ formatNumber(stats.delivered) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <Wallet :size="15" class="text-amber-500"/>
                    پرداخت نشده
                </div>
                <div class="mt-2 text-lg font-bold text-amber-600">
                    {{ formatNumber(stats.unpaid) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <PackageCheck :size="15" class="text-teal-600"/>
                    مجموع فروش
                </div>
                <div class="mt-2 text-lg font-bold text-slate-800">
                    {{ formatAmount(stats.revenue) }}
                    <span class="text-xs font-normal text-slate-400">تومان</span>
                </div>
            </div>

        </div>


        <!-- Required Actions -->

        <div
            v-if="stats.attention_count > 0"
            class="rounded-2xl border border-slate-200 bg-white shadow-sm"
            :class="attentionOrders.length ? 'p-3' : 'px-5 py-3'"
        >

            <div
                class="flex items-center justify-between"
                :class="attentionOrders.length ? 'mb-2' : ''"
            >

                <div>

                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-slate-800">
                            نیازمند پیگیری
                        </h2>
                        <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-rose-100 px-2 text-xs font-bold text-rose-700">
                            {{ stats.fa_attention_count }}
                        </span>
                    </div>

                    <p
                        v-if="attentionOrders.length"
                        class="mt-1.5 text-xs text-slate-400"
                    >
                        سفارش‌هایی که پرداخت نشده‌اند یا منتظر پردازش هستند
                    </p>

                </div>

                <div
                    v-if="!attentionOrders.length"
                    class="flex items-center gap-2 text-xs text-slate-400"
                >
                    <CheckCircle2 :size="18" class="text-emerald-500"/>
                    <span>موردی برای پیگیری وجود ندارد</span>
                </div>

            </div>


            <div
                v-if="attentionOrders.length"
                class="max-h-[220px] overflow-y-auto overflow-x-hidden pr-1"
            >
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                    <button
                        v-for="order in attentionOrders"
                        :key="order.id"
                        type="button"
                        class="group w-full min-w-0 rounded-lg border p-2.5 text-right transition-all duration-200 hover:shadow-sm"
                        :class="attentionClass(order).wrapper"
                        @click="openOrderPanel(order.id)"
                    >

                        <div class="flex flex-col gap-1.5">

                            <div class="flex items-center justify-between gap-2">

                                <div
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg shadow-sm"
                                    :class="attentionClass(order).icon"
                                >
                                    <component
                                        :is="attentionClass(order).iconComponent"
                                        :size="15"
                                        :stroke-width="2"
                                    />
                                </div>

                                <span
                                    class="max-w-[75%] truncate rounded-md px-2 py-1 text-[10px] font-bold"
                                    :class="attentionClass(order).badge"
                                >
                                    {{ attentionLabel(order) }}
                                </span>

                            </div>

                            <div class="min-w-0">

                                <div class="truncate text-xs font-bold text-slate-800">
                                    {{ order.receiver_name || 'بدون نام' }}
                                </div>

                                <div class="mt-1 flex min-w-0 items-center justify-between gap-2">
                                    <span dir="ltr" class="shrink-0 text-[10px] font-semibold text-slate-500">
                                        {{ order.code }}
                                    </span>
                                    <span class="truncate text-[10px] text-slate-600">
                                        {{ order.jalali_created_at ?? '—' }}
                                    </span>
                                </div>

                            </div>

                            <div class="flex items-center justify-between rounded-md border border-slate-200 bg-white px-2 py-1">
                                <span class="text-[10px] font-bold text-slate-600">مبلغ سفارش</span>
                                <span class="text-xs font-extrabold text-slate-800">
                                    {{ formatAmount(order.total_amount) }}
                                </span>
                            </div>

                        </div>

                    </button>

                </div>
            </div>

        </div>


        <!-- Datatable -->

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex flex-col gap-1 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-sm font-bold text-slate-800">
                    فهرست سفارش‌ها
                </h2>
            </div>

            <div class="p-1">

                <OrderFilters @apply="applyOrderFilters"/>

                <OrderDatatable
                    :key="filteredDatatableUrl"
                    ref="order_datatable_ref"
                    :api-url="filteredDatatableUrl"
                />

            </div>

        </div>

    </div>

</template>