<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import InventoryFilters from './InventoryFilters.vue'
import InventoryDatatable from './InventoryDatatable.vue'
import ProductStockPanel from './../product/catalog/ProductStockPanel.vue'
import {
    Boxes,
    PackageX,
    AlertTriangle,
    PackageCheck,
    Tag,
} from 'lucide-vue-next'


/*
|--------------------------------------------------------------------------
| Filters & Datatable
|--------------------------------------------------------------------------
*/

const inventoryFilters = ref({})

function applyInventoryFilters(filters)
{
    inventoryFilters.value = filters
}

const filteredDatatableUrl = computed(() =>
{
    const params = new URLSearchParams()

    Object.entries(inventoryFilters.value).forEach(([key, value]) =>
    {
        params.append(key, value)
    })

    const query = params.toString()

    return query
        ? `${Routes.inventory.datatable}?${query}`
        : Routes.inventory.datatable
})

const inventory_datatable_ref = ref(null)


/*
|--------------------------------------------------------------------------
| Stats
|--------------------------------------------------------------------------
*/

const stats = ref({
    total_units: 0,
    fa_total_units: 0,
    inventory_value: 0,
    out_of_stock: 0,
    low_stock: 0,
    in_stock: 0,
    attention_count: 0,
    fa_attention_count: 0,
})

const attentionProducts = ref([])

async function loadStats()
{
    try
    {
        const response = await axios.get(Routes.inventory.stats)

        if (response.data.success)
        {
            const data = response.data.data ?? {}

            stats.value = {
                total_units: data.total_units ?? 0,
                fa_total_units: data.fa_total_units ?? 0,
                inventory_value: data.inventory_value ?? 0,
                out_of_stock: data.out_of_stock ?? 0,
                low_stock: data.low_stock ?? 0,
                in_stock: data.in_stock ?? 0,
                attention_count: data.attention_count ?? 0,
                fa_attention_count: data.fa_attention_count ?? 0,
            }

            attentionProducts.value = data.attention_products ?? []
        }
    }
    catch (error)
    {
        console.error('خطا در دریافت آمار موجودی:', error)
    }
}


/*
|--------------------------------------------------------------------------
| Format & Status Helpers
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

function attentionClass(product)
{
    if (product.stock <= 0)
    {
        return {
            wrapper: 'border-rose-100 bg-rose-50',
            icon: 'bg-white text-rose-600',
            badge: 'bg-rose-100 text-rose-700',
            iconComponent: PackageX,
        }
    }

    return {
        wrapper: 'border-amber-100 bg-amber-50',
        icon: 'bg-white text-amber-600',
        badge: 'bg-amber-100 text-amber-700',
        iconComponent: AlertTriangle,
    }
}

function attentionLabel(product)
{
    return product.stock <= 0 ? 'ناموجود' : 'کم‌موجودی'
}


/*
|--------------------------------------------------------------------------
| Stock Panel
|--------------------------------------------------------------------------
*/

