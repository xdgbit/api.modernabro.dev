<script setup>
import { computed } from 'vue'
import {
    PackageX,
    AlertTriangle,
    PackageCheck,
} from 'lucide-vue-next'

const props = defineProps({
    params: {
        type: Object,
        required: true,
    },
})

const stockState = computed(() => {
    const data = props.params.data

    const stock = Number(data?.stock ?? 0)
    const threshold = Number(data?.low_stock_threshold ?? 0)

    if (stock <= 0) {
        return {
            label: 'ناموجود',
            class: 'bg-rose-100 text-rose-700',
            icon: PackageX,
        }
    }

    if (stock <= threshold) {
        return {
            label: 'کم‌موجودی',
            class: 'bg-amber-100 text-amber-700',
            icon: AlertTriangle,
        }
    }

    return {
        label: 'موجود',
        class: 'bg-emerald-100 text-emerald-700',
        icon: PackageCheck,
    }
})

const formattedStock = computed(() =>
    Number(props.params.data?.stock ?? 0).toLocaleString('fa-IR')
)

const formattedThreshold = computed(() =>
    Number(props.params.data?.low_stock_threshold ?? 0).toLocaleString('fa-IR')
)
</script>

<template>
    <div class="flex h-full items-center gap-2">
        <span
            :class="[
                'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold',
                stockState.class,
            ]"
        >
            <component
                :is="stockState.icon"
                :size="13"
                :stroke-width="2"
            />
            {{ stockState.label }}
        </span>

        <span class="text-xs font-bold text-slate-700">
            {{ formattedStock }}
        </span>

        <span class="text-[10px] text-slate-400">
            / حداقل {{ formattedThreshold }}
        </span>
    </div>
</template>