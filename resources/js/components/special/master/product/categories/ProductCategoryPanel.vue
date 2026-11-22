<script setup>
import { ref, computed, onMounted } from 'vue'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    categoryId: {
        type: [Number, String],
        default: null,
    },

    readOnly: {
        type: Boolean,
        default: false,
    },

    jsPanelVariableName: {
        type: String,
        required: true,
    },

    onSaved: {
        type: Function,
        default: null,
    },
})


/*
|--------------------------------------------------------------------------
| Constants
|--------------------------------------------------------------------------
*/

const inputClass =
    'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100 disabled:cursor-not-allowed disabled:bg-slate-100'


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading = ref(false)
const saving = ref(false)
const error = ref(null)

// فقط دسته‌های اصلی به عنوان والد انتخاب می‌شوند (سطح دوم نداریم)
const parentOptions = ref([])

const form = ref({
    parent_id: '',
    title: '',
    slug: '',
    description: '',
    image: null,
    sort_order: 0,
})

const imagePreview = ref(null)
/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const isEdit = computed(() => !!props.categoryId)

// در حالت ویرایش، خود دسته نمی‌تواند والد خودش باشد
const availableParents = computed(() =>
    parentOptions.value.filter(
        (item) => String(item.id) !== String(props.categoryId ?? '')
    )
)

const canSave = computed(() =>
    !props.readOnly && !saving.value && !loading.value
)


/*
|--------------------------------------------------------------------------
| Get Initial Data
|--------------------------------------------------------------------------
*/

