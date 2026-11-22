<script setup>
import {
    ref,
    computed,
    onMounted,
} from 'vue'
import axios from 'axios'
import Datatable from './Datatable.vue'
import ProductCategoryPanel from './ProductCategoryPanel.vue'
import ProductCategoryFilters from './ProductCategoryFilters.vue'
import {
    Plus,
    Layers,
    FolderTree,
    CornerDownRight,
    PackageX,
    CheckCircle2,
} from 'lucide-vue-next'


/*
|--------------------------------------------------------------------------
| Filters & Datatable
|--------------------------------------------------------------------------
*/

const categoryFilters = ref({})

function applyCategoryFilters(filters)
{
    categoryFilters.value = filters
}

const filteredDatatableUrl = computed(() =>
{
    const params = new URLSearchParams()

    Object.entries(categoryFilters.value).forEach(([key, value]) =>
    {
        params.append(key, value)
    })

    const query = params.toString()

    return query
        ? `${Routes.product.categories.datatable}?${query}`
        : Routes.product.categories.datatable
})

const panelVariable = 'jsPanelVariableNewProductCategory'
const category_datatable_ref = ref(null)


/*
|--------------------------------------------------------------------------
| Stats
|--------------------------------------------------------------------------
*/

const stats = ref({
    total: 0,
    root_count: 0,
    child_count: 0,
    empty_count: 0,
    fa_empty_count: 0,
})

const emptyCategories = ref([])
const statsLoading = ref(false)

async function loadStats()
{
    statsLoading.value = true

    try
    {
        const response = await axios.get(Routes.product.categories.stats)

        if (response.data.success)
        {
            const data = response.data.data ?? {}

            stats.value = {
                total: data.total ?? 0,
                root_count: data.root_count ?? 0,
                child_count: data.child_count ?? 0,
                empty_count: data.empty_count ?? 0,
                fa_empty_count: data.fa_empty_count ?? 0,
            }

            emptyCategories.value = data.empty_categories ?? []
        }
    }
    catch (error)
    {
        console.error('خطا در دریافت آمار دسته‌بندی‌ها:', error)
    }
    finally
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


/*
|--------------------------------------------------------------------------
| Open Panels
|--------------------------------------------------------------------------
*/

function openCategory(category)
{
    if (!category?.id)
    {
        return
    }

    window.JsPanelV4({
        component: ProductCategoryPanel,
        modal: false,
        reload: true,
        variableName: `jsPanelVariableEditProductCategory${category.id}`,
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'در حال بارگذاری ...',
        headerTitle: 'ویرایش دسته‌بندی',
        ajaxData: {},
        props: {
            categoryId: category.id,
            jsPanelVariableName: `jsPanelVariableEditProductCategory${category.id}`,
            onSaved: () =>
            {
                category_datatable_ref.value?.refreshGrid()
                loadStats()
            },
        },
        onclose_function: false,
        just_one_panel: true,
        container: 'window',
    })
}

function openNewCategory()
{
    window.JsPanelV4({
        component: ProductCategoryPanel,
        modal: false,
        reload: true,
        variableName: panelVariable,
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'در حال بارگذاری ...',
        headerTitle: 'دسته‌بندی جدید',
        ajaxData: {},
        props: {
            categoryId: null,
            jsPanelVariableName: panelVariable,
            onSaved: () =>
            {
                category_datatable_ref.value?.refreshGrid()
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
</script>


<template>

    <div class="space-y-5">

        <!-- Header -->

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <h1 class="text-xl font-bold tracking-tight text-slate-800 lg:text-2xl">
                    مدیریت دسته‌بندی محصولات
                </h1>

                <p class="mt-1 text-sm text-slate-400">
                    ساختار دسته‌بندی‌ها، زیرمجموعه‌ها و تعداد محصولات هر دسته
                </p>

            </div>

            <button
                type="button"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700"
                @click="openNewCategory"
            >
                <Plus :size="17"/>
                دسته‌بندی جدید
            </button>

        </div>


        <!-- Stats -->

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <Layers :size="15"/>
                    کل دسته‌بندی‌ها
                </div>
                <div class="mt-2 text-lg font-bold text-slate-800">
                    {{ formatNumber(stats.total) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <FolderTree :size="15" class="text-teal-600"/>
                    دسته‌های اصلی
                </div>
                <div class="mt-2 text-lg font-bold text-teal-600">
                    {{ formatNumber(stats.root_count) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <CornerDownRight :size="15" class="text-indigo-500"/>
                    زیرمجموعه‌ها
                </div>
                <div class="mt-2 text-lg font-bold text-indigo-600">
                    {{ formatNumber(stats.child_count) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <PackageX :size="15" class="text-amber-500"/>
                    بدون محصول
                </div>
                <div class="mt-2 text-lg font-bold text-amber-600">
                    {{ formatNumber(stats.empty_count) }}
                </div>
            </div>

        </div>


        <!-- Empty Categories -->

        <div
            v-if="stats.empty_count > 0"
            class="rounded-2xl border border-slate-200 bg-white shadow-sm"
            :class="emptyCategories.length ? 'p-3' : 'px-5 py-3'"
        >

            <div
                class="flex items-center justify-between"
                :class="emptyCategories.length ? 'mb-2' : ''"
            >

                <div>

                    <div class="flex items-center gap-2">

                        <h2 class="text-base font-bold text-slate-800">
                            دسته‌بندی‌های بدون محصول
                        </h2>

                        <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-amber-100 px-2 text-xs font-bold text-amber-700">
                            {{ stats.fa_empty_count }}
                        </span>

                    </div>

                    <p
                        v-if="emptyCategories.length"
                        class="mt-1.5 text-xs text-slate-400"
                    >
                        دسته‌هایی که هنوز محصولی به آن‌ها متصل نشده است
                    </p>

                </div>

                <div
                    v-if="!emptyCategories.length"
                    class="flex items-center gap-2 text-xs text-slate-400"
                >
                    <CheckCircle2 :size="18" class="text-emerald-500"/>
                    <span>موردی برای پیگیری وجود ندارد</span>
                </div>

            </div>


            <div
                v-if="emptyCategories.length"
                class="max-h-[220px] overflow-y-auto overflow-x-hidden pr-1"
            >
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                    <button
                        v-for="category in emptyCategories"
                        :key="category.id"
                        type="button"
                        class="w-full min-w-0 rounded-lg border border-amber-100 bg-amber-50 p-2.5 text-right transition-all duration-200 hover:shadow-sm"
                        @click="openCategory(category)"
                    >

                        <div class="truncate text-xs font-bold text-slate-800">
                            {{ category.title || 'بدون عنوان' }}
                        </div>

                        <div class="mt-1 truncate text-[10px] text-slate-500">
                            {{ category.parent?.title ? 'زیرمجموعه ' + category.parent.title : 'دسته اصلی' }}
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
                        فهرست دسته‌بندی‌ها
                    </h2>
                </div>
            </div>

            <div class="p-1">

                <ProductCategoryFilters @apply="applyCategoryFilters"/>

                <Datatable
                    :key="filteredDatatableUrl"
                    ref="category_datatable_ref"
                    :api-url="filteredDatatableUrl"
                    @open-category="openCategory"
                />

            </div>

        </div>

    </div>

</template>