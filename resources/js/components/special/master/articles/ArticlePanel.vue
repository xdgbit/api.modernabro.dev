
<template>
    <div ref="formContainer" dir="rtl" class="w-full p-4">
        <div v-if="loading" class="flex min-h-[250px] items-center justify-center">
            <i class="fa-solid fa-spinner fa-spin text-2xl text-[#e94b5f]"></i>
        </div>

        <form v-else @submit.prevent="save" class="space-y-5">
            <AppInput
                v-model="form.title"
                field="title"
                label="عنوان مقاله"
                placeholder="مثلاً راهنمای سفر به تهران"
                :required="true"
                :error="getError('title')"
                :validated="validationStarted"
                @update:model-value="onFieldInput('title', $event, 'عنوان مقاله را وارد کنید.')"
            />

            <AppInput
                v-model="form.slug"
                field="slug"
                label="Slug"
                placeholder="travel-guide-tehran"
                :required="true"
                :error="getError('slug')"
                :validated="validationStarted"
                @update:model-value="onFieldInput('slug', $event, 'Slug مقاله را وارد کنید.')"
            />

            <AppSelect
                v-model="form.category_id"
                field="category_id"
                label="دسته‌بندی"
                placeholder="دسته‌بندی را انتخاب کنید"
                search-placeholder="جستجوی دسته‌بندی..."
                :options="categories"
                value-key="id"
                :option-label="category => category.title"
                :search-keys="['title']"
                :error="getError('category_id')"
                :validated="validationStarted"
            />

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">توضیحات کوتاه</label>
                <textarea
                    v-model="form.short_description"
                    rows="3"
                    placeholder="خلاصه کوتاهی از مقاله..."
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-[#14b8a6] focus:ring-1 focus:ring-[#14b8a6]"
                    :class="{ 'border-red-400': validationStarted && getError('short_description') }"
                    @input="onFieldInput('short_description', form.short_description, 'توضیحات کوتاه را وارد کنید.')"
                ></textarea>
                <div v-if="validationStarted && getError('short_description')" class="text-xs text-red-500">
                    {{ getError('short_description') }}
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">توضیحات مقاله</label>
                <textarea
                    v-model="form.description"
                    rows="8"
                    placeholder="متن کامل مقاله را وارد کنید..."
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm leading-7 text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-[#14b8a6] focus:ring-1 focus:ring-[#14b8a6]"
                    :class="{ 'border-red-400': validationStarted && getError('description') }"
                    @input="onFieldInput('description', form.description, 'توضیحات مقاله را وارد کنید.')"
                ></textarea>
                <div v-if="validationStarted && getError('description')" class="text-xs text-red-500">
                    {{ getError('description') }}
                </div>
            </div>

            <AppUploadFile
                v-model="form.image"
                :initial-preview="imagePreview"
                label="تصویر مقاله"
                accept="image/jpeg,image/png,image/webp"
                :max-size="2 * 1024 * 1024"
                :error="getError('image')"
                @remove-initial-preview="imagePreview = null"
            />

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">تاریخ انتشار</label>
                <AppDatePicker
                    v-model="form.published_at"
                    mode="datetime"
                    output="gregorian"
                    :enable-seconds="false"
                    placeholder="تاریخ و زمان انتشار را انتخاب کنید"
                />
                <div v-if="getError('published_at')" class="text-xs text-red-500">
                    {{ getError('published_at') }}
                </div>
            </div>

            <AppToggle
                v-model="form.is_active"
                label="مقاله فعال باشد"
                icon="circle-check"
                description="مقاله در سایت قابل مشاهده باشد"
            />
            <AppToggle
                v-model="form.show_on_homepage"
                label="نمایش در صفحه اصلی"
                icon="house"
                description="مقاله در صفحه اصلی سایت نمایش داده شود"
            />
            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                {{ error }}
            </div>

            <button
                type="submit"
                :disabled="saving"
                class="flex w-full items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition disabled:cursor-not-allowed"
                :class="saving ? 'bg-gray-400' : 'bg-[#14b8a6] hover:bg-[#0d9488]'"
            >
                <i v-if="saving" class="fa-solid fa-spinner fa-spin"></i>
                <span>{{ saving ? 'در حال ذخیره...' : isEdit ? 'ویرایش مقاله' : 'ثبت مقاله' }}</span>
            </button>
        </form>
    </div>
</template>

<script setup>
import {ref, computed, onMounted, nextTick} from 'vue'
import axios from 'axios'
import {toast as $toast} from 'vue-sonner'
import {useFormValidation} from '@/composables/useFormValidation'

const props = defineProps({
    articleId: {type: [Number, String], default: null},
    jsPanelVariableName: {type: String, required: true},
    onSaved: {type: Function, default: null},
})

const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const formContainer = ref(null)
const categories = ref([])
const imagePreview = ref(null)

const form = ref({
    title: '',
    slug: '',
    short_description: '',
    description: '',
    tags: '',
    category_id: null,
    image: null,
    published_at: '',
    is_active: true,
    show_on_homepage: false,
})

const isEdit = computed(() => Boolean(props.articleId))

const {
    validationStarted,
    getError,
    validateRequired,
    onFieldInput,
    startValidation,
    setApiErrors,
    clearAll,
} = useFormValidation()

const scrollToTop = async () =>
{
    await nextTick()

    const panelContent = document.querySelector('.jsPanel-content')

    if (panelContent)
    {
        panelContent.scrollTo({top: 0, behavior: 'smooth'})
        return
    }

    window.scrollTo({top: 0, behavior: 'smooth'})
}

const validate = async () =>
{
    startValidation()
    clearAll()

    let valid = true

    const fields = [
        ['title', form.value.title, 'عنوان مقاله را وارد کنید.'],
        ['slug', form.value.slug, 'Slug مقاله را وارد کنید.'],
        ['category_id', form.value.category_id, 'دسته‌بندی مقاله را انتخاب کنید.'],
        ['short_description', form.value.short_description, 'توضیحات کوتاه را وارد کنید.'],
        ['description', form.value.description, 'توضیحات مقاله را وارد کنید.'],
    ]

    fields.forEach(([field, value, message]) =>
    {
        if (!validateRequired(field, value, message))
        {
            valid = false
        }
    })

    if (!valid)
    {
        $toast.error('لطفاً خطاهای فرم را برطرف کنید.')
        await scrollToTop()
    }

    return valid
}

const getCategories = async () =>
{
    try
    {
        const response = await axios.get(Routes.admin.article_category.list)
        categories.value = response.data?.data?.categories ?? response.data?.data ?? []
    } catch (err)
    {
        $toast.error(err.response?.data?.message ?? 'خطا در دریافت دسته‌بندی‌ها.')
    }
}

const getData = async () =>
{
    if (!props.articleId)
    {
        loading.value = false
        return
    }

    loading.value = true
    error.value = null

    try
    {
        const response = await axios.get(Routes.admin.article.show(props.articleId))
        const article = response.data?.data?.article ?? response.data?.data

        if (!article)
        {
            return
        }

        form.value = {
            title: article.title ?? '',
            slug: article.slug ?? '',
            short_description: article.short_description ?? '',
            description: article.description ?? '',
            tags: Array.isArray(article.tags) ? article.tags.join(', ') : article.tags ?? '',
            category_id: article.category_id ?? article.category?.id ?? null,
            image: null,
            published_at: article.published_at ?? '',
            is_active: !!article.is_active,
            show_on_homepage: !!article.show_on_homepage,
        }

        imagePreview.value = article.main_image ?? article.image ?? null
    } catch (err)
    {
        error.value = err.response?.data?.message ?? 'خطا در دریافت اطلاعات مقاله.'
        $toast.error(error.value)
    } finally
    {
        loading.value = false
    }
}

const save = async () =>
{
    if (!await validate())
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
        formData.append('short_description', form.value.short_description.trim())
        formData.append('description', form.value.description.trim())
        formData.append('tags', form.value.tags.trim())
        formData.append('category_id', form.value.category_id ?? '')
        formData.append('is_active', form.value.is_active ? '1' : '0')
        formData.append('published_at', form.value.published_at ?? '')
        formData.append(
            'show_on_homepage',
            form.value.show_on_homepage ? '1' : '0'
        )
        if (form.value.image instanceof File)
        {
            formData.append('image', form.value.image)
        }

        let response

        if (props.articleId)
        {
            formData.append('_method', 'PATCH')
            response = await axios.post(
                Routes.admin.article.update(props.articleId),
                formData
            )
        }
        else
        {
            response = await axios.post(
                Routes.admin.article.store,
                formData
            )
        }

        $toast.success(response.data?.message ?? 'مقاله با موفقیت ذخیره شد.')

        props.onSaved?.(response.data)
        window[props.jsPanelVariableName]?.close()
    } catch (err)
    {
        if (err.response?.status === 422)
        {
            setApiErrors(err.response.data?.errors ?? {})
            startValidation()
            $toast.error(err.response.data?.message ?? 'اطلاعات وارد شده صحیح نیست.')
            await scrollToTop()
            return
        }

        error.value = err.response?.data?.message ?? 'خطا در ذخیره مقاله.'
        $toast.error(error.value)
    } finally
    {
        saving.value = false
    }
}

onMounted(() => Promise.all([getCategories(), getData()]))
</script>
