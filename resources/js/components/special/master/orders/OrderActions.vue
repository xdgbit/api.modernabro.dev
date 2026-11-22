<script setup>
import {
    Eye,
    Pencil,
    Settings,
} from 'lucide-vue-next'

import OrderPanel from './OrderPanel.vue'

const props = defineProps({
    params: {
        type: Object,
        required: true,
    },
})


/*
|--------------------------------------------------------------------------
| Order
|--------------------------------------------------------------------------
*/

const order = props.params.data

const orderId = order?.id


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
                       orderId,
                       readOnly = false,
                       title,
                       initialTab = 'details',
                   })
{
    window.JsPanelV4({

        component: OrderPanel,

        modal: false,

        reload: true,

        variableName:
            `jsPanelVariableOrder${orderId}`,

        theme: 'info',

        position: 'center',

        z_index: 10000000,

        preFooterText:
            'در حال بارگذاری ...',

        headerTitle:
        title,

        ajaxData: {},

        props: {

            orderId,

            readOnly,

            initialTab,

            jsPanelVariableName:
                `jsPanelVariableOrder${orderId}`,

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

function viewOrder()
{
    if (!orderId)
    {
        return
    }

    openPanel({

        orderId,

        readOnly: true,

        title: 'مشاهده سفارش',

    })
}


/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

function editOrder()
{
    if (!orderId)
    {
        return
    }

    openPanel({

        orderId,

        readOnly: false,

        title: 'جزئیات سفارش',

    })
}


/*
|--------------------------------------------------------------------------
| Status (opens the action tab directly)
|--------------------------------------------------------------------------
*/

function openStatusTab()
{
    if (!orderId)
    {
        return
    }

    openPanel({

        orderId,

        readOnly: false,

        title: 'تغییر وضعیت سفارش',

        initialTab: 'action',

    })
}

</script>


<template>

    <div class="flex items-center justify-center whitespace-nowrap mt-2">

        <!-- تغییر وضعیت -->
        <button
            type="button"
            title="تغییر وضعیت"
            @click="openStatusTab"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-teal-50 hover:text-teal-600"
        >
            <Settings :size="20" />
        </button>

        <!-- مشاهده -->
        <button
            type="button"
            title="مشاهده"
            @click="viewOrder"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-blue-50 hover:text-blue-600"
        >
            <Eye :size="20" />
        </button>

        <!-- ویرایش / جزئیات -->
        <button
            type="button"
            title="جزئیات"
            @click="editOrder"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-amber-50 hover:text-amber-600"
        >
            <Pencil :size="20" />
        </button>

    </div>

</template>