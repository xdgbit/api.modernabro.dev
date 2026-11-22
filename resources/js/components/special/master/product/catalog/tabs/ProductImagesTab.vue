<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
    productId: {
        type: [Number, String],
        required: true,
    },

    readOnly: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['saved'])

const form = ref({
    images: [],
})

const images = ref([])
const saving = ref(false)
const loading = ref(false)
const uploadError = ref('')
const showUpload = ref(false)

const handleUploadError = (message) =>
{
    uploadError.value = message
    $toast.error(message)
}

const loadImages = async () =>
{
    loading.value = true

    try
    {
        const response = await axios.get(
            Routes.product.images.list(props.productId)
        )

        images.value = response.data.data ?? response.data
    }
    catch (error)
    {
        console.error(error)
        $toast.error('خطا در دریافت تصاویر محصول.')
    }
    finally
    {
        loading.value = false
    }
}

const uploadImages = async () =>
{
    if (!form.value.images.length)
    {
        return
    }

    saving.value = true

    try
    {
        const formData = new FormData()

        form.value.images.forEach((file) =>
        {
            formData.append('images[]', file)
        })

        await axios.post(
            Routes.product.images.upload(props.productId),
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            }
        )

        form.value.images = []
        showUpload.value = false

        await loadImages()
        emit('saved')

        $toast.success('تصاویر با موفقیت آپلود شد.')
    }
    catch (error)
    {
        console.error(error)
        $toast.error(
            error.response?.data?.message ??
            'آپلود تصاویر با خطا مواجه شد.'
        )
    }
    finally
    {
        saving.value = false
    }
}

const setCover = async (image) =>
{
    if (image.is_cover || props.readOnly)
    {
        return
    }

    try
    {
        await axios.put(
            Routes.product.images.cover(props.productId, image.id)
        )

        images.value.forEach((item) =>
        {
            item.is_cover = item.id === image.id
        })

        emit('saved')
    }
    catch (error)
    {
        console.error(error)
        $toast.error('تنظیم تصویر اصلی انجام نشد.')
    }
}

const deleteImage = async (image) =>
{
    if (!confirm('آیا از حذف این تصویر مطمئن هستید؟'))
    {
        return
    }

    try
    {
        await axios.delete(
            Routes.product.images.delete(props.productId, image.id)
        )

        images.value = images.value.filter(
            (item) => item.id !== image.id
        )

        emit('saved')
    }
    catch (error)
    {
        console.error(error)
        $toast.error('حذف تصویر انجام نشد.')
    }
}

const moveImage = async (index, direction) =>
{
    const newIndex = index + direction

    if (newIndex < 0 || newIndex >= images.value.length)
    {
        return
    }

    const list = [...images.value]
    ;[list[index], list[newIndex]] = [list[newIndex], list[index]]
    images.value = list

    try
    {
        await axios.put(
            Routes.product.images.order(props.productId),
            {
                ids: images.value.map((item) => item.id),
            }
        )

        emit('saved')
    }
    catch (error)
    {
        console.error(error)
        $toast.error('ترتیب تصاویر ذخیره نشد.')
        await loadImages()
    }
}

onMounted(loadImages)
</script>

<template>
    <div class="space-y-6" dir="rtl">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800">
                تصاویر محصول
            </h3>

            <button
                v-if="!readOnly"
                type="button"
                class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200"
                @click="showUpload = !showUpload"
            >
                {{ showUpload ? 'بستن آپلود' : 'افزودن تصویر' }}
            </button>
        </div>

        <!-- Upload -->
        <div
            v-if="showUpload && !readOnly"
            class="rounded-xl border border-slate-200 bg-white p-5"
        >
            <AppUploadFile
                v-model="form.images"
                label="تصاویر محصول"
                accept="image/jpeg,image/png,image/webp"
                :max-size="2 * 1024 * 1024"
                :max-files="10"
                :error="uploadError"
                multiple
                @error="handleUploadError"
            />

            <button
                type="button"
                class="mt-4 rounded-lg bg-teal-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="saving || !form.images.length"
                @click="uploadImages"
            >
                {{ saving ? 'در حال آپلود...' : 'آپلود تصاویر' }}
            </button>
        </div>

        <!-- List -->
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div
                v-if="loading"
                class="py-10 text-center text-sm text-slate-400"
            >
                در حال دریافت تصاویر...
            </div>

            <div
                v-else-if="images.length"
                class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4"
            >
                <div
                    v-for="(image, index) in images"
                    :key="image.id"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white"
                >
                    <div class="aspect-square bg-slate-100">
                        <img
                            :src="image.image_src"
                            :alt="image.name || 'تصویر محصول'"
                            class="h-full w-full object-cover"
                        >
                    </div>

                    <div class="border-t border-slate-100 p-2">
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                :disabled="readOnly"
                                class="flex flex-1 items-center justify-center gap-1 rounded-lg px-2 py-1.5 text-xs font-medium transition disabled:cursor-not-allowed"
                                :class="image.is_cover
                                    ? 'bg-green-100 text-green-700 ring-1 ring-green-200'
                                    : 'bg-slate-50 text-slate-600 hover:bg-slate-100'"
                                @click="setCover(image)"
                            >
                                <span>★</span>
                                <span>{{ image.is_cover ? 'تصویر اصلی' : 'اصلی' }}</span>
                            </button>

                            <button
                                v-if="!readOnly"
                                type="button"
                                title="حذف تصویر"
                                class="rounded-lg bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100"
                                @click="deleteImage(image)"
                            >
                                حذف
                            </button>
                        </div>

                        <div
                            v-if="!readOnly"
                            class="mt-2 flex items-center justify-center gap-2"
                        >
                            <button
                                type="button"
                                :disabled="index === 0"
                                class="rounded-lg bg-slate-50 px-3 py-1 text-xs text-slate-600 transition hover:bg-slate-100 disabled:opacity-40"
                                @click="moveImage(index, -1)"
                            >
                                ▲
                            </button>
                            <button
                                type="button"
                                :disabled="index === images.length - 1"
                                class="rounded-lg bg-slate-50 px-3 py-1 text-xs text-slate-600 transition hover:bg-slate-100 disabled:opacity-40"
                                @click="moveImage(index, 1)"
                            >
                                ▼
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-10 text-center"
            >
                <div class="text-3xl text-slate-300">🖼</div>
                <p class="mt-3 text-sm font-medium text-slate-500">
                    هنوز تصویری برای این محصول ثبت نشده است.
                </p>
                <p v-if="!readOnly" class="mt-1 text-xs text-slate-400">
                    ابتدا تصاویر را از بخش بالا آپلود کنید.
                </p>
            </div>
        </div>
    </div>
</template>