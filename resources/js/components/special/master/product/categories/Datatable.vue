<script setup>
import {ref} from 'vue'
import ProductCategoryPanel from './ProductCategoryPanel.vue'
import ProductCategoryActions from './ProductCategoryActions.vue'

const dataTable_ref = ref(null)

const props = defineProps({
    apiUrl: {
        type: String,
        default: () => Routes.product.categories.datatable,
    },
})

const emit = defineEmits(['open-category'])


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function escapeHtml(value)
{
    return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;')
}

function formatNumber(value)
{
    return Number(value ?? 0).toLocaleString('fa-IR')
}

const titleRenderer = (params) =>
{
    const value = params.value

    if (value === null || value === undefined || value === '')
    {
        return '—'
    }

    const isChild = !!params.data?.parent

    const prefix = isChild
        ? '<span class="ml-1 text-slate-300">└</span>'
        : ''

    return `
        <span class="inline-flex items-center cursor-pointer font-medium transition-colors text-teal-600 hover:text-teal-800">
            ${prefix}${escapeHtml(value)}
        </span>
    `
}

const countRenderer = (colorClass) => (params) =>
{
    return `
        <span class="inline-flex min-w-[2rem] items-center justify-center rounded-md px-2 py-1 text-xs font-bold ${colorClass}">
            ${formatNumber(params.value)}
        </span>
    `
}
const imageRenderer = (params) =>
{
    const url = params.value

    if (!url)
    {
        return `
            <div class="mt-1 flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-400">
                <i class="fa-regular fa-image"></i>
            </div>
        `
    }

    return `
        <img
            src="${escapeHtml(url)}"
            alt="${escapeHtml(params.data?.title ?? '')}"
            loading="lazy"
            class="mt-1 h-10 w-10 rounded-lg border border-slate-200 object-cover"
        />
    `
}

/*
|--------------------------------------------------------------------------
| Column Definitions
|--------------------------------------------------------------------------
*/

const columnDefs = ref([
    {
        field: 'image_url',
        headerName: 'تصویر',
        minWidth: 80,
        width: 80,
        maxWidth: 80,
        sortable: false,
        filter: false,
        cellClass: 'selectable-cell flex items-center',
        cellRenderer: imageRenderer,
        onCellClicked: (params) => openCategoryPanel(params.data),
    },
    {
        field: 'title',
        headerName: 'نام دسته‌بندی',
        minWidth: 220,
        flex: 2,
        sortable: true,
        cellClass: 'selectable-cell',
        cellRenderer: titleRenderer,
        onCellClicked: (params) => openCategoryPanel(params.data),
    },
    {
        field: 'slug',
        headerName: 'نامک (Slug)',
        minWidth: 170,
        flex: 1.5,
        sortable: true,
        cellClass: 'selectable-cell ltr',
    },
    {
        headerName: 'دسته والد',
        field: 'parent',
        minWidth: 150,
        flex: 1.2,
        sortable: false,
        valueGetter: (params) => params.data?.parent?.title ?? 'دسته اصلی',
    },
    {
        field: 'children_count',
        headerName: 'زیرمجموعه',
        minWidth: 110,
        flex: 0.8,
        sortable: true,
        cellRenderer: countRenderer('bg-indigo-100 text-indigo-700'),
    },
    {
        field: 'products_count',
        headerName: 'تعداد محصول',
        minWidth: 120,
        flex: 0.9,
        sortable: true,
        cellRenderer: countRenderer('bg-teal-100 text-teal-700'),
    },
    {
        field: 'sort_order',
        headerName: 'ترتیب نمایش',
        minWidth: 110,
        flex: 0.8,
        sortable: true,
        cellClass: 'ltr',
        valueFormatter: (params) => formatNumber(params.value),
    },
    {
        headerName: 'عملیات',
        field: 'actions',
        minWidth: 110,
        width: 110,
        sortable: false,
        filter: false,
        cellRenderer: ProductCategoryActions,
        cellRendererParams: {
            refresh: () =>
            {
                dataTable_ref.value?.refreshGrid()
            },
            openCategory: (category) => openCategoryPanel(category),
        },
    },
])


/*
|--------------------------------------------------------------------------
| Open Panel
|--------------------------------------------------------------------------
*/

const openPanel = ({component, name, title, panelProps}) =>
{
    window.JsPanelV4({
        component,
        modal: false,
        reload: true,
        variableName: name,
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'در حال بارگذاری ...',
        headerTitle: title,
        ajaxData: {},
        props: {
            jsPanelVariableName: name,
            onSaved: () =>
            {
                dataTable_ref.value?.refreshGrid()
            },
            ...panelProps,
        },
        onclose_function: false,
        just_one_panel: true,
        container: 'window',
    })
}

const openCategoryPanel = (category) =>
{
    if (!category?.id) return

    emit('open-category', category)

    openPanel({
        component: ProductCategoryPanel,
        name: `jsPanelVariableEditProductCategory${category.id}`,
        title: 'ویرایش دسته‌بندی',
        panelProps: {categoryId: category.id},
    })
}

defineExpose({
    refreshGrid: () =>
    {
        dataTable_ref.value?.refreshGrid()
    },
})
</script>

<template>
    <Datatable
        ref="dataTable_ref"
        :api-url="props.apiUrl"
        :column-defs="columnDefs"
        :row-height="52"
    />
</template>

<style>
.ag-cell.selectable-cell {
    user-select: text;
    -webkit-user-select: text;
    cursor: text;
}

.ag-cell.ltr {
    direction: ltr;
}
</style>