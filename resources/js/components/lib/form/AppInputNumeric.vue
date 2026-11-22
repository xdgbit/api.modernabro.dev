<template>

    <div class="mb-6">

        <!-- Label -->
        <label
            v-if="label"
            class="mb-2 block text-sm font-medium text-slate-700"
        >
            {{ label }}
        </label>


        <!-- Input -->
        <input
            ref="inputRef"
            :value="displayValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :inputmode="allowDecimal ? 'decimal' : 'numeric'"
            :min="min"
            :max="max"
            :class="[
                'w-full rounded-xl border bg-white px-4 py-3 text-sm outline-none transition',
                'focus:ring-2 disabled:cursor-not-allowed disabled:bg-slate-100',
                error
                    ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
                    : 'border-slate-300 focus:border-teal-500 focus:ring-teal-100',

                {
                    'text-left': textAlign === 'left',
                    'text-center': textAlign === 'center',
                    'text-right': textAlign === 'right',
                },
            ]"
            @input="handleInput"
            @keydown="handleKeydown"
            @paste="handlePaste"
            @blur="handleBlur"
            @focus="handleFocus"
        />


        <!-- Error -->
        <p
            v-if="error"
            class="mt-1 text-xs text-red-500"
        >
            {{ error }}
        </p>

    </div>

</template>


<script setup>

import {
    computed,
    nextTick,
    ref,
} from 'vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    modelValue: {
        type: [Number, String, null],
        default: null,
    },

    label: {
        type: String,
        default: '',
    },

    placeholder: {
        type: String,
        default: '',
    },

    field: {
        type: String,
        default: '',
    },

    error: {
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

    /**
     * چپ / وسط / راست
     */
    textAlign: {
        type: String,
        default: 'center',

        validator: value => [
            'left',
            'center',
            'right',
        ].includes(value),
    },

    /**
     * اجازه اعشار
     */
    allowDecimal: {
        type: Boolean,
        default: false,
    },

    /**
     * حداکثر تعداد رقم اعشار
     *
     * مثال:
     * decimal-places="2"
     */
    decimalPlaces: {
        type: Number,
        default: 2,
    },

    /**
     * حداقل مقدار
     */
    min: {
        type: Number,
        default: null,
    },

    /**
     * حداکثر مقدار
     */
    max: {
        type: Number,
        default: null,
    },

})


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'update:modelValue',
    'blur',
    'focus',
])


/*
|--------------------------------------------------------------------------
| Refs
|--------------------------------------------------------------------------
*/

const inputRef = ref(null)


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * تبدیل اعداد فارسی و عربی به انگلیسی
 */
const normalizeDigits = value =>
{

    return String(value)
    .replace(/[۰-۹]/g, digit =>
        String(
            digit.charCodeAt(0) - 1776
        )
    )
    .replace(/[٠-٩]/g, digit =>
        String(
            digit.charCodeAt(0) - 1632
        )
    )

}


/**
 * حذف جداکننده‌ها و کاراکترهای اضافی
 */
const sanitize = value =>
{

    value = normalizeDigits(value)

    if (props.allowDecimal)
    {

        value = value
        .replace(/,/g, '')
        .replace(/[^\d.]/g, '')

        /*
         * فقط یک نقطه
         */
        const parts = value.split('.')

        let integer = parts.shift() ?? ''

        let decimal = parts.join('')


        /*
         * حذف صفرهای اضافی ابتدای عدد
         *
         * 0002500 -> 2500
         */
        integer =
            integer.replace(/^0+(?=\d)/, '')


        /*
         * محدودیت اعشار
         */
        if (
            props.decimalPlaces >= 0 &&
            decimal.length > props.decimalPlaces
        )
        {
            decimal =
                decimal.slice(
                    0,
                    props.decimalPlaces
                )
        }


        /*
         * اگر کاربر فقط "." زده باشد
         */
        if (!integer && value.startsWith('.'))
        {
            integer = '0'
        }


        return decimal !== ''
            ? `${integer}.${decimal}`
            : (
                value.includes('.')
                    ? `${integer}.`
                    : integer
            )

    }


    return value
    .replace(/,/g, '')
    .replace(/\D/g, '')
    .replace(/^0+(?=\d)/, '')

}


