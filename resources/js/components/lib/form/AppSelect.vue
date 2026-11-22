
<template>

    <div
        ref="root"
        dir="rtl"
        class="relative w-full"
    >

        <!-- Label -->

        <label
            v-if="label"
            :for="selectId"
            class="mb-1.5 block text-sm font-medium text-slate-700"
        >
            {{ label }}

            <span
                v-if="required"
                class="text-red-500"
            >
                *
            </span>
        </label>


        <!-- Select -->

        <button
            :id="selectId"
            type="button"
            :disabled="disabled"
            class="flex min-h-11 w-full items-center justify-between rounded-xl border bg-white px-3 py-2 text-right text-sm outline-none transition"
            :class="[
                error
                    ? 'border-red-500'
                    : isOpen
                        ? `${themeClasses.activeBorder} ring-2 ${themeClasses.activeRing}`
                        : 'border-slate-300',

                disabled
                    ? 'cursor-not-allowed bg-slate-100'
                    : 'cursor-pointer'
            ]"
            @click="toggle"
            @keydown="handleKeydown"
        >

            <div class="min-w-0 flex-1">

                <!-- Multiple -->

                <template v-if="multiple">

                    <span
                        v-if="selectedOptions.length === 0"
                        class="text-slate-400"
                    >
                        {{ placeholder }}
                    </span>

                    <div
                        v-else
                        class="flex flex-wrap gap-1"
                    >

                        <span
                            v-for="option in selectedOptions"
                            :key="option[valueKey]"
                            class="rounded-lg px-2 py-1 text-xs"
                            :class="[
                                themeClasses.selectedBg,
                                themeClasses.activeText
                            ]"
                        >
                            {{ getLabel(option) }}
                        </span>

                    </div>

                </template>


                <!-- Single -->

                <template v-else>

                    <span
                        v-if="selectedOption"
                        class="block truncate text-slate-700"
                    >
                        {{ getLabel(selectedOption) }}
                    </span>

                    <span
                        v-else
                        class="text-slate-400"
                    >
                        {{ placeholder }}
                    </span>

                </template>

            </div>


            <!-- Arrow -->

            <svg
                class="mr-2 h-5 w-5 shrink-0 text-slate-400 transition-transform duration-200"
                :class="{ 'rotate-180': isOpen }"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="m6 9 6 6 6-6"/>
            </svg>

        </button>


        <!-- =====================================================
             DESKTOP DROPDOWN
        ====================================================== -->

        <Teleport to="body">

            <div
                v-if="isOpen && !isMobile"
                ref="dropdown"
                :style="dropdownStyle"
                dir="rtl"
                class="fixed overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_20px_50px_rgba(7,31,57,0.20)]"
            >

                <!-- Search -->

                <div
                    v-if="searchable"
                    class="border-b border-slate-100 bg-white p-2"
                >

                    <input
                        ref="searchInput"
                        v-model="search"
                        type="text"
                        :placeholder="searchPlaceholder"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none transition"
                        :class="[
                            `focus:${themeClasses.activeBorder}`,
                            `focus:ring-2`,
                            `focus:${themeClasses.activeRing}`
                        ]"
                        @keydown="handleKeydown"
                        @click.stop
                    >

                </div>


                <!-- Options -->

                <div class="max-h-60 overflow-y-auto p-1">

                    <div
                        v-if="filteredOptions.length === 0"
                        class="px-3 py-6 text-center text-sm text-slate-400"
                    >
                        {{ emptyText }}
                    </div>


                    <button
                        v-for="(option, index) in filteredOptions"
                        :key="option[valueKey]"
                        type="button"
                        :data-highlighted="
                            highlightedIndex === index
                                ? 'true'
                                : 'false'
                        "
                        class="flex w-full items-center rounded-lg px-3 py-2.5 text-right text-sm transition"
                        :class="[
                            highlightedIndex === index
                                ? `${themeClasses.selectedBg} ${themeClasses.activeText}`
                                : 'hover:bg-slate-50',

                            isSelected(option)
                                ? 'font-medium'
                                : ''
                        ]"
                        @mousedown.prevent
                        @click="selectOption(option)"
                    >

                        <div class="min-w-0 flex-1">

                            <div>
                                {{ getLabel(option) }}
                            </div>

                            <div
                                v-if="option.mobile"
                                class="mt-0.5 text-xs text-slate-400"
                            >
                                {{ option.mobile }}
                            </div>

                        </div>


                        <span
                            v-if="isSelected(option)"
                            class="mr-2 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#0663C2]/10"
                        >

                            <svg
                                class="h-3.5 w-3.5 text-[#0663C2]"
                                viewBox="0 0 20 20"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m5 10 3 3 7-7"
                                />
                            </svg>

                        </span>

                    </button>

                </div>

            </div>


            <!-- =====================================================
                 MOBILE BOTTOM SHEET
            ====================================================== -->

            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >

                <div
                    v-if="isOpen && isMobile"
                    class="fixed inset-0 z-[999999] bg-slate-950/45 backdrop-blur-[2px] md:hidden"
                    @click="close"
                >

                    <Transition
                        appear
                        enter-active-class="transition duration-250 ease-out"
                        enter-from-class="translate-y-full"
                        enter-to-class="translate-y-0"
                        leave-active-class="transition duration-200 ease-in"
                        leave-from-class="translate-y-0"
                        leave-to-class="translate-y-full"
                    >

                        <div
                            v-if="isOpen"
                            ref="dropdown"
                            dir="rtl"
                            class="absolute inset-x-0 bottom-0 flex max-h-[92dvh] flex-col overflow-hidden rounded-t-[28px] bg-white shadow-[0_-20px_60px_rgba(7,31,57,0.25)]"
                            @click.stop
                        >

                            <!-- Handle -->

                            <div class="flex shrink-0 justify-center pt-3">

                                <div
                                    class="h-1.5 w-12 rounded-full bg-slate-200"
                                ></div>

                            </div>


                            <!-- Header -->

                            <div
                                class="flex shrink-0 items-center justify-between border-b border-slate-100 px-5 pb-4 pt-4"
                            >

                                <div>

                                    <h3
                                        class="text-base font-extrabold text-[#172B4D]"
                                    >
                                        {{ label || 'انتخاب کنید' }}
                                    </h3>

                                    <p
                                        class="mt-1 text-[11px] text-slate-400"
                                    >
                                        {{ selectedOption
                                        ? getLabel(selectedOption)
                                        : 'مورد موردنظر خود را انتخاب کنید'
                                        }}
                                    </p>

                                </div>


                                <button
                                    type="button"
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200"
                                    aria-label="بستن"
                                    @click="close"
                                >

                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                    >
                                        <path d="M6 6l12 12"/>
                                        <path d="M18 6L6 18"/>
                                    </svg>

                                </button>

                            </div>


                            <!-- Search -->

                            <div
                                v-if="searchable"
                                class="shrink-0 border-b border-slate-100 bg-white px-4 py-3"
                            >

                                <div class="relative">

                                    <svg
                                        class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <circle
                                            cx="11"
                                            cy="11"
                                            r="7"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            d="m20 20-4-4"
                                        />
                                    </svg>

                                    <input
                                        ref="searchInput"
                                        v-model="search"
                                        type="text"
                                        :placeholder="searchPlaceholder"
                                        class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pr-10 pl-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#0663C2] focus:bg-white focus:ring-4 focus:ring-[#0663C2]/10"
                                        @keydown="handleKeydown"
                                        @click.stop
                                    >

                                    <button
                                        v-if="search"
                                        type="button"
                                        class="absolute left-3 top-1/2 flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full bg-slate-200 text-slate-500"
                                        @click="search = ''"
                                    >
                                        <svg
                                            class="h-3.5 w-3.5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path d="M6 6l12 12"/>
                                            <path d="M18 6L6 18"/>
                                        </svg>
                                    </button>

                                </div>

                            </div>


                            <!-- Options -->

                            <div
                                class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-3 pb-[max(16px,env(safe-area-inset-bottom))] pt-2"
                            >

                                <!-- Empty -->

                                <div
                                    v-if="filteredOptions.length === 0"
                                    class="px-4 py-14 text-center"
                                >

                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100"
                                    >
                                        <svg
                                            class="h-6 w-6 text-slate-400"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <circle
                                                cx="11"
                                                cy="11"
                                                r="7"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                d="m20 20-4-4"
                                            />
                                        </svg>
                                    </div>

                                    <p
                                        class="mt-3 text-sm font-medium text-slate-500"
                                    >
                                        {{ emptyText }}
                                    </p>

                                </div>


                                <!-- Options -->

                                <button
                                    v-for="(option, index) in filteredOptions"
                                    :key="option[valueKey]"
                                    type="button"
                                    :data-highlighted="
                                        highlightedIndex === index
                                            ? 'true'
                                            : 'false'
                                    "
                                    class="mb-1 flex min-h-[58px] w-full items-center rounded-2xl px-4 py-3 text-right transition active:scale-[0.99]"
                                    :class="[
                                        highlightedIndex === index
                                            ? `${themeClasses.selectedBg} ${themeClasses.activeText}`
                                            : isSelected(option)
                                                ? 'bg-slate-50'
                                                : 'hover:bg-slate-50',

                                        isSelected(option)
                                            ? 'font-bold'
                                            : ''
                                    ]"
                                    @mousedown.prevent
                                    @click="selectOption(option)"
                                >

                                    <div class="min-w-0 flex-1">

                                        <div
                                            class="truncate text-sm"
                                        >
                                            {{ getLabel(option) }}
                                        </div>

                                        <div
                                            v-if="option.mobile"
                                            class="mt-1 text-xs text-slate-400"
                                        >
                                            {{ option.mobile }}
                                        </div>

                                    </div>


                                    <!-- Selected -->

                                    <span
                                        v-if="isSelected(option)"
                                        class="mr-3 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#0663C2]"
                                    >

                                        <svg
                                            class="h-3.5 w-3.5 text-white"
                                            viewBox="0 0 20 20"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m5 10 3 3 7-7"
                                            />
                                        </svg>

                                    </span>

                                </button>

                            </div>

                        </div>

                    </Transition>

                </div>

            </Transition>

        </Teleport>


        <!-- Error -->

        <p
            v-if="error"
            class="mt-1.5 text-xs text-red-500"
        >
            {{ error }}
        </p>

    </div>

