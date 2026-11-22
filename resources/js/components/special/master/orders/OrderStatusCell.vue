<script setup>
import { computed } from 'vue'
import {
    Clock3,
    CheckCircle2,
    PackageCheck,
    Truck,
    PackageOpen,
    XCircle,
    Undo2,
} from 'lucide-vue-next'

const props = defineProps({
    params: {
        type: Object,
        required: true,
    },
})

const icons = {
    Clock3,
    CheckCircle2,
    PackageCheck,
    Truck,
    PackageOpen,
    XCircle,
    Undo2,
}

const statusMap = {
    pending: {
        label: 'در انتظار',
        class: 'bg-slate-100 text-slate-600',
        icon: 'Clock3',
    },
    paid: {
        label: 'پرداخت شده',
        class: 'bg-blue-100 text-blue-700',
        icon: 'CheckCircle2',
    },
    processing: {
        label: 'در حال پردازش',
        class: 'bg-amber-100 text-amber-700',
        icon: 'PackageOpen',
    },
    shipped: {
        label: 'ارسال شده',
        class: 'bg-indigo-100 text-indigo-700',
        icon: 'Truck',
    },
    delivered: {
        label: 'تحویل شده',
        class: 'bg-emerald-100 text-emerald-700',
        icon: 'PackageCheck',
    },
    cancelled: {
        label: 'لغو شده',
        class: 'bg-rose-100 text-rose-700',
        icon: 'XCircle',
    },
    returned: {
        label: 'مرجوع شده',
        class: 'bg-violet-100 text-violet-700',
        icon: 'Undo2',
    },
}

const status = computed(() => {
    const data = props.params.data
    const preset = statusMap[data?.status] ?? {}

    return {
        label: data?.status_label ?? preset.label ?? data?.status ?? '-',
        class: data?.status_class ?? preset.class ?? 'bg-slate-100 text-slate-500',
        icon: icons[data?.status_icon ?? preset.icon] ?? Clock3,
    }
})
</script>

<template>
    <div class="flex h-full items-center">
        <span
            :class="[
                'inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold',
                status.class,
            ]"
        >
            <component
                :is="status.icon"
                :size="14"
                :stroke-width="2"
            />

            {{ status.label }}
        </span>
    </div>
</template>