/**
 * فرمت سه‌رقمی
 */
const formatNumber = value =>
{

    if (
        value === null ||
        value === undefined ||
        value === ''
    )
    {
        return ''
    }


    const stringValue =
        normalizeDigits(value)


    if (props.allowDecimal)
    {

        const [
            integerPart,
            decimalPart,
        ] = stringValue.split('.')


        const formattedInteger =
            Number(integerPart || 0)
            .toLocaleString('en-US')


        if (decimalPart !== undefined)
        {
            return `${formattedInteger}.${decimalPart}`
        }


        return formattedInteger

    }


    return Number(stringValue)
    .toLocaleString('en-US')

}


/*
|--------------------------------------------------------------------------
| Display Value
|--------------------------------------------------------------------------
*/

const displayValue = computed(() =>
{

    return formatNumber(
        props.modelValue
    )

})


/*
|--------------------------------------------------------------------------
| Caret Helpers
|--------------------------------------------------------------------------
*/

/**
 * تعداد رقم‌ها قبل از caret
 */
const countDigitsBefore = (
    value,
    position
) =>
{

    return normalizeDigits(
        value
    )
    .slice(0, position)
    .replace(/\D/g, '')
        .length

}


/**
 * پیدا کردن محل جدید caret
 * بعد از اضافه شدن comma
 */
const findCaretPosition = (
    formatted,
    digitCount
) =>
{

    if (digitCount <= 0)
    {
        return 0
    }


    let digits = 0


    for (
        let index = 0;
        index < formatted.length;
        index++
    )
    {

        if (/\d/.test(formatted[index]))
        {
            digits++
        }


        if (digits >= digitCount)
        {
            return index + 1
        }

    }


    return formatted.length

}


/**
 * اعمال caret
 */
const setCaret = position =>
{

    nextTick(() =>
    {

        if (!inputRef.value)
        {
            return
        }


        inputRef.value.setSelectionRange(
            position,
            position
        )

    })

}


/*
|--------------------------------------------------------------------------
| Input
|--------------------------------------------------------------------------
*/

const handleInput = event =>
{

    const input = event.target

    const oldValue = input.value

    const oldCursor =
        input.selectionStart ??
        oldValue.length


    /*
     * تعداد رقم‌هایی که قبل از caret بودند
     */
    const digitsBeforeCursor =
        countDigitsBefore(
            oldValue,
            oldCursor
        )


    /*
     * مقدار خام
     */
    let rawValue =
        sanitize(oldValue)


    /*
     * اگر خالی شد
     */
    if (rawValue === '')
    {

        emit(
            'update:modelValue',
            null
        )

        return

    }


    /*
     * مقدار عددی
     */
    let numericValue =
        Number(rawValue)


    /*
     * min
     */
    if (
        props.min !== null &&
        numericValue < props.min
    )
    {
        numericValue = props.min

        rawValue =
            String(numericValue)
    }


    /*
     * max
     */
    if (
        props.max !== null &&
        numericValue > props.max
    )
    {
        numericValue = props.max

        rawValue =
            String(numericValue)
    }


    /*
     * ارسال مقدار واقعی
     */
    emit(
        'update:modelValue',
        numericValue
    )


    /*
     * فرمت جدید
     */
    const formatted =
        formatNumber(rawValue)


    /*
     * caret جدید
     */
    const newCaret =
        findCaretPosition(
            formatted,
            digitsBeforeCursor
        )


    /*
     * Vue مقدار input را آپدیت می‌کند
     * و سپس caret را تنظیم می‌کنیم.
     */
    nextTick(() =>
    {

        if (!inputRef.value)
        {
            return
        }


        inputRef.value.value =
            formatted


        inputRef.value.setSelectionRange(
            newCaret,
            newCaret
        )

    })

}


