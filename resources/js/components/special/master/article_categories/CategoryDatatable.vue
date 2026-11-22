<script setup>

import {ref} from 'vue'

import CategoryActions from './CategoryActions.vue'
import CategoryStatusCell from './CategoryStatusCell.vue'


const dataTable_ref = ref(null)


const API_URL = Routes.admin.article_category.datatable


const columnDefs = ref([

    {
        field: 'title',
        headerName: 'عنوان دسته‌بندی',
        flex: 1,
        sortable: true,
    },

    {
        field: 'slug',
        headerName: 'Slug',
        width: 180,
        sortable: true,
    },

    {
        field: 'articles_count',
        headerName: 'تعداد مطالب',
        width: 130,
        sortable: true,

        valueFormatter: params =>
        {

            if (
                params.value === null ||
                params.value === undefined
            )
            {
                return '-'
            }

            return Number(
                params.value
            ).toLocaleString('fa-IR')

        },

    },

    {
        field: 'submitter_full_name',
        headerName: 'ثبت کننده',
        width: 140,
        sortable: true,
    },

    {
        field: 'jalali_submitted_at',
        headerName: 'تاریخ ثبت',
        width: 140,
        sortable: true,

        cellStyle: {
            direction: 'ltr',
            textAlign: 'left',
        },

    },

    {
        field: 'is_active',
        headerName: 'وضعیت',
        width: 120,
        sortable: true,
        cellRenderer: CategoryStatusCell,
    },

    {
        headerName: 'عملیات',
        field: 'actions',
        sortable: false,
        filter: false,
        width: 130,

        cellRenderer: CategoryActions,

        cellRendererParams: {

            refresh: () =>
            {

                dataTable_ref
                .value
                ?.refreshGrid()

            },

        },

    },

])


defineExpose({

    refreshGrid: () =>
    {

        dataTable_ref
        .value
        ?.refreshGrid()

    },

})

</script>


<template>

    <Datatable
        ref="dataTable_ref"
        :api-url="API_URL"
        :column-defs="columnDefs"
    />

</template>