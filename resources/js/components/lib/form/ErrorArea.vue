<template>
    <div
        v-if="errors.length"
        class="mt-1"
    >
        <ul class="space-y-1">
            <li
                v-for="(error, index) in errors"
                :key="index"
                class="text-xs text-red-500"
            >
                {{ error }}
            </li>
        </ul>
    </div>
</template>

<script setup>
import { computed } from 'vue'

import { useFormErrors } from '@/composables/useFormErrors'

const props = defineProps({
    field: {
        type: String,
        required: true,
    },
})

const {
    getErrors,
} = useFormErrors()

const errors = computed(() => {
    return getErrors(props.field)
})
</script>