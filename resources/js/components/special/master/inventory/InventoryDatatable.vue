<script setup>
import { ref } from 'vue'
import InventoryActions from './InventoryActions.vue'
import InventoryStockCell from './InventoryStockCell.vue'
import ProductStockPanel from './../product/catalog/ProductStockPanel.vue'

const dataTable_ref = ref(null)

const props = defineProps({
    apiUrl: {
        type: String,
        default: () => Routes.inventory.datatable,
    },
})


/*
|--------------------------------------------------------------------------
| Renderers & Formatters
|--------------------------------------------------------------------------
*/

const nameRenderer = (params) =>
{
    if (!params.value)
    {
        return '—'
    }

    return `
        <span class="cursor-pointer font-medium transition-colors text-teal-600 hover:text-teal-800">
            ${params.value}
        </span>
    `
}

const formatNumber = (params) =>
{
    if (params.value === null || params.value === undefined)
    {
        return '—'
    }

    return Number(params.value).toLocaleString('fa-IR')
}

const formatDate = (params) =>
{
    return params.value || '—'
}


/*
|--------------------------------------------------------------------------
| Column Definitions
|--------------------------------------------------------------------------
*/

const columnDefs = ref([
    {
        field: 'sku',
        headerName: 'کد محصول (SKU)',
        minWidth: 150,
        flex: 1,
        sortable: true,
        cellClass: 'selectable-cell ltr',
    },
    {
        field: 'name',
        headerName: 'نام محصول',
        minWidth: 220,
        flex: 2.5,
        sortable: true,
        cellRenderer: nameRenderer,
        cellClass: 'selectable-cell',
        onCellClicked: (params) => openStockPanel(params.data),
    },
    {
        field: 'brand',
        headerName: 'برند',
        minWidth: 130,
        flex: 1,
        sortable: false,
        valueGetter: (params) => params.data?.brand?.title ?? '—',
    },
    {
        field: 'category',
        headerName: 'دسته‌بندی',
        minWidth: 130,
        flex: 1,
        sortable: false,
        valueGetter: (params) => params.data?.category?.title ?? '—',
    },
    {
        field: 'stock',
        headerName: 'موجودی فعلی',
        minWidth: 130,
        flex: 1,
        sortable: true,
        valueFormatter: formatNumber,
    },
    {
        field: 'low_stock_threshold',
        headerName: 'حد هشدار',
        minWidth: 110,
        flex: 0.8,
        sortable: true,
        valueFormatter: formatNumber,
    },
    {
        field: 'stock_status_label',
        headerName: 'وضعیت موجودی',
        minWidth: 150,
        flex: 1,
        sortable: false,
        cellRenderer: InventoryStockCell,
    },
    {
        field: 'inventory_value',
        headerName: 'ارزش موجودی (تومان)',
        minWidth: 170,
        flex: 1.2,
        sortable: true,
        valueFormatter: formatNumber,
    },
    {
        field: 'last_stock_updated_at',
        headerName: 'آخرین تغییر',
        minWidth: 150,
        flex: 1,
        sortable: true,
        cellClass: 'ltr',
        valueFormatter: formatDate,
    },
    {
        headerName: 'عملیات',
        field: 'actions',
        minWidth: 90,
        width: 90,
        sortable: false,
        filter: false,
        cellRenderer: InventoryActions,
        cellRendererParams: {
            refresh: () =>
            {
                dataTable_ref.value?.refreshGrid()
            },
            openStock: (product) => openStockPanel(product),
        },
    },
])


/*
|--------------------------------------------------------------------------
| Open Stock Panel
|--------------------------------------------------------------------------
*/

const openStockPanel = (product) =>
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
                dataTable_ref.value?.refreshGrid()
            },
        },
        onclose_function: false,
        just_one_panel: true,
        container: 'window',
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
        :row-height="58"
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