</template>


<script setup>

import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    variant: {
        type: String,
        default: 'admin',
    },

    modelValue: {
        type: [String, Number, Array, Object, null],
        default: null,
    },

    options: {
        type: Array,
        default: () => [],
    },

    label: {
        type: String,
        default: '',
    },

    field: {
        type: String,
        default: '',
    },

    placeholder: {
        type: String,
        default: 'انتخاب کنید',
    },

    searchPlaceholder: {
        type: String,
        default: 'جستجو...',
    },

    emptyText: {
        type: String,
        default: 'موردی پیدا نشد',
    },

    valueKey: {
        type: String,
        default: 'value',
    },

    optionLabel: {
        type: Function,
        default: null,
    },

    searchKeys: {
        type: Array,
        default: () => [],
    },

    multiple: {
        type: Boolean,
        default: false,
    },

    searchable: {
        type: Boolean,
        default: true,
    },

    disabled: {
        type: Boolean,
        default: false,
    },

    required: {
        type: Boolean,
        default: false,
    },

    error: {
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
    'change',
])


/*
|--------------------------------------------------------------------------
| Theme
|--------------------------------------------------------------------------
*/

const themeClasses = computed(() => {

    const themes = {

        admin: {
            activeBorder: 'border-teal-500',
            activeRing: 'ring-teal-100',
            activeText: 'text-teal-700',
            selectedIcon: 'text-teal-600',
            selectedBg: 'bg-teal-50',
        },

        frontend: {
            activeBorder: 'border-[#0663C2]',
            activeRing: 'ring-[#0663C2]/10',
            activeText: 'text-[#0663C2]',
            selectedIcon: 'text-[#0663C2]',
            selectedBg: 'bg-[#0663C2]/10',
        },

    }

    return themes[props.variant] ?? themes.admin

})


