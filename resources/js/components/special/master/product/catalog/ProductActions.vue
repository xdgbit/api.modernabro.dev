<script setup>

import {
    Eye,
    Pencil,
    Trash2,
    PackagePlus,
} from 'lucide-vue-next'

import ProductPanel from './ProductPanel.vue'
import ProductStockPanel from './ProductStockPanel.vue'

const props = defineProps({
    params: {
        type: Object,
        required: true,
    },
})


/*
|--------------------------------------------------------------------------
| Product
|--------------------------------------------------------------------------
*/

const product = props.params.data

const productId = product?.id


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
                       productId,
                       readOnly = false,
                       title,
                   })
{
    window.JsPanelV4({

        component: ProductPanel,

        modal: false,

        reload: true,

        variableName:
            `jsPanelVariableProduct${productId}`,

        theme: 'info',

        position: 'center',

        z_index: 10000000,

        preFooterText:
            'در حال بارگذاری ...',

        headerTitle:
        title,

        ajaxData: {},

        props: {

            productId,

            readOnly,

            jsPanelVariableName:
                `jsPanelVariableProduct${productId}`,

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

function viewProduct()
{
    if (!productId)
    {
        return
    }

    openPanel({

        productId,

        readOnly: true,

        title: 'مشاهده محصول',

    })
}


/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

function editProduct()
{
    if (!productId)
    {
        return
    }

    openPanel({

        productId,

        readOnly: false,

        title: 'ویرایش محصول',

    })
}


/*
|--------------------------------------------------------------------------
| Stock Panel
|--------------------------------------------------------------------------
*/

function openStockPanel()
{
    if (!productId)
    {
        return
    }

    const panelName = `jsPanelV4StockProduct${productId}`

    window.JsPanelV4({
        component: ProductStockPanel,
        modal: false,
        reload: true,
        variableName: panelName,
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'در حال بارگذاری ...',
        headerTitle: 'به‌روزرسانی موجودی',
        ajaxData: {},
        panelSize: {
            width: () => Math.min(600, window.innerWidth * 0.95),
            height: () => window.innerHeight * 0.6,
        },
        props: {
            productId,
            jsPanelVariableName: panelName,
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
| Delete
|--------------------------------------------------------------------------
*/

async function deleteProduct()
{
    if (!productId)
    {
        return
    }

    const confirmed =
        window.confirm(
            'آیا از حذف این محصول اطمینان دارید؟'
        )

    if (!confirmed)
    {
        return
    }

    try
    {
        await axios.delete(
            Routes.product.destroy(
                productId
            )
        )

        refreshGrid()

    } catch (error)
    {
        console.error(
            'خطا در حذف محصول:',
            error
        )

        const message =
            error?.response?.data?.message ??
            'حذف محصول با خطا مواجه شد.'

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
            @click="viewProduct"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-blue-50 hover:text-blue-600"
        >
            <Eye :size="20" />
        </button>

        <!-- ویرایش -->
        <button
            type="button"
            title="ویرایش"
            @click="editProduct"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-amber-50 hover:text-amber-600"
        >
            <Pencil :size="20" />
        </button>

        <!-- حذف -->
        <button
            type="button"
            title="حذف"
            @click="deleteProduct"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
        >
            <Trash2 :size="20" />
        </button>

    </div>

</template>