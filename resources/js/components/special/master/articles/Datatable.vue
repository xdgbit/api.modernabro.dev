<script setup>

import { ref } from 'vue'


import ArticleActions from './ArticleActions.vue'
import ArticleImageCell from './ArticleImageCell.vue'
import ArticleStatusCell from './ArticleStatusCell.vue'


const dataTable_ref = ref(null)


const API_URL = Routes.admin.article.datatable


const columnDefs = ref([

    {
        field: 'title',
        headerName: 'عنوان مقاله',
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
        field: 'main_image',
        headerName: 'تصویر',
        width: 100,
        sortable: false,
        cellRenderer: ArticleImageCell,
    },

    {
        field: 'category_title',
        headerName: 'دسته‌بندی',
        width: 150,
        sortable: true,
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
        field: 'views',
        headerName: 'بازدید',
        width: 110,
        sortable: true,
        valueFormatter: params => {

            if (
                params.value === null ||
                params.value === undefined
            ) {
                return '-'
            }

            return Number(
                params.value
            ).toLocaleString('fa-IR')

        },
    },

    {
        field: 'is_active',
        headerName: 'وضعیت',
        width: 120,
        sortable: true,
        cellRenderer: ArticleStatusCell,
    },

    {
        field: 'jalali_published_at',
        headerName: 'تاریخ انتشار',
        width: 150,
        sortable: true,
        cellStyle: {
            direction: 'ltr',
            textAlign: 'left',
        },
    },

    {
        headerName: 'عملیات',
        field: 'actions',
        sortable: false,
        filter: false,
        width: 130,

        cellRenderer: ArticleActions,

        cellRendererParams: {

            refresh: () => {
                dataTable_ref
                .value
                ?.refreshGrid()
            },

        },

    },

])


defineExpose({

    refreshGrid: () => {

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