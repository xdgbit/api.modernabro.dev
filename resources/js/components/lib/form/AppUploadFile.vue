<template>

    <div
        class="w-full"
        dir="rtl"
    >

        <!-- Label -->

        <label
            v-if="label"
            class="mb-2 block text-sm font-semibold text-slate-700"
        >
            {{ label }}

            <span
                v-if="required"
                class="mr-1 text-red-500"
            >
                *
            </span>
        </label>


        <!-- Card -->

        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >

            <!-- Upload Area -->

            <div
                class="relative transition-colors"
                :class="isDragging ? 'bg-teal-50' : 'bg-slate-50'"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
            >

                <input
                    ref="inputRef"
                    type="file"
                    class="hidden"
                    :accept="accept"
                    :multiple="multiple"
                    @change="handleInput"
                />


                <!-- Empty -->

                <div
                    v-if="!files.length && !initialPreview"
                    class="flex min-h-52 cursor-pointer flex-col items-center justify-center px-6 py-8 text-center"
                    @click="openFilePicker"
                >

                    <div
                        class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-teal-100 bg-teal-50 text-teal-600"
                    >

                        <svg
                            class="h-7 w-7"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 16V4"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m8 8 4-4 4 4"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 14v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"
                            />

                        </svg>

                    </div>


                    <p class="text-sm font-semibold text-slate-700">
                        فایل را اینجا بکشید و رها کنید
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        یا برای انتخاب فایل کلیک کنید
                    </p>


                    <button
                        type="button"
                        class="mt-4 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-teal-700 active:scale-95"
                        @click.stop="openFilePicker"
                    >
                        انتخاب فایل
                    </button>

                </div>


                <!-- Existing Image -->

                <div
                    v-else-if="!files.length && initialPreview"
                    class="p-4"
                >

                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white"
                    >

                        <!-- Preview -->

                        <div
                            class="relative flex h-72 items-center justify-center overflow-hidden bg-slate-100"
                        >

                            <img
                                :src="initialPreview"
                                alt="تصویر فعلی"
                                class="h-full w-full object-contain"
                            />


                            <!-- Remove Existing -->

                            <button
                                type="button"
                                class="absolute left-3 top-3 flex h-9 w-9 items-center justify-center rounded-xl bg-white/90 text-slate-600 shadow-sm backdrop-blur transition hover:bg-red-50 hover:text-red-500"
                                @click="removeInitialPreview"
                            >

                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >

                                    <path
                                        stroke-linecap="round"
                                        d="M6 6l12 12"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        d="M18 6L6 18"
                                    />

                                </svg>

                            </button>

                        </div>


                        <!-- Existing Info -->

                        <div
                            class="flex items-center justify-between gap-3 border-t border-slate-100 px-4 py-3"
                        >

                            <div class="min-w-0">

                                <p
                                    class="text-sm font-semibold text-slate-700"
                                >
                                    تصویر فعلی
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    برای تغییر تصویر، فایل جدید انتخاب کنید.
                                </p>

                            </div>


                            <button
                                type="button"
                                class="shrink-0 rounded-xl bg-teal-50 px-3 py-2 text-xs font-medium text-teal-600 transition hover:bg-teal-100"
                                @click="openFilePicker"
                            >
                                تغییر تصویر
                            </button>

                        </div>

                    </div>

                </div>


                <!-- Files -->

                <div
                    v-else-if="files.length"
                    class="p-4"
                >

                    <!-- Multiple -->

                    <div
                        v-if="multiple"
                        class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-5"
                    >

                        <div
                            v-for="(file, index) in files"
                            :key="getFileKey(file, index)"
                            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white"
                        >

                            <!-- Image -->

                            <div
                                v-if="isImage(file)"
                                class="h-32 overflow-hidden bg-slate-100"
                            >

                                <img
                                    :src="getPreview(file)"
                                    :alt="file.name"
                                    class="h-full w-full object-contain transition duration-300 group-hover:scale-105"
                                />

                            </div>


                            <!-- Non Image -->

                            <div
                                v-else
                                class="flex aspect-square items-center justify-center bg-slate-50"
                            >

                                <svg
                                    class="h-10 w-10 text-slate-400"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M14 2v6h6"
                                    />

                                </svg>

                            </div>


                            <!-- Remove -->

                            <button
                                type="button"
                                class="absolute left-2 top-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white/90 text-slate-500 shadow-sm backdrop-blur transition hover:bg-red-50 hover:text-red-500"
                                @click="removeFile(index)"
                            >

                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >

                                    <path
                                        stroke-linecap="round"
                                        d="M6 6l12 12"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        d="M18 6L6 18"
                                    />

                                </svg>

                            </button>


                            <!-- Info -->

                            <div
                                class="border-t border-slate-100 px-3 py-2"
                            >

                                <p
                                    class="truncate text-xs font-medium text-slate-700"
                                    :title="file.name"
                                >
                                    {{ file.name }}
                                </p>

                                <p
                                    class="mt-1 text-[11px] text-slate-400"
                                >
                                    {{ formatSize(file.size) }}
                                </p>

                            </div>

                        </div>

                    </div>


                    <!-- Single -->

                    <div
                        v-else-if="files[0]"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white"
                    >

                        <!-- Image Preview -->

                        <div
                            v-if="isImage(files[0])"
                            class="relative flex h-72 items-center justify-center overflow-hidden bg-slate-100"
                        >

                            <img
                                :src="getPreview(files[0])"
                                :alt="files[0].name"
                                class="h-full w-full object-contain"
                            />


                            <button
                                type="button"
                                class="absolute left-3 top-3 flex h-9 w-9 items-center justify-center rounded-xl bg-white/90 text-slate-600 shadow-sm backdrop-blur transition hover:bg-red-50 hover:text-red-500"
                                @click="removeFile(0)"
                            >

                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >

                                    <path
                                        stroke-linecap="round"
                                        d="M6 6l12 12"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        d="M18 6L6 18"
                                    />

                                </svg>

                            </button>

                        </div>


                        <!-- Single File -->

                        <div
                            v-else
                            class="flex items-center gap-3 p-4"
                        >

                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-600"
                            >

                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M14 2v6h6"
                                    />

                                </svg>

                            </div>


                            <div class="min-w-0 flex-1">

                                <p
                                    class="truncate text-sm font-semibold text-slate-700"
                                >
                                    {{ files[0].name }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    {{ formatSize(files[0].size) }}

                                    <span v-if="files[0].type">
                                        · {{ files[0].type }}
                                    </span>

                                </p>

                            </div>


                            <button
                                type="button"
                                class="shrink-0 rounded-xl px-3 py-2 text-xs font-medium text-red-500 transition hover:bg-red-50"
                                @click="removeFile(0)"
                            >
                                حذف
                            </button>

                        </div>


                        <!-- Image Info -->

                        <div
                            v-if="files[0] && isImage(files[0])"
                            class="flex items-center justify-between gap-3 border-t border-slate-100 px-4 py-3"
                        >

                            <div class="min-w-0">

                                <p
                                    class="truncate text-sm font-semibold text-slate-700"
                                >
                                    {{ files[0].name }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    {{ formatSize(files[0].size) }}

                                    <span v-if="files[0].type">
                                        · {{ files[0].type }}
                                    </span>

                                </p>

                            </div>


                            <button
                                type="button"
                                class="shrink-0 rounded-xl px-3 py-2 text-xs font-medium text-red-500 transition hover:bg-red-50"
                                @click="removeFile(0)"
                            >
                                حذف
                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Rules -->

            <div
                v-if="accept || maxSize || maxFiles"
                class="flex flex-wrap gap-x-5 gap-y-1.5 border-t border-slate-100 bg-white px-4 py-3 text-xs text-slate-400"
            >

                <span v-if="accept">
                    فرمت مجاز:

                    <strong class="font-medium text-slate-500">
                        {{ acceptLabel }}
                    </strong>

                </span>


                <span v-if="maxSize">
                    حداکثر حجم:

                    <strong class="font-medium text-slate-500">
                        {{ formatSize(maxSize) }}
                    </strong>

                </span>


                <span v-if="maxFiles">
                    حداکثر تعداد:

                    <strong class="font-medium text-slate-500">
                        {{ maxFiles }} فایل
                    </strong>

                </span>

            </div>

        </div>


        <!-- Error -->

        <p
            v-if="displayError"
            class="mt-2 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-xs font-medium leading-6 text-red-600"
        >
            {{ displayError }}
        </p>

    </div>

</template>


<script setup>

import {
    computed,
    getCurrentInstance,
    onBeforeUnmount,
    ref,
} from 'vue'


const props = defineProps({

    modelValue: {
        type: [File, Array],
        default: null,
    },

    /*
    |--------------------------------------------------------------------------
    | Existing Preview
    |--------------------------------------------------------------------------
    */

    initialPreview: {
        type: String,
        default: null,
    },

    label: {
        type: String,
        default: '',
    },

    field: {
        type: String,
        default: '',
    },

    accept: {
        type: String,
        default: '',
    },

    maxSize: {
        type: Number,
        default: null,
    },

    maxFiles: {
        type: Number,
        default: null,
    },

    multiple: {
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


const emit = defineEmits([
    'update:modelValue',
    'error',
    'remove-initial-preview',
])


const instance = getCurrentInstance()

const internalError = ref('')

const displayError = computed(() =>
    props.error || internalError.value
)

const handleUploadError = (message) =>
{
    internalError.value = message

    // اگر والد listener داشته باشد، مدیریت خطا را به والد واگذار می‌کنیم.
    const hasErrorListener = Boolean(
        instance?.vnode.props?.onError
    )

    if (hasErrorListener)
    {
        emit('error', message)
        return
    }

    // در استفاده‌های عادی، Toast داخل خود کامپوننت نمایش داده می‌شود.
    instance?.proxy?.$toast?.error?.(message)
}

const clearUploadError = () =>
{
    internalError.value = ''
}

const inputRef = ref(null)

const isDragging = ref(false)

const files = ref([])

const previews = new Map()


/*
|--------------------------------------------------------------------------
| Initial Value
|--------------------------------------------------------------------------
*/

if (props.modelValue)
{
    files.value = Array.isArray(props.modelValue)
        ? [...props.modelValue]
        : [props.modelValue]
}


/*
|--------------------------------------------------------------------------
| Accept Label
|--------------------------------------------------------------------------
*/

const acceptLabel = computed(() =>
{
    if (!props.accept)
        return ''

    return props.accept
                .split(',')
                .map(item => item.trim())
                .filter(Boolean)
                .join('، ')
})


/*
|--------------------------------------------------------------------------
| Open Picker
|--------------------------------------------------------------------------
*/

const openFilePicker = () =>
{
    inputRef.value?.click()
}


/*
|--------------------------------------------------------------------------
| Input
|--------------------------------------------------------------------------
*/

const handleInput = (event) =>
{
    const selectedFiles = Array.from(
        event.target.files || []
    )

    processFiles(selectedFiles)

    event.target.value = ''
}


/*
|--------------------------------------------------------------------------
| Drop
|--------------------------------------------------------------------------
*/

const handleDrop = (event) =>
{
    isDragging.value = false

    const droppedFiles = Array.from(
        event.dataTransfer.files || []
    )

    processFiles(droppedFiles)
}


/*
|--------------------------------------------------------------------------
| Process Files
|--------------------------------------------------------------------------
*/

const processFiles = (selectedFiles) =>
{
    clearUploadError()

    const validFiles = []

    for (const file of selectedFiles)
    {
        const validationError = validateFile(file)

        if (validationError)
        {
            handleUploadError(validationError)
            continue
        }

        const exists = files.value.some(existingFile =>
            existingFile.name === file.name &&
            existingFile.size === file.size &&
            existingFile.lastModified === file.lastModified
        )

        if (exists)
            continue

        validFiles.push(file)
    }


    if (!validFiles.length)
        return


    /*
    |--------------------------------------------------------------------------
    | Single
    |--------------------------------------------------------------------------
    */

    if (!props.multiple)
    {
        files.value = [validFiles[0]]

        /*
        |--------------------------------------------------------------------------
        | New File Selected
        | Existing preview will no longer be displayed
        |--------------------------------------------------------------------------
        */

        emitValue()

        return
    }


    /*
    |--------------------------------------------------------------------------
    | Multiple
    |--------------------------------------------------------------------------
    */

    let newFiles = [
        ...files.value,
        ...validFiles,
    ]


    /*
    |--------------------------------------------------------------------------
    | Max Files
    |--------------------------------------------------------------------------
    */

    if (props.maxFiles)
    {
        newFiles = newFiles.slice(
            0,
            props.maxFiles
        )
    }


    files.value = newFiles

    emitValue()
}


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

const validateFile = (file) => {

    // بررسی فرمت فایل
    if (props.accept) {

        const acceptedTypes = props.accept
                                   .split(',')
                                   .map(item => item.trim())
                                   .filter(Boolean)

        const validType = acceptedTypes.some(type => {

            if (type.endsWith('/*')) {
                return file.type.startsWith(
                    type.replace('/*', '/')
                )
            }

            if (type.startsWith('.')) {
                return file.name
                           .toLowerCase()
                           .endsWith(type.toLowerCase())
            }

            return file.type === type
        })

        if (!validType) {
            return `فرمت فایل انتخاب‌شده مجاز نیست. فرمت فایل: ${file.type || 'ناشناخته'} | فرمت‌های مجاز: ${acceptLabel.value}`
        }
    }

    // بررسی حجم فایل
    if (
        props.maxSize &&
        file.size > props.maxSize
    ) {
        return `حجم فایل بیش از حد مجاز است. حجم فایل: ${formatSize(file.size)} | حداکثر حجم مجاز: ${formatSize(props.maxSize)}`
    }

    return null
}


/*
|--------------------------------------------------------------------------
| Image
|--------------------------------------------------------------------------
*/

const isImage = (file) =>
{
    return file?.type?.startsWith('image/')
}


/*
|--------------------------------------------------------------------------
| Preview
|--------------------------------------------------------------------------
*/

const getPreview = (file) =>
{
    if (!file)
        return ''

    if (!previews.has(file))
    {
        previews.set(
            file,
            URL.createObjectURL(file)
        )
    }

    return previews.get(file)
}


/*
|--------------------------------------------------------------------------
| File Key
|--------------------------------------------------------------------------
*/

const getFileKey = (file, index) =>
{
    return `${file.name}-${file.size}-${file.lastModified}-${index}`
}


/*
|--------------------------------------------------------------------------
| Remove New File
|--------------------------------------------------------------------------
*/

const removeFile = (index) =>
{
    const file = files.value[index]

    if (!file)
        return

    if (previews.has(file))
    {
        URL.revokeObjectURL(
            previews.get(file)
        )

        previews.delete(file)
    }

    files.value.splice(index, 1)

    emitValue()
}


/*
|--------------------------------------------------------------------------
| Remove Existing Preview
|--------------------------------------------------------------------------
*/

const removeInitialPreview = () =>
{
    emit(
        'update:modelValue',
        props.multiple
            ? []
            : null
    )

    emit('remove-initial-preview')
}


/*
|--------------------------------------------------------------------------
| Emit
|--------------------------------------------------------------------------
*/

const emitValue = () =>
{
    emit(
        'update:modelValue',
        props.multiple
            ? [...files.value]
            : files.value[0] ?? null
    )
}


/*
|--------------------------------------------------------------------------
| Format Size
|--------------------------------------------------------------------------
*/

const formatSize = (bytes) =>
{
    if (!bytes)
        return '0 بایت'

    const units = [
        'بایت',
        'KB',
        'MB',
        'GB',
    ]

    const index = Math.min(
        Math.floor(
            Math.log(bytes) / Math.log(1024)
        ),
        units.length - 1
    )

    const size =
        bytes / Math.pow(1024, index)

    return `${Number(size.toFixed(2))} ${units[index]}`
}


/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() =>
{
    previews.forEach(url =>
    {
        URL.revokeObjectURL(url)
    })

    previews.clear()
})

</script>
