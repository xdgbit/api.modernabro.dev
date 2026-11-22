<script setup>
import {ref, onMounted} from 'vue'
import axios from 'axios'

import {
    Settings,
    Phone,
    Mail,
    MapPin,
    Globe,
    Save,
} from 'lucide-vue-next'

const loading = ref(false)
const saving = ref(false)

const successMessage = ref(null)
const error = ref(null)

const form = ref({
    site_name: '',
    phone: '',
    phone_secondary: '',
    email: '',
    address: '',
    footer_text: '',
})

/*
|--------------------------------------------------------------------------
| Get Settings
|--------------------------------------------------------------------------
*/

async function getSettings()
{
    loading.value = true
    error.value = null

    try
    {
        const response =
            await axios.get(
                Routes.admin.settings.get_data
            )

        const data = response.data.data ?? {}


        form.value = {
            site_name: data.site_name ?? '',
            phone: data.phone ?? '',
            phone_secondary: data.phone_secondary ?? '',
            email: data.email ?? '',
            address: data.address ?? '',
            footer_text: data.footer_text ?? '',
        }

    } catch (err)
    {
        console.error(
            'خطا در دریافت تنظیمات:',
            err
        )

        error.value =
            err?.response?.data?.message ??
            'خطا در دریافت تنظیمات.'
    } finally
    {
        loading.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Save Settings
|--------------------------------------------------------------------------
*/

async function saveSettings()
{
    saving.value = true
    error.value = null
    successMessage.value = null

    try
    {
        await axios.put(
            Routes.admin.settings.update,
            form.value
        )

        successMessage.value =
            'تنظیمات با موفقیت ذخیره شد.'

        setTimeout(() =>
        {
            successMessage.value = null
        }, 4000)
    } catch (err)
    {
        console.error(
            'خطا در ذخیره تنظیمات:',
            err
        )

        error.value =
            err?.response?.data?.message ??
            'خطا در ذخیره تنظیمات.'
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
    getSettings()
})
</script>


<template>

    <div class="space-y-6">

        <!-- Header -->

        <div>

            <h1
                class="text-2xl font-bold tracking-tight text-slate-800"
            >
                تنظیمات
            </h1>

            <p
                class="mt-1 text-sm text-slate-400"
            >
                مدیریت اطلاعات عمومی سایت و اطلاعات نمایش داده شده در فوتر
            </p>

        </div>


        <!-- Error -->

        <div
            v-if="error"
            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
        >
            {{ error }}
        </div>


        <!-- Success -->

        <div
            v-if="successMessage"
            class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
        >
            {{ successMessage }}
        </div>


        <!-- Main Card -->

        <div
            class="rounded-2xl border border-slate-200 bg-white shadow-sm"
        >

            <!-- Card Header -->

            <div
                class="border-b border-slate-100 px-5 py-4"
            >

                <div
                    class="flex items-center gap-3"
                >

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-teal-600"
                    >

                        <Settings
                            :size="21"
                        />

                    </div>

                    <div>

                        <h2
                            class="text-base font-bold text-slate-800"
                        >
                            اطلاعات سایت
                        </h2>

                        <p
                            class="mt-1 text-xs text-slate-400"
                        >
                            این اطلاعات در بخش‌های عمومی سایت مانند فوتر استفاده می‌شوند.
                        </p>

                    </div>

                </div>

            </div>


            <!-- Form -->

            <div
                class="p-5"
            >

                <div
                    class="grid gap-5 md:grid-cols-2"
                >

                    <!-- Site Name -->

                    <div>

                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            نام سایت
                        </label>

                        <div class="relative">

                            <Globe
                                :size="18"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="form.site_name"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-white py-3 pr-10 pl-3 text-sm text-slate-700 outline-none transition focus:border-teal-400 focus:ring-2 focus:ring-teal-100"
                                placeholder="نام سایت"
                            />

                        </div>

                    </div>


                    <!-- Phone -->

                    <div>

                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            شماره تماس
                        </label>

                        <div class="relative">

                            <Phone
                                :size="18"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="form.phone"
                                type="text"
                                dir="ltr"
                                class="w-full rounded-xl border border-slate-200 bg-white py-3 pr-10 pl-3 text-sm text-slate-700 outline-none transition focus:border-teal-400 focus:ring-2 focus:ring-teal-100"
                                placeholder="02112345678"
                            />

                        </div>

                    </div>


                    <!-- Secondary Phone -->

                    <div>

                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            شماره تماس دوم
                            <span class="text-xs font-normal text-slate-400">
                                (اختیاری)
                            </span>
                        </label>

                        <div class="relative">

                            <Phone
                                :size="18"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="form.phone_secondary"
                                type="text"
                                dir="ltr"
                                class="w-full rounded-xl border border-slate-200 bg-white py-3 pr-10 pl-3 text-sm text-slate-700 outline-none transition focus:border-teal-400 focus:ring-2 focus:ring-teal-100"
                                placeholder="09121234567"
                            />

                        </div>

                    </div>


                    <!-- Email -->

                    <div>

                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            ایمیل
                        </label>

                        <div class="relative">

                            <Mail
                                :size="18"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="form.email"
                                type="email"
                                dir="ltr"
                                class="w-full rounded-xl border border-slate-200 bg-white py-3 pr-10 pl-3 text-sm text-slate-700 outline-none transition focus:border-teal-400 focus:ring-2 focus:ring-teal-100"
                                placeholder="info@example.com"
                            />

                        </div>

                    </div>


                    <!-- Address -->

                    <div
                        class="md:col-span-2"
                    >

                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            آدرس
                        </label>

                        <div class="relative">

                            <MapPin
                                :size="18"
                                class="absolute right-3 top-3.5 text-slate-400"
                            />

                            <textarea
                                v-model="form.address"
                                rows="3"
                                class="w-full resize-none rounded-xl border border-slate-200 bg-white py-3 pr-10 pl-3 text-sm text-slate-700 outline-none transition focus:border-teal-400 focus:ring-2 focus:ring-teal-100"
                                placeholder="آدرس کامل مجموعه"
                            />

                        </div>

                    </div>


                    <!-- Footer Text -->

                    <div
                        class="md:col-span-2"
                    >

                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            متن فوتر
                        </label>

                        <textarea
                            v-model="form.footer_text"
                            rows="3"
                            class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 outline-none transition focus:border-teal-400 focus:ring-2 focus:ring-teal-100"
                            placeholder="متن کوتاه درباره مجموعه یا سایت"
                        />

                    </div>

                </div>


                <!-- Actions -->

                <div
                    class="mt-6 flex justify-end border-t border-slate-100 pt-5"
                >

                    <button
                        type="button"
                        :disabled="saving || loading"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="saveSettings"
                    >

                        <Save
                            :size="18"
                        />

                        <span>
                            {{ saving ? 'در حال ذخیره...' : 'ذخیره تنظیمات' }}
                        </span>

                    </button>

                </div>

            </div>

        </div>

    </div>

</template>