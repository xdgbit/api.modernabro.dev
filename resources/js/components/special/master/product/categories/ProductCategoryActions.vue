<script setup>
import {
    Eye,
    Pencil,
    Trash2,
} from 'lucide-vue-next'

import ProductCategoryPanel from './ProductCategoryPanel.vue'

const props = defineProps({
    params: {
        type: Object,
        required: true,
    },
})


/*
|--------------------------------------------------------------------------
| Category
|--------------------------------------------------------------------------
*/

const category = props.params.data

const categoryId = category?.id


/*
|--------------------------------------------------------------------------
| Refresh Grid
|--------------------------------------------------------------------------
*/

function refreshGrid()
{
    props.params.refresh?.()
}


/*
|--------------------------------------------------------------------------
| Open Panel
|--------------------------------------------------------------------------
*/

function openPanel({
                       categoryId,
                       readOnly = false,
                       title,
                   })
{
    window.JsPanelV4({

        component: ProductCategoryPanel,

        modal: false,

        reload: true,

        variableName:
            `jsPanelVariableProductCategory${categoryId}`,

        theme: 'info',

        position: 'center',

        z_index: 10000000,

        preFooterText:
            'در حال بارگذاری ...',

        headerTitle:
        title,

        ajaxData: {},

        props: {

            categoryId,

            readOnly,

            jsPanelVariableName:
                `jsPanelVariableProductCategory${categoryId}`,

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


/*
|--------------------------------------------------------------------------
| View
|--------------------------------------------------------------------------
*/

function viewCategory()
{
    if (!categoryId)
    {
        return
    }

    openPanel({

        categoryId,

        readOnly: true,

        title: 'مشاهده دسته‌بندی',

    })
}


/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

function editCategory()
{
    if (!categoryId)
    {
        return
    }

    openPanel({

        categoryId,

        readOnly: false,

        title: 'ویرایش دسته‌بندی',

    })
}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

async function deleteCategory()
{
    if (!categoryId)
    {
        return
    }

    const confirmed =
        window.confirm(
            'آیا از حذف این دسته‌بندی اطمینان دارید؟'
        )

    if (!confirmed)
    {
        return
    }

    try
    {
        await axios.delete(Routes.product.categories.destroy(categoryId))
        refreshGrid()

    } catch (error)
    {
        console.error(
            'خطا در حذف دسته‌بندی:',
            error
        )

        const message =
            error?.response?.data?.message ??
            'حذف دسته‌بندی با خطا مواجه شد.'

        alert(message)
    }
}

</script>


<template>

    <div class="flex items-center justify-center whitespace-nowrap mt-2">

        <!-- مشاهده -->
        <button
            type="button"
            title="مشاهده"
            @click="viewCategory"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-blue-50 hover:text-blue-600"
        >
            <Eye :size="20"/>
        </button>

        <!-- ویرایش -->
        <button
            type="button"
            title="ویرایش"
            @click="editCategory"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-amber-50 hover:text-amber-600"
        >
            <Pencil :size="20"/>
        </button>

        <!-- حذف -->
        <button
            type="button"
            title="حذف"
            @click="deleteCategory"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
        >
            <Trash2 :size="20"/>
        </button>

    </div>

</template>