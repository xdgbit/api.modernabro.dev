<template>

    <div class="w-full">

        <!-- Label -->

        <label
            v-if="label"
            class="mb-2 block text-sm font-medium text-slate-700"
        >

            {{ label }}

            <span
                v-if="required"
                class="mr-1 text-red-500"
            >
                *
            </span>

        </label>


        <!-- Password Input -->

        <input
            v-model="model"
            type="password"
            :name="field"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :autocomplete="autocomplete"
            :class="inputClasses"
            @input="handleInput"
        >


        <!-- Password Rules -->

        <div
            v-if="model"
            class="mt-3 space-y-2"
        >

            <div
                v-for="rule in passwordRules"
                :key="rule.key"
                class="flex items-center gap-2 text-xs"
                :class="
                    rule.valid
                        ? 'text-teal-600'
                        : 'text-slate-400'
                "
            >

                <span
                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full"
                    :class="
                        rule.valid
                            ? 'bg-teal-100 text-teal-600'
                            : 'bg-slate-100 text-slate-400'
                    "
                >
                    {{ rule.valid ? '✓' : '×' }}
                </span>

                <span>
                    {{ rule.label }}
                </span>

            </div>

        </div>


        <!-- Server / Validation Error -->

        <ErrorArea
            :field="field"
        />

    </div>

</template>


<script setup>

import {
    computed,
} from 'vue'

import ErrorArea from './ErrorArea.vue'

import {
    useFormErrors,
} from '@/composables/useFormErrors'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    modelValue: {
        type: String,
        default: '',
    },

    field: {
        type: String,
        required: true,
    },

    label: {
        type: String,
        default: '',
    },

    placeholder: {
        type: String,
        default: '',
    },

    disabled: {
        type: Boolean,
        default: false,
    },

    readonly: {
        type: Boolean,
        default: false,
    },

    required: {
        type: Boolean,
        default: false,
    },

    autocomplete: {
        type: String,
        default: 'new-password',
    },

    inputClass: {
        type: String,
        default: '',
    },

})


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'update:modelValue',
])


/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
*/

const {
    hasError,
    clearField,
} = useFormErrors()


/*
|--------------------------------------------------------------------------
| Model
|--------------------------------------------------------------------------
*/

const model = computed({

    get() {
        return props.modelValue
    },

    set(value) {
        emit('update:modelValue', value)
    },

})


/*
|--------------------------------------------------------------------------
| Password Rules
|--------------------------------------------------------------------------
*/

const passwordRules = computed(() => {

    const password = model.value

    return [

        {
            key: 'length',

            label: 'حداقل ۸ کاراکتر',

            valid: password.length >= 8,
        },

        {
            key: 'uppercase',

            label: 'حداقل یک حرف بزرگ انگلیسی',

            valid: /[A-Z]/.test(password),
        },

        {
            key: 'lowercase',

            label: 'حداقل یک حرف کوچک انگلیسی',

            valid: /[a-z]/.test(password),
        },

        {
            key: 'number',

            label: 'حداقل یک عدد',

            valid: /[0-9]/.test(password),
        },

        {
            key: 'special',

            label: 'حداقل یک کاراکتر خاص',

            valid: /[^A-Za-z0-9]/.test(password),
        },

    ]

})


/*
|--------------------------------------------------------------------------
| Password Valid
|--------------------------------------------------------------------------
*/

const passwordIsValid = computed(() => {

    return passwordRules.value.every(
        rule => rule.valid
    )

})


/*
|--------------------------------------------------------------------------
| Input Classes
|--------------------------------------------------------------------------
*/

const inputClasses = computed(() => {

    let classes =
        'h-11 w-full rounded-xl border bg-white px-4 text-sm outline-none transition disabled:cursor-not-allowed disabled:bg-slate-100 disabled:opacity-70'


    /*
    |--------------------------------------------------------------------------
    | Error
    |--------------------------------------------------------------------------
    */

    if (hasError(props.field)) {

        classes +=
            ' border-red-500 focus:border-red-500 focus:ring-2 focus:ring-red-100'

    }


    /*
    |--------------------------------------------------------------------------
    | Password Rules Invalid
    |--------------------------------------------------------------------------
    */

    else if (
        model.value &&
        !passwordIsValid.value
    ) {

        classes +=
            ' border-red-500 focus:border-red-500 focus:ring-2 focus:ring-red-100'

    }


    /*
    |--------------------------------------------------------------------------
    | Valid
    |--------------------------------------------------------------------------
    */

    else if (
        model.value &&
        passwordIsValid.value
    ) {

        classes +=
            ' border-teal-500 focus:border-teal-500 focus:ring-2 focus:ring-teal-100'

    }


    /*
    |--------------------------------------------------------------------------
    | Default
    |--------------------------------------------------------------------------
    */

    else {

        classes +=
            ' border-slate-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-100'

    }


    /*
    |--------------------------------------------------------------------------
    | Custom Classes
    |--------------------------------------------------------------------------
    */

    if (props.inputClass) {

        classes += ` ${props.inputClass}`

    }


    return classes
})


/*
|--------------------------------------------------------------------------
| Input
|--------------------------------------------------------------------------
*/

const handleInput = () => {

    clearField(props.field)

}

</script>