/*
|--------------------------------------------------------------------------
| Keydown
|--------------------------------------------------------------------------
*/

const handleKeydown = event =>
{

    /*
     * کلیدهای کنترلی
     */
    if (
        event.ctrlKey ||
        event.metaKey
    )
    {
        return
    }


    const allowedKeys = [
        'Backspace',
        'Delete',
        'ArrowLeft',
        'ArrowRight',
        'ArrowUp',
        'ArrowDown',
        'Home',
        'End',
        'Tab',
    ]


    if (
        allowedKeys.includes(event.key)
    )
    {
        return
    }


    /*
     * اعداد انگلیسی
     */
    if (
        /\d/.test(event.key)
    )
    {
        return
    }


    /*
     * اعداد فارسی
     */
    if (
        /[۰-۹٠-٩]/.test(event.key)
    )
    {
        return
    }


    /*
     * اعشار
     */
    if (
        props.allowDecimal &&
        (
            event.key === '.' ||
            event.key === '٫'
        )
    )
    {

        const value =
            inputRef.value?.value ?? ''


        if (!value.includes('.'))
        {
            return
        }

    }


    /*
     * سایر کاراکترها ممنوع
     */
    event.preventDefault()

}


/*
|--------------------------------------------------------------------------
| Paste
|--------------------------------------------------------------------------
*/

const handlePaste = event =>
{

    event.preventDefault()


    const pasted =
        event.clipboardData
             ?.getData('text') ?? ''


    const input = inputRef.value


    if (!input)
    {
        return
    }


    /*
     * مقدار فعلی
     */
    const currentValue =
        input.value


    const start =
        input.selectionStart ?? 0


    const end =
        input.selectionEnd ?? start


    /*
     * متن قبل و بعد selection
     */
    const newValue =
        currentValue.slice(0, start) +
        pasted +
        currentValue.slice(end)


    /*
     * caret منطقی قبل از format
     */
    const cursorBeforeFormat =
        start + pasted.length


    /*
     * تعداد رقم قبل از caret
     */
    const digitsBeforeCursor =
        countDigitsBefore(
            newValue,
            cursorBeforeFormat
        )


    /*
     * sanitize
     */
    const rawValue =
        sanitize(newValue)


    if (rawValue === '')
    {

        emit(
            'update:modelValue',
            null
        )

        return

    }


    const numericValue =
        Number(rawValue)


    /*
     * min/max
     */
    let finalValue = numericValue


    if (
        props.min !== null &&
        finalValue < props.min
    )
    {
        finalValue = props.min
    }


    if (
        props.max !== null &&
        finalValue > props.max
    )
    {
        finalValue = props.max
    }


    emit(
        'update:modelValue',
        finalValue
    )


    const formatted =
        formatNumber(finalValue)


    const newCaret =
        findCaretPosition(
            formatted,
            digitsBeforeCursor
        )


    nextTick(() =>
    {

        if (!inputRef.value)
        {
            return
        }


        inputRef.value.value =
            formatted


        inputRef.value.setSelectionRange(
            newCaret,
            newCaret
        )

    })

}


/*
|--------------------------------------------------------------------------
| Focus
|--------------------------------------------------------------------------
*/

const handleFocus = event =>
{

    emit(
        'focus',
        event
    )

}


/*
|--------------------------------------------------------------------------
| Blur
|--------------------------------------------------------------------------
*/

const handleBlur = event =>
{

    /*
     * هنگام blur مقدار نهایی را
     * دوباره normalize می‌کنیم.
     */
    if (
        props.modelValue !== null &&
        props.modelValue !== undefined &&
        props.modelValue !== ''
    )
    {

        let value =
            Number(props.modelValue)


        if (
            props.min !== null &&
            value < props.min
        )
        {
            value = props.min
        }


        if (
            props.max !== null &&
            value > props.max
        )
        {
            value = props.max
        }


        if (
            value !== props.modelValue
        )
        {

            emit(
                'update:modelValue',
                value
            )

        }

    }


    emit(
        'blur',
        event
    )

}

</script>