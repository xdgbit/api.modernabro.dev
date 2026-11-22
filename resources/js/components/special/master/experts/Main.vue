<script setup>

import {
    onMounted,
    ref,
} from 'vue'

import axios from 'axios'

import ExpertDatatable from './ExpertDatatable.vue'
import ExpertPanel from './ExpertPanel.vue'
import {
    ClipboardCheck,
    Clock3,
    Plus,
} from 'lucide-vue-next'


const expert_datatable_ref =
    ref(null)


const stats = ref({
    total: 0,
    active: 0,
    inactive: 0,
    busy: 0,
    pending: 0,
    assigned: 0,
})


const statsLoading = ref(false)

async function loadStats()
{
    statsLoading.value = true

    try
    {
        const response = await axios.get(Routes.admin.expert.get_data)


        if (response.data.success)
        {
            stats.value = {
                total: response.data.data?.total ?? 0,


                active:
                    response.data.data?.active ??
                    0,

                inactive:
                    response.data.data?.inactive ??
                    0,

                busy:
                    response.data.data?.busy ??
                    0,

                pending:
                    response.data.data?.pending ??
                    0,

                assigned:
                    response.data.data?.assigned ??
                    0,
            }
        }
    } catch (error)
    {
        console.error(
            'خطا در دریافت آمار کارشناسان:',
            error
        )
    } finally
    {
        statsLoading.value = false
    }
}


function open_new_expert()
{
    window.JsPanelV4({
        component: ExpertPanel,
        modal: false,
        reload: true,
        variableName: 'jsPanelVariableNewExpert',
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'درحال بارگذاری ...',
        headerTitle: 'کارشناس جدید',
        ajaxData: {},

        props: {
            expertId: null,

            jsPanelVariableName:
                'jsPanelVariableNewExpert',

            onSaved: () =>
            {
                expert_datatable_ref
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


onMounted(() =>
{
    loadStats()
})

</script>


<template>

    <div class="space-y-5">


        <!-- Header -->

        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >

            <div>

                <h1
                    class="text-xl font-bold tracking-tight text-slate-800 lg:text-2xl"
                >
                    مدیریت کارشناسان
                </h1>

                <p
                    class="mt-1 text-sm text-slate-400"
                >
                    مدیریت کارشناسان، وضعیت فعالیت و درخواست‌های ارجاع‌شده
                </p>

            </div>


            <button
                type="button"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700"
                @click="open_new_expert"
            >

                <Plus :size="17"/>

                کارشناس جدید

            </button>

        </div>


        <!-- Operational Summary -->

        <div
            class="grid grid-cols-1 gap-4 lg:grid-cols-2"
        >


            <!-- Expert Status -->

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >

                <div class="mb-4">

                    <h2
                        class="text-sm font-bold text-slate-800"
                    >
                        وضعیت کارشناسان
                    </h2>

                    <p
                        class="mt-1 text-xs text-slate-400"
                    >
                        نمای کلی وضعیت فعالیت کارشناسان
                    </p>

                </div>


                <div
                    class="grid grid-cols-1 gap-2 sm:grid-cols-2"
                >

                    <div
                        class="flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3"
                    >

                        <span class="text-xs text-slate-600">
                            فعال
                        </span>

                        <strong
                            class="text-sm font-bold text-emerald-600"
                        >
                            {{ stats.active }}
                        </strong>

                    </div>


                    <div
                        class="flex items-center justify-between rounded-xl bg-slate-100 px-4 py-3"
                    >

                        <span class="text-xs text-slate-600">
                            غیرفعال
                        </span>

                        <strong
                            class="text-sm font-bold text-slate-600"
                        >
                            {{ stats.inactive }}
                        </strong>

                    </div>


                    <div
                        class="flex items-center justify-between rounded-xl bg-violet-50 px-4 py-3"
                    >

                        <span class="text-xs text-slate-600">
                            در حال بررسی
                        </span>

                        <strong
                            class="text-sm font-bold text-violet-600"
                        >
                            {{ stats.busy }}
                        </strong>

                    </div>


                    <div
                        class="flex items-center justify-between rounded-xl bg-teal-50 px-4 py-3"
                    >

                        <span class="text-xs text-slate-600">
                            ارجاع‌شده
                        </span>

                        <strong
                            class="text-sm font-bold text-teal-600"
                        >
                            {{ stats.assigned }}
                        </strong>

                    </div>

                </div>

            </div>


            <!-- Required Actions -->

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >

                <div class="mb-4">

                    <h2
                        class="text-sm font-bold text-slate-800"
                    >
                        نیازمند پیگیری
                    </h2>

                    <p
                        class="mt-1 text-xs text-slate-400"
                    >
                        درخواست‌هایی که نیاز به اقدام کارشناس دارند
                    </p>

                </div>


                <div class="space-y-2">


                    <div
                        class="flex items-center justify-between rounded-xl border border-amber-100 bg-amber-50 px-4 py-3"
                    >

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-amber-600 shadow-sm"
                            >
                                <Clock3 :size="17"/>
                            </div>

                            <div>

                                <div
                                    class="text-xs font-semibold text-slate-700"
                                >
                                    درخواست‌های منتظر تخصیص
                                </div>

                                <div
                                    class="mt-0.5 text-[11px] text-slate-400"
                                >
                                    نیازمند ارجاع به کارشناس
                                </div>

                            </div>

                        </div>


                        <strong
                            class="text-lg font-bold text-amber-700"
                        >
                            {{ stats.pending }}
                        </strong>

                    </div>


                    <div
                        class="flex items-center justify-between rounded-xl border border-violet-100 bg-violet-50 px-4 py-3"
                    >

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-violet-600 shadow-sm"
                            >
                                <ClipboardCheck :size="17"/>
                            </div>

                            <div>

                                <div
                                    class="text-xs font-semibold text-slate-700"
                                >
                                    درخواست‌های در حال بررسی
                                </div>

                                <div
                                    class="mt-0.5 text-[11px] text-slate-400"
                                >
                                    توسط کارشناسان در حال پیگیری است
                                </div>

                            </div>

                        </div>


                        <strong
                            class="text-lg font-bold text-violet-700"
                        >
                            {{ stats.busy }}
                        </strong>

                    </div>

                </div>

            </div>

        </div>


        <!-- Datatable -->

        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >

            <div
                class="flex flex-col gap-1 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
            >

                <div>

                    <h2
                        class="text-sm font-bold text-slate-800"
                    >
                        فهرست کارشناسان
                    </h2>

                    <p
                        class="mt-1 text-xs text-slate-400"
                    >
                        مشاهده و مدیریت کارشناسان سیستم
                    </p>

                </div>

            </div>


            <div class="p-1">

                <ExpertDatatable
                    ref="expert_datatable_ref"
                />

            </div>

        </div>

    </div>

</template>