async function getData()
{
    loading.value = true
    error.value = null

    try
    {
        const response = await axios.post(
            Routes.product.categories.jspanel,
            {
                category_id: props.categoryId || null,
            }
        )

        const data = response.data.data

        parentOptions.value = data.parents ?? []

        if (!data.category)
        {
            return
        }

        const category = data.category

        form.value = {
            parent_id: category.parent_id ?? '',
            title: category.title ?? '',
            slug: category.slug ?? '',
            description: category.description ?? '',
            image: null,
            sort_order: category.sort_order ?? 0,
        }
        imagePreview.value = category.image_url ?? null
    }
    catch (err)
    {
        console.error('خطا در دریافت اطلاعات دسته‌بندی:', err)

        error.value =
            err?.response?.data?.message ??
            'خطا در دریافت اطلاعات دسته‌بندی.'
    }
    finally
    {
        loading.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function validate()
{
    error.value = null

    if (!form.value.title.trim())
    {
        error.value = 'نام دسته‌بندی را وارد کنید.'
        return false
    }

    if (Number(form.value.sort_order) < 0)
    {
        error.value = 'ترتیب نمایش نمی‌تواند منفی باشد.'
        return false
    }

    return true
}


/*
|--------------------------------------------------------------------------
| Save Category
|--------------------------------------------------------------------------
*/

async function saveCategory()
{
    if (props.readOnly || !validate())
    {
        return
    }

    saving.value = true
    error.value = null

    try
    {
        const formData = new FormData()

        formData.append('title', form.value.title.trim())
        formData.append('slug', form.value.slug.trim())
        formData.append('description', form.value.description ?? '')
        formData.append('parent_id', form.value.parent_id ?? '')
        formData.append('sort_order', String(Number(form.value.sort_order)))

        if (form.value.image instanceof File)
        {
            formData.append('image', form.value.image)
        }

        let response

        if (isEdit.value)
        {
            formData.append('_method', 'PATCH')

            response = await axios.post(
                Routes.product.categories.update(props.categoryId),
                formData
            )
        }
        else
        {
            response = await axios.post(
                Routes.product.categories.store,
                formData
            )
        }

        if (props.onSaved)
        {
            props.onSaved(response.data)
        }

        window[props.jsPanelVariableName]?.close()
    }
    catch (err)
    {
        console.error('خطا در ذخیره دسته‌بندی:', err)

        error.value =
            err?.response?.data?.message ??
            'ذخیره دسته‌بندی انجام نشد.'
    }
    finally
    {
        saving.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

function closePanel()
{
    window[props.jsPanelVariableName]?.close()
}


/*
|--------------------------------------------------------------------------
| Mount
|--------------------------------------------------------------------------
*/

onMounted(() =>
{
    getData()
})
</script>


<template>

    <div
        class="min-h-full bg-slate-50 p-5"
        dir="rtl"
    >

        <div class="mx-auto max-w-3xl">

            <!-- Loading -->

            <div
                v-if="loading"
                class="flex min-h-[300px] items-center justify-center"
            >
                <i class="fa-solid fa-spinner fa-spin ml-2 text-xl text-teal-600"></i>
                <span class="text-sm text-slate-500">
                    در حال بارگذاری...
                </span>
            </div>


            <div
                v-else
                class="space-y-5"
            >

                <!-- Header -->

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h2 class="text-xl font-bold text-slate-800">
                        {{
                            readOnly
                                ? 'مشاهده دسته‌بندی'
                                : isEdit
                                    ? 'ویرایش دسته‌بندی'
                                    : 'دسته‌بندی جدید'
                        }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        نام، نامک، دسته والد و ترتیب نمایش دسته‌بندی
                    </p>

                </div>


                <!-- Error -->

                <div
                    v-if="error"
                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
                >
                    {{ error }}
                </div>


                <!-- Basic Info -->

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h3 class="mb-5 font-bold text-slate-800">
                        اطلاعات پایه
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                نام دسته‌بندی
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="نام دسته‌بندی را وارد کنید"
                                :disabled="readOnly"
                                :class="inputClass"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                نامک (Slug)
                            </label>
                            <input
                                v-model="form.slug"
                                type="text"
                                dir="ltr"
                                placeholder="خالی بگذارید تا خودکار ساخته شود"
                                :disabled="readOnly"
                                :class="inputClass"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                دسته والد
                            </label>
                            <select
                                v-model="form.parent_id"
                                :disabled="readOnly"
                                :class="inputClass"
                            >
                                <option value="">دسته اصلی (بدون والد)</option>
                                <option
                                    v-for="parent in availableParents"
                                    :key="parent.id"
                                    :value="parent.id"
                                >
                                    {{ parent.title }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                ترتیب نمایش
                            </label>
                            <input
                                v-model="form.sort_order"
                                type="number"
                                min="0"
                                dir="ltr"
                                :disabled="readOnly"
                                :class="inputClass"
                            />
                        </div>

                    </div>

                </div>


                <!-- Details -->

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h3 class="mb-5 font-bold text-slate-800">
                        جزئیات
                    </h3>

                    <div class="space-y-4">

                        <div>
                            <template v-if="!readOnly">
                                <AppUploadFile
                                    v-model="form.image"
                                    :initial-preview="imagePreview"
                                    label="تصویر دسته‌بندی"
                                    accept="image/jpeg,image/png,image/webp"
                                    :max-size="2 * 1024 * 1024"
                                    @remove-initial-preview="imagePreview = null"
                                />
                            </template>

                            <div v-else-if="imagePreview" class="mt-2">
                                <img
                                    :src="imagePreview"
                                    alt="تصویر دسته‌بندی"
                                    class="h-40 w-40 rounded-xl border border-slate-200 object-cover"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                توضیحات
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                placeholder="توضیحات کوتاه درباره دسته‌بندی..."
                                :disabled="readOnly"
                                :class="inputClass"
                            ></textarea>
                        </div>

                    </div>

                </div>


                <!-- Footer -->

                <div class="sticky bottom-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-lg">

                    <div class="flex gap-3">

                        <template v-if="readOnly">
                            <button
                                type="button"
                                @click="closePanel"
                                class="w-full rounded-xl bg-slate-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                            >
                                بستن
                            </button>
                        </template>

                        <template v-else>
                            <button
                                type="button"
                                @click="closePanel"
                                :disabled="saving"
                                class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:opacity-50"
                            >
                                انصراف
                            </button>

                            <button
                                type="button"
                                @click="saveCategory"
                                :disabled="!canSave"
                                class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <i
                                    v-if="saving"
                                    class="fa-solid fa-spinner fa-spin ml-2"
                                ></i>

                                {{
                                    saving
                                        ? 'در حال ذخیره...'
                                        : isEdit
                                            ? 'ذخیره تغییرات'
                                            : 'ثبت دسته‌بندی'
                                }}
                            </button>
                        </template>

                    </div>

                </div>

            </div>

        </div>

    </div>

</template>