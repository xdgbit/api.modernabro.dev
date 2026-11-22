<script setup>
import {ref} from 'vue'
const dataTable_ref = ref(null)
const API_URL = Routes.admin.reservation.datatable
const columnDefs = [
    {
        field: 'id',
        headerName: 'شماره',
        width: 90,
        sortable: true,
    },
    {
        field: 'customer_name',
        headerName: 'مهمان',
        flex: 1,
        sortable: true,
    },
    {
        field: 'accommodation_title',
        headerName: 'اقامتگاه',
        flex: 1,
        sortable: true,
    },
    {
        field: 'unit_title',
        headerName: 'واحد',
        flex: 1,
        sortable: true,
    },
    {
        field: 'fa_check_in',
        headerName: 'ورود',
        width: 120,
        sortable: true,
    },
    {
        field: 'fa_check_out',
        headerName: 'خروج',
        width: 120,
        sortable: true,
    },
    {
        field: 'guest_count',
        headerName: 'مهمان',
        width: 90,
        sortable: true,
    },
    {
        field: 'fa_status',
        headerName: 'وضعیت',
        width: 120,
        sortable: false,
    },
]
</script>
<template>
    <Datatable
        ref="dataTable_ref"
        :api-url="API_URL"
        :column-defs="columnDefs"
        :extra-params="{
            limit: 5,
        }"
    />
</template>