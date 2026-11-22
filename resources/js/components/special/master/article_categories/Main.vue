<script setup>
import { ref } from 'vue'
import CategoryPanel from './CategoryPanel.vue'
import CategoryDatatable from './CategoryDatatable.vue'

import {
    Plus,
} from 'lucide-vue-next'
const panelVariable = 'jsPanelVariableNewCategory'
const category_datatable_ref = ref(null)
function open_new_category()
{
    window.JsPanelV4({
        component: CategoryPanel,
        modal: false,
        reload: true,
        variableName: panelVariable,
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'درحال بارگذاری ...',
        headerTitle: 'دسته‌بندی جدید',
        ajaxData: {},
        props: {
            categoryId: null,
            jsPanelVariableName:
            panelVariable,
            onSaved: () =>
            {
                category_datatable_ref
                .value
                ?.refreshGrid()
            },
        },

        onclose_function: false,
        just_one_panel: true,
        container: 'window',
    })
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-800">دسته‌بندی مطالب</h1>
                <p class="mt-1 text-sm text-slate-400">مدیریت دسته‌بندی مطالب سایت</p>
            </div>

            <button
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700"
                @click="open_new_category()"
            >
                <Plus :size="18" />
                دسته‌بندی جدید
            </button>
        </div>

        <CategoryDatatable ref="category_datatable_ref" />
    </div>
</template>