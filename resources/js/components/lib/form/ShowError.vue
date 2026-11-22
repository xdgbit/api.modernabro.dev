<template>
    <div v-if="items.length">
        <ul class="m-0 mt-1 space-y-1 p-0">
            <li
                v-for="(item, index) in items"
                :key="index"
                class="text-xs text-red-500"
            >
                {{ item }}
            </li>
        </ul>
    </div>
</template>

<script setup>

defineProps({
    items: {
        type: Array,
        default: () => [],
    },
})

</script>