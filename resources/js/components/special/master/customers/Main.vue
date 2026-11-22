<script setup>

import {ref, onMounted} from 'vue'
import axios from 'axios'

import CustomerPanel from './CustomerPanel.vue'
import CustomerDatatable from './CustomerDatatable.vue'

import {
    Users,
    Plus,
    CheckCircle2,
    XCircle,
    CalendarCheck,
} from 'lucide-vue-next'


/* ======================================================
   Panel
====================================================== */
const panelVariable = 'jsPanelVariableNewCustomer'

const customer_datatable_ref = ref(null)


/* ======================================================
   Stats
====================================================== */

const stats = ref({
    total: 0,
    active: 0,
    inactive: 0,
    reservations: 0,
})

const statsLoading = ref(false)


async function loadStats()
{
    statsLoading.value = true
    try
    {
        const response = await axios.get(Routes.customer.get_data)
        if (response.data.success)
        {
            stats.value = {
                total: response.data.data?.total ?? 0,
                active: response.data.data?.active ?? 0,
                inactive: response.data.data?.inactive ?? 0,
                reservations: response.data.data?.reservations ?? 0,
            }
        }
    } catch (error)
    {
        console.error('خطا در دریافت آمار مشتریان:', error)
    } finally
    {
        statsLoading.value = false
    }
}


/* ======================================================
   New Customer
====================================================== */

function open_new_customer()
{
    window.JsPanelV4({

        component: CustomerPanel,

        modal: false,

        reload: true,

        variableName: panelVariable,

        theme: 'info',

        position: 'center',

        z_index: 10000000,

        preFooterText: 'درحال بارگذاری ...',

        headerTitle: 'مشتری جدید',

        ajaxData: {},

        props: {

            customerId: null,

            jsPanelVariableName: panelVariable,

            onSaved: () =>
            {

                customer_datatable_ref
                .value
                ?.refreshGrid()

                loadStats()
            },
        },

        onclose_function: false,

        just_one_panel: true,

        container: 'window',
    })
}


/* ======================================================
   Mounted
====================================================== */

onMounted(() =>
{

    loadStats()

})

</script>


<template>

    <div class="space-y-6">

        <!-- ==================================================
             Header
        ================================================== -->

        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >

            <div>

                <h1
                    class="text-2xl font-bold tracking-tight text-slate-800"
                >
                    مشتریان
                </h1>

                <p class="mt-1 text-sm text-slate-400">
                    مدیریت مشتریان و اطلاعات کاربران سیستم
                </p>

            </div>


            <button
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700"
                @click="open_new_customer"
            >

                <Plus :size="18"/>

                مشتری جدید

            </button>

        </div>


        <!-- ==================================================
             Stats
        ================================================== -->

        <div
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
        >

            <!-- Total -->
            <!-- Total -->
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-slate-400">
                            کل مشتریان
                        </p>

                        <div
                            v-if="statsLoading"
                            class="mt-3 h-8 w-16 animate-pulse rounded-lg bg-slate-100"
                        ></div>

                        <p
                            v-else
                            class="mt-2 text-2xl font-bold text-slate-800"
                        >
                            {{ stats.total }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-teal-600"
                    >
                        <Users :size="22"/>
                    </div>

                </div>
            </div>


            <!-- Active -->
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-slate-400">
                            مشتریان فعال
                        </p>

                        <div
                            v-if="statsLoading"
                            class="mt-3 h-8 w-16 animate-pulse rounded-lg bg-slate-100"
                        ></div>

                        <p
                            v-else
                            class="mt-2 text-2xl font-bold text-green-600"
                        >
                            {{ stats.active }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-green-600"
                    >
                        <CheckCircle2 :size="22"/>
                    </div>

                </div>
            </div>


            <!-- Inactive -->
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-slate-400">
                            مشتریان غیرفعال
                        </p>

                        <div
                            v-if="statsLoading"
                            class="mt-3 h-8 w-16 animate-pulse rounded-lg bg-slate-100"
                        ></div>

                        <p
                            v-else
                            class="mt-2 text-2xl font-bold text-red-600"
                        >
                            {{ stats.inactive }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-600"
                    >
                        <XCircle :size="22"/>
                    </div>

                </div>
            </div>


            <!-- Reservations -->
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-slate-400">
                            مشتریان دارای رزرو
                        </p>

                        <div
                            v-if="statsLoading"
                            class="mt-3 h-8 w-16 animate-pulse rounded-lg bg-slate-100"
                        ></div>

                        <p
                            v-else
                            class="mt-2 text-2xl font-bold text-blue-600"
                        >
                            {{ stats.reservations }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                    >
                        <CalendarCheck :size="22"/>
                    </div>

                </div>
            </div>

        </div>


        <!-- ==================================================
             Datatable
        ================================================== -->

        <CustomerDatatable
            ref="customer_datatable_ref"
        />

    </div>

</template>
