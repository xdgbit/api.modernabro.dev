<script setup>
import { ref } from 'vue'
import ReviewActions from './ReviewActions.vue'
import ProductPanel from '../../product/catalog/ProductPanel.vue'


/*
|--------------------------------------------------------------------------
| Props & Emits
|--------------------------------------------------------------------------
*/

const props = defineProps({
    apiUrl: { type: String, required: true },
    onChanged: { type: Function, default: null },
})

const emit = defineEmits(['changed'])

const dataTable_ref = ref(null)
const actioned_type_LABELS = {
    pending: 'در انتظار تأیید',
    approved: 'تأیید شده',
    rejected: 'رد شده',
}

const actioned_type_CLASSES = {
    pending: 'bg-amber-100 text-amber-700',
    approved: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-rose-100 text-rose-700',
}


/*
|--------------------------------------------------------------------------
| Formatters (متن ساده، بدون HTML)
|--------------------------------------------------------------------------
*/

function ratingText(value)
{
    const rating = Math.min(5, Math.max(0, Math.round(Number(value ?? 0))))

    return '★'.repeat(rating) + '☆'.repeat(5 - rating)
}

function truncate(text, length = 120)
{
    if (!text) return '—'

    return text.length > length ? `${text.slice(0, length)}…` : text
}


/*
|--------------------------------------------------------------------------
| Link Handlers
|--------------------------------------------------------------------------
*/

function openPanel({ component, name, title, panelProps })
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
            onSaved: () => refreshGrid(),
            ...panelProps,
        },
        onclose_function: false,
        just_one_panel: true,
        container: 'window',
    })
}

function openProductPanel(productId)
{
    if (!productId) return

    openPanel({
        component: ProductPanel,
        name: 'jsPanelVariableEditProduct',
        title: 'ویرایش محصول',
        panelProps: {productId },
    })
}


/*
|--------------------------------------------------------------------------
| Review Actions
|--------------------------------------------------------------------------
*/

function refreshGrid()
{
    dataTable_ref.value?.refreshGrid()
    emit('changed')
}


/*
|--------------------------------------------------------------------------
| Column Definitions
|--------------------------------------------------------------------------
*/

const columnDefs = [
    {
        field: 'id',
        headerName: 'شناسه',
        minWidth: 80,
        flex: 0.5,
        sortable: true,
        cellClass: 'ltr',
    },
    {
        field: 'product_name',
        headerName: 'محصول',
        minWidth: 160,
        flex: 1.5,
        sortable: false,
        cellClass: 'selectable-cell link-cell',
        valueFormatter: params => params.value || '—',
        onCellClicked: params => openProductPanel(params.data?.product_id),
    },
    {
        field: 'author_name',
        headerName: 'نام مهمان',
        minWidth: 140,
        flex: 1,
        sortable: false,
        valueFormatter: params => params.value || 'مهمان',
    },
    {
        field: 'rating',
        headerName: 'امتیاز',
        minWidth: 120,
        flex: 0.8,
        sortable: true,
        cellClass: 'ltr',
        valueFormatter: params => ratingText(params.value),
    },
    {
        field: 'comment',
        headerName: 'متن نظر',
        minWidth: 240,
        flex: 3,
        sortable: false,
        cellClass: 'selectable-cell',
        valueFormatter: params => truncate(params.value),
    },
    {
        field: 'actioned_type',
        headerName: 'وضعیت',
        minWidth: 130,
        flex: 1,
        sortable: true,
        valueFormatter: params => actioned_type_LABELS[params.value] ?? params.value,
    },
    {
        field: 'jalali_submitted_at',
        headerName: 'تاریخ ثبت',
        minWidth: 140,
        flex: 1,
        sortable: true,
        cellClass: 'ltr',
    },
    {
        headerName: 'عملیات',
        field: 'actions',
        minWidth: 150,
        width: 150,
        sortable: false,
        filter: false,
        cellRenderer: ReviewActions,
        cellRendererParams: {
            onChanged: () => refreshGrid(),
        },
    },
]

defineExpose({
    refreshGrid,
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

.ag-cell.link-cell {
    cursor: pointer;
    color: #4f46e5;
    font-weight: 500;
}

.ag-cell.link-cell:hover {
    color: #3730a3;
}
</style>