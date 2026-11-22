<template>

    <div
        ref="formContainer"
        dir="rtl"
        class="w-full p-4"
    >

        <!-- Loading -->

        <div
            v-if="loading"
            class="flex min-h-[250px] items-center justify-center"
        >

            <i
                class="fa-solid fa-spinner fa-spin text-2xl text-[#e94b5f]"
            ></i>

        </div>


        <!-- Form -->

        <form
            v-else
            @submit.prevent="save"
            class="space-y-5"
        >

            <!-- Title -->

            <AppInput
                v-model="form.title"
                field="title"
                label="عنوان دسته‌بندی"
                placeholder="مثلاً اخبار"
                :required="true"
                :error="getError('title')"
                :validated="validationStarted"
                @update:model-value="
                    onFieldInput(
                        'title',
                        $event,
                        'عنوان دسته‌بندی را وارد کنید.'
                    )
                "
            />


            <!-- Slug -->

            <AppInput
                v-model="form.slug"
                field="slug"
                label="Slug"
                placeholder="news"
                :required="true"
                :error="getError('slug')"
                :validated="validationStarted"
                @update:model-value="
                    onFieldInput(
                        'slug',
                        $event,
                        'Slug دسته‌بندی را وارد کنید.'
                    )
                "
            />


            <!-- Description -->

            <div class="space-y-2">

                <label
                    class="block text-sm font-medium text-gray-700"
                >
                    توضیحات
                </label>

                <textarea
                    v-model="form.description"
                    rows="5"
                    placeholder="توضیحات دسته‌بندی را وارد کنید..."
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm leading-7 text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-[#14b8a6] focus:ring-1 focus:ring-[#14b8a6]"
                    :class="{
                        'border-red-400':
                            validationStarted &&
                            getError('description')
                    }"
                    @input="
                        onFieldInput(
                            'description',
                            form.description,
                            'توضیحات دسته‌بندی را وارد کنید.'
                        )
                    "
                ></textarea>

                <div
                    v-if="
                        validationStarted &&
                        getError('description')
                    "
                    class="text-xs text-red-500"
                >
                    {{ getError('description') }}
                </div>

            </div>


            <!-- Parent Category -->

            <!--
            <AppSelect
                v-model="form.parent_id"
                field="parent_id"
                label="دسته‌بندی والد"
                placeholder="بدون دسته‌بندی والد"
                :options="parentCategories"
                :error="getError('parent_id')"
                :validated="validationStarted"
            />
            -->


            <!-- Active -->

            <AppToggle
                v-model="form.is_active"
                label="دسته‌بندی فعال باشد"
                icon="circle-check"
                description="دسته‌بندی در سایت قابل مشاهده باشد"
            />


            <!-- General Error -->

            <div
                v-if="error"
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
            >
                {{ error }}
            </div>


            <!-- Submit -->

            <button
                type="submit"
                :disabled="saving"
                class="flex w-full items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition disabled:cursor-not-allowed"
                :class="
                    saving
                        ? 'bg-gray-400'
                        : 'bg-[#14b8a6] hover:bg-[#0d9488]'
                "
            >

                <i
                    v-if="saving"
                    class="fa-solid fa-spinner fa-spin"
                ></i>

                <span>
                    {{
                        saving
                            ? 'در حال ذخیره...'
                            : isEdit
                                ? 'ویرایش دسته‌بندی'
                                : 'ثبت دسته‌بندی'
                    }}
                </span>

            </button>

        </form>

    </div>

</template>


<script setup>

import {
    ref,
    computed,
    onMounted,
    nextTick,
} from 'vue'

import axios from 'axios'

import {toast as $toast} from 'vue-sonner'

import {
    useFormValidation,
} from '@/composables/useFormValidation'


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
| State
|--------------------------------------------------------------------------
*/

const loading = ref(false)

const saving = ref(false)

const error = ref(null)

const formContainer = ref(null)


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = ref({

    title: '',

    slug: '',

    description: '',

    image: null,

    is_active: true,

})


/*
|--------------------------------------------------------------------------
| Existing Image
|--------------------------------------------------------------------------
*/

const imagePreview = ref(null)


/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

const isEdit = computed(() =>
{

    return Boolean(
        props.categoryId
    )

})


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

const {
    validationStarted,

    getError,

    validateRequired,

    onFieldInput,

    startValidation,

    setApiErrors,

    clearAll,

} = useFormValidation()


/*
|--------------------------------------------------------------------------
| Scroll To Top
|--------------------------------------------------------------------------
*/

const scrollToTop = async () =>
{

    await nextTick()


    const panelContent =
        document.querySelector(
            '.jsPanel-content'
        )


    if (panelContent)
    {

        panelContent.scrollTo({

            top: 0,

            behavior: 'smooth',

        })

        return

    }


    window.scrollTo({

        top: 0,

        behavior: 'smooth',

    })

}


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

