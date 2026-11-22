<script setup>
import { ref } from 'vue'
import OrderPanel from './OrderPanel.vue'
import OrderActions from './OrderActions.vue'
import OrderStatusCell from './OrderStatusCell.vue'
import OrderPaymentCell from './OrderPaymentCell.vue'

const dataTable_ref = ref(null)

const props = defineProps({
    apiUrl: {
        type: String,
        default: () => Routes.orders.datatable,
    },
})


/*
|--------------------------------------------------------------------------
| Renderers & Formatters
|--------------------------------------------------------------------------
*/

const orderCodeRenderer = (params) =>
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

const formatAmount = (params) =>
{
    if (params.value === null || params.value === undefined || params.value === '')
    {
        return '—'
    }

    return `${Number(params.value).toLocaleString('fa-IR')} تومان`
}

const formatCount = (params) =>
{
    if (params.value === null || params.value === undefined)
    {
        return '—'
    }

    return Number(params.value).toLocaleString('fa-IR')
}


/*
|--------------------------------------------------------------------------
| Column Definitions
|--------------------------------------------------------------------------
*/

const columnDefs = ref([
    {
        field: 'code',
        headerName: 'شماره سفارش',
        minWidth: 150,
        flex: 1,
        sortable: true,
        cellClass: 'selectable-cell ltr',
        cellRenderer: orderCodeRenderer,
        onCellClicked: (params) => openOrderPanel(params.data?.id, true),
    },
    {
        field: 'jalali_created_at',
        headerName: 'تاریخ ثبت',
        minWidth: 150,
        flex: 1,
        sortable: true,
        cellClass: 'ltr',
    },
    {
        field: 'customer',
        headerName: 'مشتری',
        minWidth: 170,
        flex: 1.5,
        sortable: false,
        valueGetter: (params) => params.data?.customer?.full_name ?? '—',
    },
    {
        field: 'receiver_name',
        headerName: 'گیرنده',
        minWidth: 150,
        flex: 1.3,
        sortable: true,
        valueGetter: (params) => params.data?.receiver_name ?? '—',
    },
    {
        field: 'items_count',
        headerName: 'تعداد اقلام',
        minWidth: 110,
        flex: 0.8,
        sortable: true,
        valueFormatter: formatCount,
    },
    {
        field: 'subtotal',
        headerName: 'جمع اقلام',
        minWidth: 140,
        flex: 1.1,
        sortable: true,
        valueFormatter: formatAmount,
    },
    {
        field: 'discount_amount',
        headerName: 'تخفیف',
        minWidth: 120,
        flex: 1,
        sortable: true,
        valueFormatter: formatAmount,
    },
    {
        field: 'total_amount',
        headerName: 'مبلغ نهایی',
        minWidth: 150,
        flex: 1.2,
        sortable: true,
        valueFormatter: formatAmount,
    },
    {
        field: 'payment_status',
        headerName: 'وضعیت پرداخت',
        minWidth: 150,
        flex: 1,
        sortable: true,
        cellRenderer: OrderPaymentCell,
    },
    {
        field: 'status_label',
        headerName: 'وضعیت سفارش',
        minWidth: 150,
        flex: 1,
        sortable: true,
        cellRenderer: OrderStatusCell,
    },
    {
        headerName: 'عملیات',
        field: 'actions',
        minWidth: 110,
        width: 110,
        sortable: false,
        filter: false,
        cellRenderer: OrderActions,
        cellRendererParams: {
            // OrderActions بعد از ذخیره، این تابع را صدا می‌زند تا جدول رفرش شود
            refresh: () =>
            {
                dataTable_ref.value?.refreshGrid()
            },
        },
    },
])


/*
|--------------------------------------------------------------------------
| Open Panel
|--------------------------------------------------------------------------
*/

const openOrderPanel = (orderId, readOnly = false) =>
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
        headerTitle: readOnly ? 'مشاهده سفارش' : 'جزئیات سفارش',
        ajaxData: {},
        props: {
            orderId,
            readOnly,
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