/*
|--------------------------------------------------------------------------
| Refs
|--------------------------------------------------------------------------
*/

const root = ref(null)

const dropdown = ref(null)

const searchInput = ref(null)


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const isOpen = ref(false)

const isMobile = ref(false)

const search = ref('')

const highlightedIndex = ref(-1)


/*
|--------------------------------------------------------------------------
| Mobile Detection
|--------------------------------------------------------------------------
*/

const updateMobileState = () => {

    isMobile.value =
        window.matchMedia('(max-width: 767px)').matches

}


/*
|--------------------------------------------------------------------------
| Dropdown Position
|--------------------------------------------------------------------------
*/

const dropdownStyle = ref({
    position: 'fixed',
    top: '0px',
    right: '0px',
    width: '0px',
    zIndex: '999999',
})


const updateDropdownPosition = () => {

    if (
        !root.value ||
        !isOpen.value ||
        isMobile.value
    ) {
        return
    }

    const rect =
        root.value.getBoundingClientRect()

    const gap = 8

    let right =
        window.innerWidth - rect.right

    if (right < 8) {
        right = 8
    }

    const maxRight =
        window.innerWidth - rect.width - 8

    if (right > maxRight) {
        right = Math.max(8, maxRight)
    }

    const top =
        rect.bottom + gap

    dropdownStyle.value = {
        position: 'fixed',
        top: `${top}px`,
        right: `${right}px`,
        width: `${rect.width}px`,
        zIndex: '999999',
    }

}


