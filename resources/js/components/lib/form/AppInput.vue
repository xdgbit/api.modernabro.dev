<template>
    <div class="w-full">

        <!-- Label -->
        <label
            v-if="label"
            class="mb-2 block text-sm font-medium text-gray-700"
        >
            {{ label }}

            <span
                v-if="required"
                class="text-red-500"
            >
                *
            </span>
        </label>


        <!-- Input -->
        <input
            ref="inputRef"

            :value="modelValue"

            :type="type"
            :name="name"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :autocomplete="autocomplete"

            @input="handleInput"
            @blur="$emit('blur', $event)"
            @focus="$emit('focus', $event)"

            :class="[
                'w-full rounded-lg border px-3 py-2.5 text-sm text-gray-800',
                'outline-none transition-all duration-200',
                'placeholder:text-gray-400',
                'disabled:cursor-not-allowed disabled:bg-gray-100',

                /*
                |--------------------------------------------------------------------------
                | Error
                |--------------------------------------------------------------------------
                */
                hasError
                    ? [
                        'border-red-500',
                        'bg-red-50',
                        'focus:border-red-500',
                        'focus:ring-2',
                        'focus:ring-red-500/20',
                    ]
                    :

                /*
                |--------------------------------------------------------------------------
                | Valid
                |--------------------------------------------------------------------------
                */
                isValid
                    ? [
                        'border-green-500',
                        'bg-green-50',
                        'focus:border-green-500',
                        'focus:ring-2',
                        'focus:ring-green-500/20',
                    ]
                    :

                /*
                |--------------------------------------------------------------------------
                | Normal
                |--------------------------------------------------------------------------
                */
                    [
                        'border-gray-300',
                        'bg-white',
                        'focus:border-[#e94b5f]',
                        'focus:ring-2',
                        'focus:ring-[#e94b5f]/20',
                    ],
            ]"
        />


        <!-- Error -->
        <div
            v-if="hasError"
            class="mt-1.5 text-xs text-red-500"
        >
            {{ error }}
        </div>

    </div>
</template>


<script setup>
import {
    ref,
    computed,
} from 'vue'


const props = defineProps({

    modelValue: {
        type: [String, Number],
        default: '',
    },

    label: {
        type: String,
        default: '',
    },

    placeholder: {
        type: String,
        default: '',
    },

    type: {
        type: String,
        default: 'text',
    },

    name: {
        type: String,
        default: '',
    },

    autocomplete: {
        type: String,
        default: 'off',
    },

    required: {
        type: Boolean,
        default: false,
    },

    disabled: {
        type: Boolean,
        default: false,
    },

    readonly: {
        type: Boolean,
        default: false,
    },

    /*
    |--------------------------------------------------------------------------
    | Validation error
    |--------------------------------------------------------------------------
    */

    error: {
        type: String,
        default: '',
    },

    /*
    |--------------------------------------------------------------------------
    | وقتی true باشد، validation فعال شده است.
    |--------------------------------------------------------------------------
    */

    validated: {
        type: Boolean,
        default: false,
    },
})


const emit = defineEmits([
    'update:modelValue',
    'blur',
    'focus',
])


const inputRef = ref(null)


/*
|--------------------------------------------------------------------------
| آیا خطا دارد؟
|--------------------------------------------------------------------------
*/

const hasError = computed(() => {
    return Boolean(
        props.error?.trim()
    )
})


/*
|--------------------------------------------------------------------------
| آیا مقدار معتبر دارد؟
|--------------------------------------------------------------------------
|
| فقط بعد از شروع validation سبز می‌شود.
|--------------------------------------------------------------------------
*/

const hasValue = computed(() => {

    const value = props.modelValue

    if (
        value === null ||
        value === undefined
    ) {
        return false
    }

    return String(value).trim().length > 0
})


const isValid = computed(() => {

    return (
        props.validated &&
        hasValue.value &&
        !hasError.value &&
        !props.disabled
    )
})


/*
|--------------------------------------------------------------------------
| Input
|--------------------------------------------------------------------------
*/

const handleInput = (event) => {

    emit(
        'update:modelValue',
        event.target.value
    )
}
</script>