<script setup>
import { ref, computed, onMounted } from 'vue'

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

const TITLE_MAX = 60
const DESCRIPTION_MAX = 160

const inputClass =
    'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100 disabled:cursor-not-allowed disabled:bg-slate-100'

const loading = ref(false)
const saving = ref(false)
const error = ref(null)

const form = ref({
    slug: '',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
})

const titleLength = computed(() => form.value.meta_title.length)
const descriptionLength = computed(() => form.value.meta_description.length)

const canSave = computed(() =>
    !props.readOnly && !saving.value && !loading.value
)

async function loadSeo()
{
    loading.value = true
    error.value = null

    try
    {
        const response = await axios.get(
            Routes.product.seo.show(props.productId)
        )

        const seo = response.data?.data ?? response.data ?? {}

        form.value = {
            slug: seo.slug ?? '',
            meta_title: seo.meta_title ?? '',
            meta_description: seo.meta_description ?? '',
            meta_keywords: seo.meta_keywords ?? '',
        }
    }
    catch (err)
    {
        console.error(err)
        error.value =
            err?.response?.data?.message ??
            'خطا در دریافت اطلاعات سئو.'
    }
    finally
    {
        loading.value = false
    }
}

async function saveSeo()
{
    if (props.readOnly)
    {
        return
    }

    saving.value = true
    error.value = null

    try
    {
        await axios.put(
            Routes.product.seo.update(props.productId),
            {
                slug: form.value.slug.trim(),
                meta_title: form.value.meta_title.trim(),
                meta_description: form.value.meta_description.trim(),
                meta_keywords: form.value.meta_keywords.trim(),
            }
        )

        $toast.success('اطلاعات سئو با موفقیت ذخیره شد.')
        emit('saved')
    }
    catch (err)
    {
        console.error(err)

        const errors = err?.response?.data?.errors
        const firstError = errors
            ? Object.values(errors)[0]?.[0]
            : null

        error.value =
            firstError ??
            err?.response?.data?.message ??
            'ذخیره اطلاعات سئو انجام نشد.'
    }
    finally
    {
        saving.value = false
    }
}

onMounted(loadSeo)
</script>

<template>
    <div class="mx-auto max-w-4xl space-y-5" dir="rtl">
        <!-- Loading -->
        <div
            v-if="loading"
            class="flex min-h-[300px] items-center justify-center"
        >
            <i class="fa-solid fa-spinner fa-spin ml-2 text-xl text-teal-600"></i>
            <span class="text-sm text-slate-500">در حال بارگذاری...</span>
        </div>

        <template v-else>
            <!-- Error -->
            <div
                v-if="error"
                class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
            >
                {{ error }}
            </div>

            <!-- Fields -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-1 font-bold text-slate-800">تنظیمات سئو</h3>
                <p class="mb-5 text-xs text-slate-400">
                    این مقادیر در نتایج موتورهای جستجو نمایش داده می‌شوند.
                </p>

                <div class="space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            نشانی صفحه (Slug)
                        </label>
                        <input
                            v-model="form.slug"
                            type="text"
                            dir="ltr"
                            placeholder="product-name"
                            :disabled="readOnly"
                            :class="inputClass"
                        />
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label class="text-sm font-medium text-slate-700">
                                عنوان سئو (Meta Title)
                            </label>
                            <span
                                class="text-xs"
                                :class="titleLength > TITLE_MAX ? 'text-rose-600' : 'text-slate-400'"
                            >
                                {{ titleLength }} / {{ TITLE_MAX }}
                            </span>
                        </div>
                        <input
                            v-model="form.meta_title"
                            type="text"
                            placeholder="عنوانی که در گوگل نمایش داده می‌شود"
                            :disabled="readOnly"
                            :class="inputClass"
                        />
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label class="text-sm font-medium text-slate-700">
                                توضیحات سئو (Meta Description)
                            </label>
                            <span
                                class="text-xs"
                                :class="descriptionLength > DESCRIPTION_MAX ? 'text-rose-600' : 'text-slate-400'"
                            >
                                {{ descriptionLength }} / {{ DESCRIPTION_MAX }}
                            </span>
                        </div>
                        <textarea
                            v-model="form.meta_description"
                            rows="3"
                            placeholder="توضیح کوتاه برای نمایش در نتایج جستجو"
                            :disabled="readOnly"
                            :class="inputClass"
                        ></textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            کلمات کلیدی
                        </label>
                        <input
                            v-model="form.meta_keywords"
                            type="text"
                            placeholder="کلمات را با کاما جدا کنید"
                            :disabled="readOnly"
                            :class="inputClass"
                        />
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 class="mb-3 text-sm font-bold text-slate-700">پیش‌نمایش در نتایج جستجو</h4>
                <div class="rounded-xl bg-slate-50 p-4">
                    <p dir="ltr" class="truncate text-xs text-emerald-700">
                        yoursite.com/products/{{ form.slug || 'product-slug' }}
                    </p>
                    <p class="mt-1 truncate text-base font-medium text-blue-700">
                        {{ form.meta_title || 'عنوان محصول' }}
                    </p>
                    <p class="mt-1 line-clamp-2 text-sm text-slate-600">
                        {{ form.meta_description || 'توضیحات سئو اینجا نمایش داده می‌شود.' }}
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 z-40 rounded-2xl border border-slate-200 bg-white p-4 shadow-lg">
                <div class="flex justify-end">
                    <button
                        v-if="!readOnly"
                        type="button"
                        :disabled="!canSave"
                        class="inline-flex h-11 min-w-40 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="saveSeo"
                    >
                        <i :class="saving ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-floppy-disk'"></i>
                        <span>{{ saving ? 'در حال ذخیره...' : 'ذخیره سئو' }}</span>
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>