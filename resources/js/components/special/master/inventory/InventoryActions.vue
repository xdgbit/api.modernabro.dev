<script setup>
import { PackagePlus } from 'lucide-vue-next'

const props = defineProps({
    params: {
        type: Object,
        required: true,
    },
})

function openStock()
{
    if (!props.params.data?.id)
    {
        return
    }

    // تابع openStock از InventoryDatatable در cellRendererParams می‌آید
    props.params.openStock?.(props.params.data)
}
</script>

<template>
    <div class="mt-2 flex items-center justify-center whitespace-nowrap">
        <button
            type="button"
            title="به‌روزرسانی موجودی"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-teal-50 hover:text-teal-600"
            @click="openStock"
        >
            <PackagePlus :size="20" />
        </button>
    </div>
</template>