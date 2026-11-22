<script setup>
import {ref} from 'vue'

import CustomerPanel from './CustomerPanel.vue'
import CustomerActions from './CustomerActions.vue'

const dataTable_ref = ref(null)
const API_URL = Routes.customer.datatable
const refreshGrid = () =>
{
    dataTable_ref.value?.refreshGrid()
}

const openEditCustomer = (customer) =>
{
    const panelVariable = `jsPanelVariableEditCustomer${customer.id}`
    window.JsPanelV4({
        component: CustomerPanel,
        modal: false,
        reload: true,
        variableName: panelVariable,
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'درحال بارگذاری ...',
        headerTitle: 'ویرایش مشتری',
        ajaxData: {},
        props: {
            customerId: customer.id,
            jsPanelVariableName: panelVariable,
            onSaved: () =>
            {
                refreshGrid()
            },
        },
        onclose_function: false,
        just_one_panel: true,
        container: 'window',
    })
}
const activeStatusRenderer = (params) =>
{
    const isActive =
        params.value === true ||
        params.value === 1 ||
        params.value === '1' ||
        params.value === 'فعال'
    return isActive
        ? `<span class="status-active">
                <i class="fa fa-check"></i>
                فعال
           </span>`

        : `<span class="status-inactive">
                <i class="fa fa-times"></i>
                غیرفعال
           </span>`
}


/* ======================================================
   Columns
====================================================== */

const columnDefs = [
    {
        field: 'full_name',
        headerName: 'نام و نام خانوادگی',
        flex: 1.4,
        sortable: true,
    },
    {
        field: 'mobile_number',
        headerName: 'شماره موبایل',
        flex: 1,
        sortable: true,
    },
    {
        field: 'email',
        headerName: 'ایمیل',
        flex: 1.2,
        sortable: true,
    },
    {
        field: 'reservations_count',
        headerName: 'تعداد رزرو',
        width: 130,
        sortable: true,
    },
    {
        field: 'fa_is_active',
        headerName: 'وضعیت',
        width: 120,
        sortable: false,
        cellRenderer: activeStatusRenderer,
    },
    {
        field: 'actions',
        headerName: 'عملیات',
        sortable: false,
        filter: false,
        width: 120,
        cellRenderer: CustomerActions,
        cellRendererParams: {

            onEdit: openEditCustomer,

            onRefresh: refreshGrid,

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
        :api-url="API_URL"
        :column-defs="columnDefs"
    />
</template>

<style scoped>

:deep(.status-active) {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #16a34a !important;
    font-weight: 600;
}


:deep(.status-active i) {
    color: #16a34a !important;
    font-size: 13px;
}


:deep(.status-inactive) {
    color: #dc2626 !important;
}

</style>