/*
|--------------------------------------------------------------------------
| ID
|--------------------------------------------------------------------------
*/

const selectId = computed(() => {

    return `app-select-${props.field}`

})


/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

const getLabel = (option) => {

    if (props.optionLabel) {
        return props.optionLabel(option)
    }

    return String(option.label ?? '')

}


/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const filteredOptions = computed(() => {

    const query =
        search.value.trim().toLowerCase()

    if (!query) {
        return props.options
    }

    return props.options.filter(option => {

        if (props.searchKeys.length) {

            return props.searchKeys.some(key => {

                return String(
                    option[key] ?? ''
                )
                .toLowerCase()
                .includes(query)

            })

        }

        return getLabel(option)
        .toLowerCase()
        .includes(query)

    })

})


/*
|--------------------------------------------------------------------------
| Selected Option
|--------------------------------------------------------------------------
*/

const selectedOption = computed(() => {

    if (props.multiple) {
        return null
    }

    return props.options.find(option =>

        option[props.valueKey] ===
        props.modelValue

    ) ?? null

})


/*
|--------------------------------------------------------------------------
| Selected Options
|--------------------------------------------------------------------------
*/

const selectedOptions = computed(() => {

    if (!props.multiple) {
        return []
    }

    if (!Array.isArray(props.modelValue)) {
        return []
    }

    return props.options.filter(option =>

        props.modelValue.includes(
            option[props.valueKey]
        )

    )

})


/*
|--------------------------------------------------------------------------
| Open
|--------------------------------------------------------------------------
*/

const open = async () => {

    if (props.disabled) {
        return
    }

    updateMobileState()

    isOpen.value = true

    highlightedIndex.value = -1

    document.body.style.overflow = 'hidden'

    await nextTick()

    if (!isMobile.value) {
        updateDropdownPosition()
    }

    window.addEventListener(
        'resize',
        handleResize
    )

    window.addEventListener(
        'scroll',
        updateDropdownPosition,
        true
    )

    if (props.searchable) {

        await nextTick()

        searchInput.value?.focus()

    }

}


/*
|--------------------------------------------------------------------------
| Toggle
|--------------------------------------------------------------------------
*/

const toggle = () => {

    if (isOpen.value) {
        close()
    }
    else {
        open()
    }

}


/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

const close = () => {

    isOpen.value = false

    highlightedIndex.value = -1

    search.value = ''

    document.body.style.overflow = ''

    window.removeEventListener(
        'resize',
        handleResize
    )

    window.removeEventListener(
        'scroll',
        updateDropdownPosition,
        true
    )

}