const validate = async () =>
{

    startValidation()

    clearAll()

    let valid = true


    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */

    if (
        !validateRequired(
            'title',
            form.value.title,
            'عنوان دسته‌بندی را وارد کنید.'
        )
    )
    {
        valid = false
    }


    /*
    |--------------------------------------------------------------------------
    | Slug
    |--------------------------------------------------------------------------
    */

    if (
        !validateRequired(
            'slug',
            form.value.slug,
            'Slug دسته‌بندی را وارد کنید.'
        )
    )
    {
        valid = false
    }


    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    if (
        !validateRequired(
            'description',
            form.value.description,
            'توضیحات دسته‌بندی را وارد کنید.'
        )
    )
    {
        valid = false
    }


    /*
    |--------------------------------------------------------------------------
    | Result
    |--------------------------------------------------------------------------
    */

    if (!valid)
    {

        $toast.error(
            'لطفاً خطاهای فرم را برطرف کنید.'
        )

        await scrollToTop()

        return false

    }


    return true

}


/*
|--------------------------------------------------------------------------
| Get Data
|--------------------------------------------------------------------------
*/

const getData = async () =>
{

    /*
    |--------------------------------------------------------------------------
    | Create Mode
    |--------------------------------------------------------------------------
    */

    if (!props.categoryId)
    {

        loading.value = false

        return

    }


    loading.value = true

    error.value = null


    try
    {

        const response =
            await axios.get(
                Routes.admin.article_category.show(
                    props.categoryId
                )
            )


        const category = response.data?.data?.category ?? response.data?.data


        if (category)
        {

            form.value = {

                title:
                    category.title ??
                    '',

                slug:
                    category.slug ??
                    '',

                description:
                    category.description ??
                    '',

                image: null,

                is_active:
                    !!category.is_active,

            }


            imagePreview.value =
                category.image ??
                category.main_image ??
                null

        }

    } catch (err)
    {

        error.value =
            err.response?.data?.message ??
            'خطا در دریافت اطلاعات دسته‌بندی.'


        $toast.error(
            error.value
        )

    } finally
    {

        loading.value = false

    }

}


/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

const save = async () =>
{

    const valid =
        await validate()


    if (!valid)
    {
        return
    }


    saving.value = true

    error.value = null


    try
    {

        const formData =
            new FormData()


        /*
        |--------------------------------------------------------------------------
        | Basic Fields
        |--------------------------------------------------------------------------
        */

        formData.append(
            'title',
            form.value.title.trim()
        )


        formData.append(
            'slug',
            form.value.slug.trim()
        )


        formData.append(
            'description',
            form.value.description.trim()
        )


        /*
        |--------------------------------------------------------------------------
        | Active
        |--------------------------------------------------------------------------
        */

        formData.append(
            'is_active',
            form.value.is_active
                ? '1'
                : '0'
        )


        /*
        |--------------------------------------------------------------------------
        | Image
        |--------------------------------------------------------------------------
        */

        if (
            form.value.image
            instanceof File
        )
        {

            formData.append(
                'image',
                form.value.image
            )

        }


        /*
        |--------------------------------------------------------------------------
        | Request
        |--------------------------------------------------------------------------
        */

        let response


        if (props.categoryId)
        {

            formData.append(
                '_method',
                'PATCH'
            )


            response =
                await axios.post(
                    Routes.admin.article_category.update(
                        props.categoryId
                    ),
                    formData
                )

        }
        else
        {

            response =
                await axios.post(
                    Routes.admin.article_category.store,
                    formData
                )

        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        $toast.success(
            response.data?.message ??
            'دسته‌بندی با موفقیت ذخیره شد.'
        )


        if (props.onSaved)
        {

            props.onSaved(
                response.data
            )

        }


        /*
        |--------------------------------------------------------------------------
        | Close Panel
        |--------------------------------------------------------------------------
        */

        window[
            props.jsPanelVariableName
            ]?.close()

    } catch (err)
    {

        /*
        |--------------------------------------------------------------------------
        | Validation Error
        |--------------------------------------------------------------------------
        */

        if (
            err.response?.status === 422
        )
        {

            setApiErrors(
                err.response.data?.errors ??
                {}
            )


            startValidation()


            $toast.error(
                err.response.data?.message ??
                'اطلاعات وارد شده صحیح نیست.'
            )


            await scrollToTop()

            return

        }


        /*
        |--------------------------------------------------------------------------
        | General Error
        |--------------------------------------------------------------------------
        */

        error.value =
            err.response?.data?.message ??
            'خطا در ذخیره دسته‌بندی.'


        $toast.error(
            error.value
        )

    } finally
    {

        saving.value = false

    }

}


/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(() =>
{

    getData()

})

</script>