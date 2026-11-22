
<script setup>

import {
    Pencil,
    RefreshCw,
} from 'lucide-vue-next'


/* ======================================================
   Props
====================================================== */

const props = defineProps({

    params: {
        type: Object,
        default: () => ({}),
    },

})


/* ======================================================
   Customer
====================================================== */

const customer = props.params?.data


/* ======================================================
   Edit
====================================================== */

const editCustomer = () => {

    if (
        typeof props.params?.onEdit === 'function'
    ) {

        props.params.onEdit(customer)

    }

}


/* ======================================================
   Refresh
====================================================== */

const refreshCustomer = () => {

    if (
        typeof props.params?.onRefresh === 'function'
    ) {

        props.params.onRefresh()

    }

}

</script>


<template>

    <div
        class="flex h-full items-center justify-center gap-1"
        dir="rtl"
    >

        <!-- ==================================================
             Edit
        ================================================== -->

        <button
            type="button"
            title="ویرایش مشتری"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-teal-50 hover:text-teal-600"
            @click="editCustomer"
        >

            <Pencil
                :size="16"
                :stroke-width="1.8"
            />

        </button>


        <!-- ==================================================
             Refresh
        ================================================== -->

        <button
            type="button"
            title="بروزرسانی"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
            @click="refreshCustomer"
        >

            <RefreshCw
                :size="16"
                :stroke-width="1.8"
            />

        </button>

    </div>

</template>

