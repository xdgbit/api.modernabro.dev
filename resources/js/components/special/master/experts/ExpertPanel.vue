<script setup>

import {
    ref,
    onMounted,
} from 'vue'

import axios from 'axios'

import {
    UserRoundCog,
    Phone,
    Mail,
    Lock,
    CheckCircle2,
    ShieldCheck,
} from 'lucide-vue-next'


/* ======================================================
   Props
====================================================== */

const props = defineProps({

    expertId: {
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


/* ======================================================
   State
====================================================== */

const loading = ref(false)

const saving = ref(false)

const rolesLoading = ref(false)

const errors = ref({})

const roles = ref([])


const form = ref({

    first_name: '',

    last_name: '',

    mobile_number: '',
    username: '',
    national_code: '',

    email: '',

    password: '',

    role_id: '',

    is_active: true,

})


/* ======================================================
   Edit Mode
====================================================== */

const isEdit = ref(
    !!props.expertId
)


/* ======================================================
   Load Roles
====================================================== */

async function loadRoles()
{
    rolesLoading.value = true

    try
    {
        const response = await axios.get(Routes.admin.expert.roles)
        if (response.data.success)
        {
            roles.value = response.data.data.roles ?? []
        }

    } catch (error)
    {
        console.error(
            'خطا در دریافت نقش‌ها:',
            error
        )
    } finally
    {
        rolesLoading.value = false
    }
}


/* ======================================================
   Load Expert
====================================================== */

async function loadExpert()
{
    if (!props.expertId)
    {
        return
    }


    loading.value = true


    try
    {
        const response = await axios.get(
            Routes.expert.show(
                props.expertId
            )
        )


        if (response.data.success)
        {
            const expert =
                response.data.data.expert


            form.value = {

                first_name:
                    expert.first_name ??
                    '',

                last_name:
                    expert.last_name ??
                    '',
                username: expert.username ?? '',
                national_code:
                    expert.national_code ??
                    '',

                mobile_number:
                    expert.mobile_number ??
                    '',

                email:
                    expert.email ??
                    '',

                password:
                    '',

                role_id:
                    expert.role_id ??
                    '',

                is_active:
                    Boolean(
                        Number(
                            expert.is_active
                        )
                    ),

            }
        }

    } catch (error)
    {
        console.error(
            'خطا در دریافت اطلاعات کارشناس:',
            error
        )
    } finally
    {
        loading.value = false
    }
}


/* ======================================================
   Save
====================================================== */

async function save()
{
    errors.value = {}

    saving.value = true


    try
    {
        const url = props.expertId
            ? Routes.expert.update(
                props.expertId
            )
            : Routes.expert.store


        const payload = {
            first_name: form.value.first_name,
            last_name: form.value.last_name,
            mobile_number: form.value.mobile_number,
            national_code: form.value.national_code,
            email: form.value.email,
            password: form.value.password,
            role_id: form.value.role_id,
            is_active: form.value.is_active,
            username: form.value.username,
        }
        const response = props.expertId
            ? await axios.patch(
                url,
                payload
            )

            : await axios.post(
                url,
                payload
            )


        if (response.data.success)
        {
            if (
                typeof props.onSaved ===
                'function'
            )
            {
                props.onSaved(
                    response.data.data
                )
            }


            closePanel()
        }

    } catch (error)
    {
        if (
            error.response?.data?.errors
        )
        {
            errors.value =
                error.response.data.errors
        }
        else
        {
            console.error(
                'خطا در ذخیره کارشناس:',
                error
            )
        }
    } finally
    {
        saving.value = false
    }

}


/* ======================================================
   Close Panel
====================================================== */

function closePanel()
{
    const panel =
        window[
            props.jsPanelVariableName
            ]


    if (panel)
    {
        panel.close()
    }
}


/* ======================================================
   Field Error
====================================================== */

function fieldError(field)
{
    return (
        errors.value?.[field]?.[0] ??
        ''
    )
}


/* ======================================================
   Mounted
====================================================== */

onMounted(async () =>
{
    await Promise.all([
        loadRoles(),
        loadExpert(),
    ])
})

</script>


<template>

    <div
        class="min-h-full bg-slate-50 p-5"
        dir="rtl"
    >

        <!-- Loading -->

        <div
            v-if="loading"
            class="flex min-h-[300px] items-center justify-center"
        >

            <i
                class="fa fa-spinner fa-spin text-xl text-teal-600"
            ></i>

        </div>


        <!-- Form -->

        <form
            v-else
            class="space-y-5"
            autocomplete="off"
            @submit.prevent="save"
        >



            <!-- ردیف اول: نام و نام خانوادگی -->
            <div class="grid gap-4 md:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        نام
                    </label>

                    <input
                        v-model="form.first_name"
                        type="text"
                        name="expert_first_name"
                        autocomplete="off"
                        placeholder="نام کارشناس"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                    >

                    <p v-if="fieldError('first_name')" class="mt-1 text-xs text-red-500">
                        {{ fieldError('first_name') }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        نام خانوادگی
                    </label>

                    <input
                        v-model="form.last_name"
                        type="text"
                        name="expert_last_name"
                        autocomplete="off"
                        placeholder="نام خانوادگی کارشناس"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                    >

                    <p v-if="fieldError('last_name')" class="mt-1 text-xs text-red-500">
                        {{ fieldError('last_name') }}
                    </p>
                </div>

            </div>


            <!-- ردیف دوم: نام کاربری و ایمیل -->
            <div class="mt-4 grid gap-4 md:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        نام کاربری
                    </label>

                    <input
                        v-model="form.username"
                        type="text"
                        name="expert_username"
                        autocomplete="off"
                        dir="ltr"
                        placeholder="نام کاربری"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                    >

                    <p v-if="fieldError('username')" class="mt-1 text-xs text-red-500">
                        {{ fieldError('username') }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        ایمیل
                    </label>

                    <div class="relative">
                        <Mail
                            :size="17"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="form.email"
                            type="email"
                            name="expert_email"
                            autocomplete="off"
                            dir="ltr"
                            placeholder="example@email.com"
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                        >
                    </div>

                    <p v-if="fieldError('email')" class="mt-1 text-xs text-red-500">
                        {{ fieldError('email') }}
                    </p>
                </div>

            </div>


            <!-- ردیف سوم: موبایل و کد ملی -->
            <div class="mt-4 grid gap-4 md:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        شماره موبایل
                    </label>

                    <div class="relative">
                        <Phone
                            :size="17"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="form.mobile_number"
                            type="tel"
                            name="expert_mobile"
                            autocomplete="off"
                            inputmode="tel"
                            dir="ltr"
                            placeholder="09xxxxxxxxx"
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                        >
                    </div>

                    <p v-if="fieldError('mobile_number')" class="mt-1 text-xs text-red-500">
                        {{ fieldError('mobile_number') }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        کد ملی
                    </label>

                    <input
                        v-model="form.national_code"
                        type="text"
                        name="expert_national_code"
                        autocomplete="off"
                        inputmode="numeric"
                        maxlength="10"
                        dir="ltr"
                        placeholder="کد ملی ۱۰ رقمی"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                    >

                    <p v-if="fieldError('national_code')" class="mt-1 text-xs text-red-500">
                        {{ fieldError('national_code') }}
                    </p>
                </div>

            </div>

            <!-- ==================================================
                 نقش و دسترسی
            ================================================== -->

            <!-- نقش و دسترسی (به‌زودی) -->

            <div class="relative rounded-2xl border border-slate-200 bg-white p-5">
                <div class="mb-5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600"
                        >
                            <ShieldCheck :size="18"/>
                        </div>

                        <div>
                            <h3 class="font-semibold text-slate-800">
                                نقش و دسترسی
                            </h3>

                            <p class="text-xs text-slate-500">
                                تعیین سطح دسترسی کارشناس
                            </p>
                        </div>
                    </div>

                    <span
                        class="rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600"
                    >
            مدیریت نقش
        </span>
                </div>

                <label class="mb-1.5 block text-sm font-medium text-slate-700">
                    نقش کارشناس
                </label>

                <select
                    v-model="form.role_id"
                    :disabled="rolesLoading"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 disabled:cursor-wait disabled:bg-slate-100"
                >
                    <option value="" disabled>
                        {{ rolesLoading ? 'در حال دریافت نقش‌ها...' : 'انتخاب نقش کارشناس' }}
                    </option>

                    <option
                        v-for="role in roles"
                        :key="role.id"
                        :value="role.id"
                    >
                        {{ role.label }}
                    </option>
                </select>

                <p class="mt-2 text-xs text-slate-500">
                    نقش انتخاب‌شده، سطح دسترسی کارشناس را مشخص می‌کند.
                </p>
            </div>


            <!-- ==================================================
                 اطلاعات حساب
            ================================================== -->

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >

                <div
                    class="mb-5 flex items-center gap-2"
                >

                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                    >

                        <Lock :size="18"/>

                    </div>

                    <div>

                        <h3
                            class="font-semibold text-slate-800"
                        >
                            اطلاعات حساب
                        </h3>

                        <p
                            class="text-xs text-slate-400"
                        >
                            اطلاعات ورود کارشناس
                        </p>

                    </div>

                </div>


                <label
                    class="mb-1.5 block text-sm font-medium text-slate-700"
                >
                    {{
                        isEdit
                            ? 'رمز عبور جدید'
                            : 'رمز عبور'
                    }}
                </label>


                <input
                    v-model="form.password"
                    type="password"
                    name="expert_new_password"
                    autocomplete="new-password"
                    dir="ltr"
                    :placeholder="
                        isEdit
                            ? 'در صورت نیاز وارد کنید'
                            : 'رمز عبور'
                    "
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                >


                <p
                    v-if="fieldError('password')"
                    class="mt-1 text-xs text-red-500"
                >
                    {{ fieldError('password') }}
                </p>

            </div>


            <!-- ==================================================
                 وضعیت کارشناس
            ================================================== -->

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >

                <div class="space-y-3">

                    <label
                        class="flex cursor-pointer items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-4 transition hover:border-gray-300"
                    >

                        <div
                            class="flex items-center gap-3"
                        >

                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600"
                            >

                                <CheckCircle2
                                    :size="20"
                                />

                            </div>

                            <div>

                                <div
                                    class="text-sm font-medium text-gray-800"
                                >
                                    کارشناس فعال باشد
                                </div>

                                <div
                                    class="mt-1 text-xs text-gray-500"
                                >
                                    کارشناس فعال بتواند درخواست‌ها را بررسی و پیگیری کند
                                </div>

                            </div>

                        </div>


                        <div class="relative">

                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                name="expert_is_active"
                                autocomplete="off"
                                class="peer sr-only"
                            >

                            <div
                                class="h-6 w-11 rounded-full bg-gray-300 transition peer-checked:bg-[#14b8a6]"
                            ></div>

                            <div
                                class="absolute right-1 top-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:-translate-x-5"
                            ></div>

                        </div>

                    </label>

                </div>

            </div>


            <!-- ==================================================
                 دکمه‌ها
            ================================================== -->

            <div
                class="flex items-center justify-end gap-3"
            >

                <button
                    type="button"
                    class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                    @click="closePanel"
                >
                    انصراف
                </button>


                <button
                    type="submit"
                    :disabled="saving"
                    class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60"
                >

                    <i
                        v-if="saving"
                        class="fa fa-spinner fa-spin"
                    ></i>

                    <span>
                        {{
                            saving
                                ? 'در حال ذخیره...'
                                : (
                                    isEdit
                                        ? 'ذخیره تغییرات'
                                        : 'ایجاد کارشناس'
                                )
                        }}
                    </span>

                </button>

            </div>

        </form>

    </div>

</template>