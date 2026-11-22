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

const stockState = computed(() =>
{
    const stock = Number(props.params.data?.stock ?? 0)
    const threshold = Number(props.params.data?.low_stock_threshold ?? 0)

    if (stock <= 0)
    {
        return {
            label: 'ناموجود',
            class: 'bg-rose-100 text-rose-700',
            icon: PackageX,
        }
    }

    if (stock <= threshold)
    {
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
</script>

<template>
    <div class="flex h-full items-center">
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
    </div>
</template>