<script setup>

import {ref} from 'vue'

import ExpertPanel from './ExpertPanel.vue'
import ExpertActions from './ExpertActions.vue'


/* ======================================================
   DataTable
====================================================== */

const dataTable_ref = ref(null)

const API_URL = Routes.expert.datatable


/* ======================================================
   Refresh
====================================================== */

const refreshGrid = () =>
{
    dataTable_ref.value?.refreshGrid()
}


/* ======================================================
   Edit Expert
====================================================== */

const openEditExpert = (expert) =>
{
    const panelVariable =
        `jsPanelVariableEditExpert${expert.id}`


    window.JsPanelV4({

        component: ExpertPanel,

        modal: false,

        reload: true,

        variableName: panelVariable,

        theme: 'info',

        position: 'center',

        z_index: 10000000,

        preFooterText: 'درحال بارگذاری ...',

        headerTitle: 'ویرایش کارشناس',

        ajaxData: {},

        props: {
            expertId: expert.id,
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


/* ======================================================
   Status Renderer
====================================================== */

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

const columnDefs = ref([
    {
        field: 'full_name',
        headerName: 'کارشناس',
        minWidth: 180,
        flex: 1.5,
        sortable: true,
    },
    {
        field: 'username',
        headerName: 'نام کاربری',
        minWidth: 130,
        flex: 1,
        sortable: true,
    },
    {
        field: 'mobile_number',
        headerName: 'شماره موبایل',
        minWidth: 130,
        flex: 1,
        sortable: true,
    },
    {
        field: 'national_code',
        headerName: 'کد ملی',
        minWidth: 180,
        flex: 1.5,
        sortable: true,
    },
    {
        field: 'jalali_submitted_at',
        headerName: 'تاریخ عضویت',
        minWidth: 130,
        flex: 1,
        sortable: true,
        cellStyle: {
            direction: 'ltr',
        },
    },
    {
        field: 'fa_is_active',
        headerName: 'وضعیت',
        minWidth: 110,
        width: 110,
        sortable: false,
        filter: false,
        cellRenderer: activeStatusRenderer,
    },
    {
        headerName: 'عملیات',
        field: 'actions',
        minWidth: 110,
        width: 110,
        sortable: false,
        filter: false,

        cellRenderer: ExpertActions,

        cellRendererParams: {

            onEdit: openEditExpert,

            onRefresh: refreshGrid,

        },

    },

])


/* ======================================================
   Expose
====================================================== */

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
    display: inline-flex;

    align-items: center;

    gap: 5px;

    color: #dc2626 !important;

    font-weight: 600;
}


:deep(.status-inactive i) {
    color: #dc2626 !important;

    font-size: 13px;
}

</style>