function openStockPanel(product)
{
    if (!product?.id)
    {
        return
    }

    const name = `jsPanelV4StockProduct${product.id}`

    window.JsPanelV4({
        component: ProductStockPanel,
        modal: false,
        reload: true,
        variableName: name,
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'در حال بارگذاری ...',
        headerTitle: 'به‌روزرسانی موجودی',
        ajaxData: {},
        panelSize: {
            width: () => Math.min(600, window.innerWidth * 0.95),
            height: () => window.innerHeight * 0.6,
        },
        props: {
            productId: product.id,
            jsPanelVariableName: name,
            onSaved: () =>
            {
                inventory_datatable_ref.value?.refreshGrid()
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
                موجودی انبار
            </h1>
            <p class="mt-1 text-sm text-slate-400">
                وضعیت موجودی محصولات، ارزش انبار و محصولات نیازمند تأمین
            </p>
        </div>


        <!-- Stats -->

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <Boxes :size="15" class="text-teal-600"/>
                    کل اقلام موجود در انبار
                </div>
                <div class="mt-2 text-lg font-bold text-slate-800">
                    {{ stats.fa_total_units }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <PackageCheck :size="15" class="text-emerald-500"/>
                    موجود
                </div>
                <div class="mt-2 text-lg font-bold text-emerald-600">
                    {{ formatNumber(stats.in_stock) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <AlertTriangle :size="15" class="text-amber-500"/>
                    کم‌موجودی
                </div>
                <div class="mt-2 text-lg font-bold text-amber-600">
                    {{ formatNumber(stats.low_stock) }}
                </div>
                <div class="mt-1 text-[11px] text-slate-400">
                    ناموجود: {{ formatNumber(stats.out_of_stock) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <Boxes :size="15" class="text-slate-500"/>
                    ارزش موجودی انبار
                </div>
                <div class="mt-2 text-lg font-bold text-slate-800">
                    {{ formatNumber(stats.inventory_value) }}
                    <span class="text-xs font-normal text-slate-400">تومان</span>
                </div>
            </div>

        </div>


        <!-- Required Actions -->

        <div
            v-if="stats.attention_count > 0"
            class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm"
        >

            <div class="mb-2 flex items-center gap-2">
                <h2 class="text-base font-bold text-slate-800">
                    نیازمند تأمین موجودی
                </h2>
                <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-rose-100 px-2 text-xs font-bold text-rose-700">
                    {{ stats.fa_attention_count }}
                </span>
            </div>

            <div class="max-h-[220px] overflow-y-auto overflow-x-hidden pr-1">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                    <button
                        v-for="product in attentionProducts"
                        :key="product.id"
                        type="button"
                        class="w-full min-w-0 rounded-lg border p-2.5 text-right transition-all duration-200 hover:shadow-sm"
                        :class="attentionClass(product).wrapper"
                        @click="openStockPanel(product)"
                    >

                        <div class="flex items-center justify-between gap-2">

                            <div
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg shadow-sm"
                                :class="attentionClass(product).icon"
                            >
                                <component
                                    :is="attentionClass(product).iconComponent"
                                    :size="15"
                                    :stroke-width="2"
                                />
                            </div>

                            <span
                                class="max-w-[75%] truncate rounded-md px-2 py-1 text-[10px] font-bold"
                                :class="attentionClass(product).badge"
                            >
                                {{ attentionLabel(product) }}
                            </span>

                        </div>

                        <div class="mt-1.5 min-w-0">
                            <div class="truncate text-xs font-bold text-slate-800">
                                {{ product.name || 'بدون نام' }}
                            </div>
                            <div class="mt-1 flex items-center justify-between gap-2">
                                <span dir="ltr" class="shrink-0 text-[10px] font-semibold text-slate-500">
                                    {{ product.sku }}
                                </span>
                                <div class="flex min-w-0 items-center gap-1 text-[10px] text-slate-600">
                                    <Tag :size="11" class="shrink-0 text-slate-400"/>
                                    <span class="truncate">{{ product.brand || 'بدون برند' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-1.5 flex items-center justify-between rounded-md border border-slate-200 bg-white px-2 py-1">
                            <span class="text-[10px] font-bold text-slate-600">موجودی فعلی</span>
                            <span class="text-xs font-extrabold text-slate-800">
                                {{ formatNumber(product.stock) }}
                                <span class="text-[10px] font-normal text-slate-400">
                                    از حداقل {{ formatNumber(product.low_stock_threshold) }}
                                </span>
                            </span>
                        </div>

                    </button>

                </div>
            </div>

        </div>


        <!-- Datatable -->

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex flex-col gap-1 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-sm font-bold text-slate-800">
                    فهرست موجودی محصولات
                </h2>
            </div>

            <div class="p-1">

                <InventoryFilters @apply="applyInventoryFilters"/>

                <InventoryDatatable
                    :key="filteredDatatableUrl"
                    ref="inventory_datatable_ref"
                    :api-url="filteredDatatableUrl"
                />

            </div>

        </div>

    </div>

</template>