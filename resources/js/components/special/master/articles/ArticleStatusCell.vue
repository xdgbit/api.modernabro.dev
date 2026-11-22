<script setup>
const props = defineProps({
    params: {
        type: Object,
        required: true,
    },
})

const isActive = props.params?.data?.is_active
</script>

<template>
    <div class="flex h-full items-center">
        <span
            v-if="isActive"
            class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-600"
        >
            فعال
        </span>

        <span
            v-else
            class="inline-flex items-center rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-600"
        >
            غیر فعال
        </span>
    </div>
</template>