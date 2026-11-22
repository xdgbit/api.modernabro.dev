<script setup>
import {
    ref,
    onMounted,
    computed,
    onBeforeUnmount,
} from 'vue'
import axios from 'axios'
import Datatable from './Datatable.vue'
import ProductPanel from './ProductPanel.vue'
import ProductFilters from './ProductFilters.vue'
import ProductStockPanel from './ProductStockPanel.vue'
import {
    Plus,
    CheckCircle2,
    AlertTriangle,
    PackageX,
    Package,
    Boxes,
    Tag,
} from 'lucide-vue-next'


/*
|--------------------------------------------------------------------------
| Filters & Datatable
|--------------------------------------------------------------------------
*/

const productFilters = ref({})

function applyProductFilters(filters)
{
    productFilters.value = filters
}

const filteredDatatableUrl = computed(() =>
{
    const params = new URLSearchParams()

    Object.entries(productFilters.value).forEach(([key, value]) =>
    {
        params.append(key, value)
    })

    const query = params.toString()

    return query
        ? `${Routes.product.datatable}?${query}`
        : Routes.product.datatable
})

const panelVariable = 'jsPanelVariableNewProduct'
const product_datatable_ref = ref(null)


/*
|--------------------------------------------------------------------------
| Stats
|--------------------------------------------------------------------------
*/

const stats = ref({
    total: 0,
    active: 0,
    inactive: 0,
    out_of_stock: 0,
    low_stock: 0,
    inventory_value: 0,
    attention_count: 0,
    fa_attention_count: 0,
})

const attentionProducts = ref([])
const statsLoading = ref(false)

