<script setup>

import { computed } from 'vue'


const props = defineProps({

    params: {
        type: Object,
        required: true,
    },

})


const image = computed(() =>
    props.params.value
)

</script>


<template>

    <div class="flex h-full items-center">

        <img
            v-if="image"
            :src="image"
            :alt="params.data?.title ?? 'مقاله'"
            class="h-10 w-14 rounded-lg object-cover"
        >


        <div
            v-else
            class="flex h-10 w-14 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-400"
        >
            بدون تصویر
        </div>

    </div>

</template>