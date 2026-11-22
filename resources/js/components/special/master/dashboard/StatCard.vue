<script setup>
import {
    ArrowDownRight,
    ArrowUpRight,
} from 'lucide-vue-next'

defineProps({
    title: {
        type: String,
        required: true,
    },

    value: {
        type: [String, Number],
        required: true,
    },

    description: {
        type: String,
        default: '',
    },

    icon: {
        type: Object,
        required: true,
    },

    trend: {
        type: Number,
        default: null,
    },

    trendLabel: {
        type: String,
        default: '',
    },

    iconClass: {
        type: String,
        default: 'bg-teal-50 text-teal-600',
    },
})
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
    >
        <div class="flex items-start justify-between">
            <div class="min-w-0">
                <p class="text-sm text-slate-500">
                    {{ title }}
                </p>

                <p class="mt-2 text-2xl font-bold tracking-tight text-slate-800">
                    {{ value }}
                </p>

                <p
                    v-if="description"
                    class="mt-1 text-xs text-slate-400"
                >
                    {{ description }}
                </p>
            </div>

            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                :class="iconClass"
            >
                <component
                    :is="icon"
                    :size="21"
                    :stroke-width="1.8"
                />
            </div>
        </div>

        <div
            v-if="trend !== null"
            class="mt-4 flex items-center gap-1 text-xs"
        >
            <span
                class="inline-flex items-center gap-0.5 rounded-full px-2 py-1 font-medium"
                :class="
                    trend >= 0
                        ? 'bg-emerald-50 text-emerald-600'
                        : 'bg-red-50 text-red-600'
                "
            >
                <ArrowUpRight
                    v-if="trend >= 0"
                    :size="14"
                />

                <ArrowDownRight
                    v-else
                    :size="14"
                />

                {{ Math.abs(trend) }}%
            </span>

            <span class="text-slate-400">
                {{ trendLabel }}
            </span>
        </div>
    </div>
</template>