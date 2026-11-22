<script setup>
import { computed } from 'vue'
import {
    CheckCircle2,
    Clock3,
    CircleDollarSign,
    RotateCcw,
    XCircle,
} from 'lucide-vue-next'

const props = defineProps({
    params: {
        type: Object,
        required: true,
    },
})

const icons = {
    CheckCircle2,
    Clock3,
    CircleDollarSign,
    RotateCcw,
    XCircle,
}

const paymentMap = {
    unpaid: {
        label: 'پرداخت نشده',
        class: 'bg-rose-100 text-rose-700',
        icon: 'XCircle',
    },
    partially_paid: {
        label: 'پرداخت بخشی',
        class: 'bg-amber-100 text-amber-700',
        icon: 'Clock3',
    },
    paid: {
        label: 'پرداخت شده',
        class: 'bg-emerald-100 text-emerald-700',
        icon: 'CheckCircle2',
    },
    refunded: {
        label: 'بازپرداخت شده',
        class: 'bg-slate-100 text-slate-600',
        icon: 'RotateCcw',
    },
}

const payment = computed(() => {
    const data = props.params.data
    const preset = paymentMap[data?.payment_status] ?? {}

    return {
        label: data?.payment_status_label ?? preset.label ?? data?.payment_status ?? '-',
        class: data?.payment_status_class ?? preset.class ?? 'bg-slate-100 text-slate-500',
        icon: icons[data?.payment_status_icon ?? preset.icon] ?? CircleDollarSign,
    }
})
</script>

<template>
    <div class="flex h-full items-center">
        <span
            :class="[
                'inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold',
                payment.class,
            ]"
        >
            <component
                :is="payment.icon"
                :size="14"
                :stroke-width="2"
            />

            {{ payment.label }}
        </span>
    </div>
</template>