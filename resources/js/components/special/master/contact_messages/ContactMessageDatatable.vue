<script setup>
import {ref} from 'vue'
import ContactMessagePanel from './ContactMessagePanel.vue'
import ContactMessageActions from './ContactMessageActions.vue'

const emit = defineEmits(['changed'])

const dataTable_ref = ref(null)
const API_URL = Routes.contact_message.datatable

const refreshGrid = () => dataTable_ref.value?.refreshGrid()

const openMessage = (message) =>
{
    const panelVariable = `jsPanelVariableContactMessage${message.id}`

    window.JsPanelV4({
        component: ContactMessagePanel,
        modal: false,
        reload: true,
        variableName: panelVariable,
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'درحال بارگذاری ...',
        headerTitle: 'مشاهده پیام',
        ajaxData: {},
        props: {
            messageId: message.id,
            jsPanelVariableName: panelVariable,
            // چون پیام با باز شدن «خوانده» می‌شود، جدول و آمار باید تازه شوند
            onSaved: () => emit('changed'),
        },
        onclose_function: false,
        just_one_panel: true,
        container: 'window',
    })
}

const readStatusRenderer = (params) =>
{
    const isRead = params.value === true || params.value === 1 || params.value === '1'
    return isRead
        ? `<span class="status-read"><i class="fa fa-envelope-open"></i> خوانده‌شده</span>`
        : `<span class="status-unread"><i class="fa fa-envelope"></i> خوانده‌نشده</span>`
}

const columnDefs = [
    {field: 'full_name', headerName: 'نام و نام خانوادگی', flex: 1.2, sortable: true},
    {field: 'mobile', headerName: 'شماره موبایل', flex: 1, sortable: true},
    {field: 'subject', headerName: 'موضوع', flex: 1.5, sortable: true},
    {field: 'jalali_submitted_at', headerName: 'تاریخ ارسال', width: 160, sortable: true, cellStyle: {direction: 'ltr', textAlign: 'right'}},
    {field: 'is_read', headerName: 'وضعیت', width: 130, sortable: false, cellRenderer: readStatusRenderer},
    {
        field: 'actions',
        headerName: 'عملیات',
        sortable: false,
        filter: false,
        width: 120,
        cellRenderer: ContactMessageActions,
        cellRendererParams: {
            onView: openMessage,
            onRefresh: () =>
            {
                refreshGrid()
                emit('changed')
            },
        },
    },
]

defineExpose({refreshGrid})
</script>

<template>
    <Datatable ref="dataTable_ref" :api-url="API_URL" :column-defs="columnDefs"/>
</template>

<style scoped>
:deep(.status-read) {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #16a34a !important;
    font-weight: 600;
}

:deep(.status-unread) {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #dc2626 !important;
    font-weight: 600;
}
</style>