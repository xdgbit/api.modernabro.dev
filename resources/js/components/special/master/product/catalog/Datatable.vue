<script setup>
import {ref} from 'vue'
import ProductPanel from './ProductPanel.vue'
import ProductActions from './ProductActions.vue'
import ProductStockCell from './ProductStockCell.vue'
import ProductStatusCell from './ProductStatusCell.vue'
import ProductStockPanel from './ProductStockPanel.vue'

const dataTable_ref = ref(null)

const props = defineProps({
    apiUrl: {
        type: String,
        default: () => Routes.product.datatable,
    },
})


/*
|--------------------------------------------------------------------------
| Renderers & Formatters
|--------------------------------------------------------------------------
*/

const linkRenderer = (colorClass) => (params) =>
{
    if (params.value === null || params.value === undefined || params.value === '')
    {
        return '—'
    }

    return `
        <span class="cursor-pointer font-medium transition-colors ${colorClass}">
            ${params.value}
        </span>
    `
}

const productNameRenderer = linkRenderer('text-teal-600 hover:text-teal-800')

const formatPrice = (params) =>
{
    if (params.value === null || params.value === undefined || params.value === '')
    {
        return '—'
    }

    return `${Number(params.value).toLocaleString('fa-IR')} تومان`
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
        cellClass: 'selectable-cell',
        cellRenderer: productNameRenderer,
        onCellClicked: (params) => openProductPanel(params.data?.id),
    },
    {
        headerName: 'برند',
        field: 'brand',
        minWidth: 130,
        flex: 1,
        sortable: false,
        valueGetter: (params) => params.data?.brand?.title ?? '—',
    },
    {
        headerName: 'دسته‌بندی',
        field: 'category',
        minWidth: 130,
        flex: 1,
        sortable: false,
        valueGetter: (params) => params.data?.category?.title ?? '—',
    },
    {
        field: 'price',
        headerName: 'قیمت اصلی',
        minWidth: 140,
        flex: 1.2,
        sortable: true,
        valueFormatter: formatPrice,
    },
    {
        field: 'sale_price',
        headerName: 'قیمت فروش ویژه',
        minWidth: 140,
        flex: 1.2,
        sortable: true,
        valueFormatter: formatPrice,
    },
    {
        field: 'stock',
        headerName: 'موجودی',
        minWidth: 160,
        flex: 1.2,
        sortable: true,
        cellRenderer: ProductStockCell,
        cellClass: 'selectable-cell',
        onCellClicked: (params) => openStockPanel(params.data),
    },
    {
        field: 'status_label',
        headerName: 'وضعیت انتشار',
        minWidth: 130,
        flex: 1,
        sortable: true,
        cellRenderer: ProductStatusCell,
    },
    {
        headerName: 'عملیات',
        field: 'actions',
        minWidth: 110,
        width: 110,
        sortable: false,
        filter: false,
        cellRenderer: ProductActions,
        cellRendererParams: {
            refresh: () =>
            {
                dataTable_ref.value?.refreshGrid()
            },
            openProduct: (product) => openProductPanel(product?.id),
            openStock: (product) => openStockPanel(product),
        },
    },
])


/*
|--------------------------------------------------------------------------
| Open Panels
|--------------------------------------------------------------------------
*/

const openPanel = ({component, name, title, panelProps, panelSize = null}) =>
{
    const config = {
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
    }

    if (panelSize)
    {
        config.panelSize = panelSize
    }

    window.JsPanelV4(config)
}

const openProductPanel = (productId) =>
{
    if (!productId) return

    openPanel({
        component: ProductPanel,
        name: `jsPanelVariableEditProduct${productId}`,
        title: 'ویرایش محصول',
        panelProps: {productId},
    })
}

const openStockPanel = (product) =>
{
    if (!product?.id) return

    openPanel({
        component: ProductStockPanel,
        name: `jsPanelVariableStockProduct${product.id}`,
        title: 'به‌روزرسانی موجودی',
        panelProps: {productId: product.id},
        panelSize: {
            width: () => Math.min(600, window.innerWidth * 0.95),
            height: () => window.innerHeight * 0.6,
        },
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