/*
|--------------------------------------------------------------------------
| Resize
|--------------------------------------------------------------------------
*/

const handleResize = () => {

    updateMobileState()

    if (isOpen.value && !isMobile.value) {
        updateDropdownPosition()
    }

}


/*
|--------------------------------------------------------------------------
| Keyboard
|--------------------------------------------------------------------------
*/

const handleKeydown = (event) => {

    if (event.key === 'ArrowDown') {

        event.preventDefault()

        if (!isOpen.value) {
            open()
            return
        }

        if (!filteredOptions.value.length) {
            return
        }

        if (
            highlightedIndex.value >=
            filteredOptions.value.length - 1
        ) {
            highlightedIndex.value = 0
        }
        else {
            highlightedIndex.value++
        }

        scrollHighlighted()

        return

    }


    if (event.key === 'ArrowUp') {

        event.preventDefault()

        if (!isOpen.value) {
            open()
            return
        }

        if (!filteredOptions.value.length) {
            return
        }

        if (highlightedIndex.value <= 0) {

            highlightedIndex.value =
                filteredOptions.value.length - 1

        }
        else {
            highlightedIndex.value--
        }

        scrollHighlighted()

        return

    }


    if (event.key === 'Enter') {

        event.preventDefault()

        if (!isOpen.value) {
            open()
            return
        }

        const option =
            filteredOptions.value[
                highlightedIndex.value
                ]

        if (option) {
            selectOption(option)
        }

        return

    }


    if (event.key === 'Escape') {

        event.preventDefault()

        close()

    }

}


/*
|--------------------------------------------------------------------------
| Select Option
|--------------------------------------------------------------------------
*/

const selectOption = (option) => {

    const value =
        option[props.valueKey]


    /*
    |--------------------------------------------------------------------------
    | Multiple
    |--------------------------------------------------------------------------
    */

    if (props.multiple) {

        const values =
            Array.isArray(props.modelValue)
                ? [...props.modelValue]
                : []

        const index =
            values.indexOf(value)

        if (index === -1) {
            values.push(value)
        }
        else {
            values.splice(index, 1)
        }

        emit(
            'update:modelValue',
            values
        )

        emit(
            'change',
            values
        )

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Single
    |--------------------------------------------------------------------------
    */

    emit(
        'update:modelValue',
        value
    )

    emit(
        'change',
        value
    )

    search.value = ''

    close()

}


/*
|--------------------------------------------------------------------------
| Is Selected
|--------------------------------------------------------------------------
*/

const isSelected = (option) => {

    const value =
        option[props.valueKey]

    if (props.multiple) {

        return (
            Array.isArray(props.modelValue) &&
            props.modelValue.includes(value)
        )

    }

    return props.modelValue === value

}


/*
|--------------------------------------------------------------------------
| Scroll Highlighted
|--------------------------------------------------------------------------
*/

const scrollHighlighted = async () => {

    await nextTick()

    const element =
        dropdown.value?.querySelector(
            '[data-highlighted="true"]'
        )

    element?.scrollIntoView({
        block: 'nearest',
    })

}


/*
|--------------------------------------------------------------------------
| Search Reset
|--------------------------------------------------------------------------
*/

watch(search, () => {

    highlightedIndex.value = -1

})


/*
|--------------------------------------------------------------------------
| Click Outside
|--------------------------------------------------------------------------
*/

const handleClickOutside = (event) => {

    const target = event.target

    if (
        root.value?.contains(target) ||
        dropdown.value?.contains(target)
    ) {
        return
    }

    close()

}


/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(() => {

    updateMobileState()

    document.addEventListener(
        'click',
        handleClickOutside
    )

    window.addEventListener(
        'resize',
        updateMobileState
    )

})


/*
|--------------------------------------------------------------------------
| Before Unmount
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {

    document.removeEventListener(
        'click',
        handleClickOutside
    )

    window.removeEventListener(
        'resize',
        updateMobileState
    )

    window.removeEventListener(
        'resize',
        handleResize
    )

    window.removeEventListener(
        'scroll',
        updateDropdownPosition,
        true
    )

    document.body.style.overflow = ''

})

</script>
