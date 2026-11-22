
<template>

    <div
        class="flex items-center justify-between rounded-2xl border bg-white p-4 shadow-sm transition-all duration-200"
        :class="[
            disabled
                ? 'cursor-not-allowed opacity-60'
                : 'cursor-pointer hover:shadow-md',

            modelValue
                ? 'border-emerald-200 bg-emerald-50/30'
                : 'border-slate-200 hover:border-emerald-200',
        ]"
        @click="toggle"
    >

        <!-- Content -->

        <div class="flex min-w-0 items-center gap-3">

            <!-- Icon -->

            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-all duration-300"
                :class="
                    modelValue
                        ? 'bg-emerald-100 text-emerald-600'
                        : 'bg-slate-100 text-slate-400'
                "
            >

                <!-- Custom Icon Slot -->

                <slot name="icon">

                    <!-- Mobile / SMS -->

                    <svg
                        v-if="icon === 'mobile'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4v-4z"
                        />
                    </svg>


                    <!-- Email -->

                    <svg
                        v-else-if="icon === 'email'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                        />
                    </svg>


                    <!-- Bell -->

                    <svg
                        v-else-if="icon === 'bell'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                        />
                    </svg>


                    <!-- User -->

                    <svg
                        v-else-if="icon === 'user'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 19a4 4 0 00-6 0M12 12a4 4 0 100-8 4 4 0 000 8z"
                        />
                    </svg>


                    <!-- Users -->

                    <svg
                        v-else-if="icon === 'users'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M17 20a4 4 0 00-8 0M13 12a4 4 0 100-8 4 4 0 000 8zM21 20a4 4 0 00-3-3.87M18 4.13a4 4 0 010 7.75"
                        />
                    </svg>


                    <!-- Lock -->

                    <svg
                        v-else-if="icon === 'lock'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M16 11V7a4 4 0 10-8 0v4M5 11h14v10H5V11z"
                        />
                    </svg>


                    <!-- Shield -->

                    <svg
                        v-else-if="icon === 'shield'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"
                        />
                    </svg>


                    <!-- Check -->

                    <svg
                        v-else-if="icon === 'check'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>


                    <!-- Settings -->

                    <svg
                        v-else-if="icon === 'settings'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06-1.5 1.5-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V20h-2.12v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06-1.5-1.5.06-.06A1.65 1.65 0 009.4 15a1.65 1.65 0 00-1.51-1H7v-2.12h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06 1.5-1.5.06.06a1.65 1.65 0 001.82.33 1.65 1.65 0 001-1.51V6h2.12v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06 1.5 1.5-.06.06A1.65 1.65 0 0019.4 9c.4.61 1.03 1 1.7 1h.09v2.12h-.09a1.65 1.65 0 00-1.7 1.88z"
                        />
                    </svg>


                    <!-- Default -->

                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                        />
                    </svg>

                </slot>

            </div>


            <!-- Text -->

            <div class="min-w-0">

                <div class="font-semibold text-slate-800">
                    {{ label }}
                </div>

                <div
                    v-if="description"
                    class="mt-1 text-xs text-slate-500"
                >
                    {{ description }}
                </div>

            </div>

        </div>


        <!-- Toggle -->

        <div
            class="relative h-7 w-12 shrink-0 rounded-full transition-colors duration-300"
            :class="
                modelValue
                    ? 'bg-emerald-500'
                    : 'bg-slate-300'
            "
            role="switch"
            :aria-checked="modelValue"
        >

            <div
                class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-md transition-transform duration-300"
                :class="
                    modelValue
                        ? 'translate-x-5'
                        : 'translate-x-0'
                "
            ></div>

        </div>

    </div>

</template>


<script setup>

const props = defineProps({

    modelValue: {
        type: Boolean,
        default: false,
    },

    label: {
        type: String,
        required: true,
    },

    description: {
        type: String,
        default: '',
    },

    icon: {
        type: String,
        default: 'mobile',
    },

    disabled: {
        type: Boolean,
        default: false,
    },

})


const emit = defineEmits([
    'update:modelValue',
])


const toggle = () =>
{

    if (props.disabled)
    {
        return
    }

    emit(
        'update:modelValue',
        !props.modelValue
    )

}

</script>
