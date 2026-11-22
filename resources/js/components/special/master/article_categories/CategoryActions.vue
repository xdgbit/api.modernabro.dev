<script setup>

import CategoryPanel from './CategoryPanel.vue'
import Swal from 'sweetalert2'

import {
    Pencil,
    Trash2,
} from 'lucide-vue-next'


const props = defineProps({

    params: {
        type: Object,
        required: true,
    },

})


const panelVariable =
    'jsPanelVariableEditCategory'


function refresh()
{
    props.params.refresh?.()
}


function edit()
{
    const categoryId =
        props.params.data.id

    window.JsPanelV4({

        component: CategoryPanel,

        modal: false,

        reload: true,

        variableName:
        panelVariable,

        theme: 'info',

        position: 'center',

        z_index: 10000000,

        preFooterText:
            'درحال بارگذاری ...',

        headerTitle:
            'ویرایش دسته‌بندی',

        ajaxData: {},

        props: {

            categoryId,

            jsPanelVariableName:
            panelVariable,

            onSaved: () =>
            {
                refresh()
            },

        },

        onclose_function: false,

        just_one_panel: true,

        container: 'window',

    })
}


async function remove()
{
    const result = await Swal.fire({

        title: 'حذف دسته‌بندی؟',

        text:
            'آیا از حذف این دسته‌بندی مطمئن هستید؟',

        icon: 'warning',

        showCancelButton: true,

        confirmButtonText:
            'بله، حذف کن',

        cancelButtonText:
            'لغو',

        confirmButtonColor:
            '#d33',

        reverseButtons: true,

    })


    if (!result.isConfirmed)
    {
        return
    }


    const categoryId =
        props.params.data.id


    try
    {

        await axios.delete(
            Routes.admin.article_category.delete(
                categoryId
            )
        )


        $toast.success(
            'دسته‌بندی با موفقیت حذف شد.'
        )


        refresh()

    }
    catch (error)
    {

        console.error(
            'خطا در حذف دسته‌بندی:',
            error
        )


        $toast.error(
            error.response?.data?.message ??
            'حذف دسته‌بندی با خطا مواجه شد.'
        )

    }

}

</script>


<template>

    <div
        class="flex h-full items-center gap-1"
    >

        <button
            type="button"
            title="ویرایش"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-blue-50 hover:text-blue-600"
            @click="edit"
        >

            <Pencil :size="16"/>

        </button>


        <button
            type="button"
            title="حذف"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600"
            @click="remove"
        >

            <Trash2 :size="16"/>

        </button>

    </div>

</template>