async function loadStats()
{
    statsLoading.value = true

    try
    {
        const response = await axios.get(Routes.product.stats)

        if (response.data.success)
        {
            const data = response.data.data ?? {}

            stats.value = {
                total: data.total ?? 0,
                active: data.active ?? 0,
                inactive: data.inactive ?? 0,
                out_of_stock: data.out_of_stock ?? 0,
                low_stock: data.low_stock ?? 0,
                inventory_value: data.inventory_value ?? 0,
                attention_count: data.attention_count ?? 0,
                fa_attention_count: data.fa_attention_count ?? 0,
            }

            attentionProducts.value = data.attention_products ?? []
        }
    } catch (error)
    {
        console.error(
            'خطا در دریافت آمار محصولات:',
            error
        )
    } finally
    {
        statsLoading.value = false
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

function formatAmount(amount)
{
    return Number(amount ?? 0).toLocaleString('fa-IR')
}


/*
|--------------------------------------------------------------------------
| Stock Status
|--------------------------------------------------------------------------
*/

function stockStatusClass(product)
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

function stockStatusLabel(product)
{
    if (product.stock <= 0)
    {
        return 'ناموجود'
    }

    return 'کم‌موجودی'
}


/*
|--------------------------------------------------------------------------
| Open Panels
|--------------------------------------------------------------------------
*/

function openProduct(product)
{
    if (!product?.id)
    {
        return
    }

    window.JsPanelV4({
        component: ProductPanel,
        modal: false,
        reload: true,
        variableName: panelVariable,
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'درحال بارگذاری ...',
        headerTitle: 'جزئیات محصول',
        ajaxData: {},
        props: {
            productId: product.id,
            jsPanelVariableName: panelVariable,
            onSaved: () =>
            {
                product_datatable_ref.value?.refreshGrid()
                loadStats()
            },
        },
        onclose_function: false,
        just_one_panel: true,
        container: 'window',
    })
}

function openStockPanel(product)
{
    const productId = product.id
    const panelName = `jsPanelV4StockProduct${productId}`

    window.JsPanelV4({
        component: ProductStockPanel,
        modal: false,
        reload: true,
        variableName: panelName,
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
            productId,
            jsPanelVariableName: panelName,
            onSaved: () =>
            {
                product_datatable_ref.value?.refreshGrid()
                loadStats()
            },
        },
        onclose_function: false,
        just_one_panel: true,
        container: 'window',
    })
}

function open_new_product()
{
    window.JsPanelV4({
        component: ProductPanel,
        modal: false,
        reload: true,
        variableName: panelVariable,
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'درحال بارگذاری ...',
        headerTitle: 'محصول جدید',
        ajaxData: {},
        props: {
            productId: null,
            jsPanelVariableName: panelVariable,
            onSaved: () =>
            {
                product_datatable_ref.value?.refreshGrid()
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
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() =>
{
    loadStats()
})

onBeforeUnmount(() =>
{
    // no timers to clean up
})
</script>


<template>

    <div class="space-y-5">

        <!-- Header -->

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <h1 class="text-xl font-bold tracking-tight text-slate-800 lg:text-2xl">
                    مدیریت محصولات
                </h1>

                <p class="mt-1 text-sm text-slate-400">
                    مشاهده وضعیت محصولات، موجودی انبار و مدیریت کالاهای فروشگاه
                </p>

            </div>

            <button
                type="button"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700"
                @click="open_new_product"
            >
                <Plus :size="17"/>
                محصول جدید
            </button>

        </div>


        <!-- Stats -->

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <Package :size="15"/>
                    کل محصولات
                </div>
                <div class="mt-2 text-lg font-bold text-slate-800">
                    {{ formatNumber(stats.total) }}
                </div>
                <div class="mt-1 text-[11px] text-slate-400">
                    فعال: {{ formatNumber(stats.active) }} | غیرفعال: {{ formatNumber(stats.inactive) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <PackageX :size="15" class="text-rose-500"/>
                    ناموجود
                </div>
                <div class="mt-2 text-lg font-bold text-rose-600">
                    {{ formatNumber(stats.out_of_stock) }}
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
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <Boxes :size="15" class="text-teal-600"/>
                    ارزش موجودی انبار
                </div>
                <div class="mt-2 text-lg font-bold text-slate-800">
                    {{ formatAmount(stats.inventory_value) }}
                    <span class="text-xs font-normal text-slate-400">تومان</span>
                </div>
            </div>

        </div>


        <!-- Required Actions -->

        <div
            v-if="stats.attention_count > 0"
            class="rounded-2xl border border-slate-200 bg-white shadow-sm"
            :class="attentionProducts.length ? 'p-3' : 'px-5 py-3'"
        >

            <div
                class="flex items-center justify-between"
                :class="attentionProducts.length ? 'mb-2' : ''"
            >

                <div>

                    <div class="flex items-center gap-2">

                        <h2 class="text-base font-bold text-slate-800">
                            نیازمند تأمین موجودی
                        </h2>

                        <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-rose-100 px-2 text-xs font-bold text-rose-700">
                            {{ stats.fa_attention_count }}
                        </span>

                    </div>

                    <p
                        v-if="attentionProducts.length"
                        class="mt-1.5 text-xs text-slate-400"
                    >
                        محصولاتی که ناموجود شده‌اند یا به حداقل موجودی رسیده‌اند
                    </p>

                </div>

                <div
                    v-if="!attentionProducts.length"
                    class="flex items-center gap-2 text-xs text-slate-400"
                >
                    <CheckCircle2 :size="18" class="text-emerald-500"/>
                    <span>موردی برای پیگیری وجود ندارد</span>
                </div>

            </div>


            <div
                v-if="attentionProducts.length"
                class="max-h-[220px] overflow-y-auto overflow-x-hidden pr-1"
            >
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                    <button
                        v-for="product in attentionProducts"
                        :key="product.id"
                        type="button"
                        class="group w-full min-w-0 rounded-lg border p-2.5 text-right transition-all duration-200 hover:shadow-sm"
                        :class="stockStatusClass(product).wrapper"
                        @click="openStockPanel(product)"
                    >

                        <div class="flex flex-col gap-1.5">

                            <!-- Icon + Status -->

                            <div class="flex items-center justify-between gap-2">

                                <div
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg shadow-sm"
                                    :class="stockStatusClass(product).icon"
                                >
                                    <component
                                        :is="stockStatusClass(product).iconComponent"
                                        :size="15"
                                        :stroke-width="2"
                                    />
                                </div>

                                <span
                                    class="max-w-[75%] truncate rounded-md px-2 py-1 text-[10px] font-bold"
                                    :class="stockStatusClass(product).badge"
                                >
                                    {{ stockStatusLabel(product) }}
                                </span>

                            </div>


                            <!-- Name + SKU -->

                            <div class="min-w-0">

                                <div class="truncate text-xs font-bold text-slate-800">
                                    {{ product.name || 'بدون نام' }}
                                </div>

                                <div class="mt-1 flex min-w-0 items-center justify-between gap-2">

                                    <span
                                        dir="ltr"
                                        class="shrink-0 text-[10px] font-semibold text-slate-500"
                                    >
                                        {{ product.sku }}
                                    </span>

                                    <div class="flex min-w-0 items-center gap-1 text-[10px] font-medium text-slate-600">
                                        <Tag :size="11" class="shrink-0 text-slate-400"/>
                                        <span class="truncate">{{ product.brand || 'بدون برند' }}</span>
                                    </div>

                                </div>

                            </div>


                            <!-- Stock -->

                            <div class="flex items-center justify-between rounded-md border border-slate-200 bg-white px-2 py-1">

                                <span class="text-[10px] font-bold text-slate-600">
                                    موجودی فعلی
                                </span>

                                <span class="text-xs font-extrabold text-slate-800">
                                    {{ formatNumber(product.stock) }}
                                    <span class="text-[10px] font-normal text-slate-400">
                                        از حداقل {{ formatNumber(product.low_stock_threshold) }}
                                    </span>
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
                <div>
                    <h2 class="text-sm font-bold text-slate-800">
                        فهرست محصولات
                    </h2>
                </div>
            </div>

            <div class="p-1">

                <ProductFilters @apply="applyProductFilters"/>

                <Datatable
                    :key="filteredDatatableUrl"
                    ref="product_datatable_ref"
                    :api-url="filteredDatatableUrl"
                />

            </div>

        </div>

    </div>

</template>