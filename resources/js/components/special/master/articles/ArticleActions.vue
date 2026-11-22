<script setup>

import ArticlePanel from './ArticlePanel.vue'
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
    'jsPanelVariableEditArticle'


function refresh()
{
    props.params.refresh?.()
}


function edit()
{
    const articleId =
        props.params.data.id

    window.JsPanelV4({

        component: ArticlePanel,

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
            'ویرایش مقاله',

        ajaxData: {},

        props: {

            articleId,

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

        title: 'حذف مقاله؟',

        text:
            'آیا از حذف این مقاله مطمئن هستید؟',

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


    const articleId =
        props.params.data.id


    try
    {
        await axios.delete(
            Routes.admin.article.delete(
                articleId
            )
        )


        $toast.success(
            'مقاله با موفقیت حذف شد.'
        )


        refresh()
    }
    catch (error)
    {
        console.error(
            'خطا در حذف مقاله:',
            error
        )


        $toast.error(
            error.response?.data?.message ??
            'حذف مقاله با خطا مواجه شد.'
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