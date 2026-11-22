<script setup>
import {ref} from 'vue'
import axios from 'axios'
import {LockKeyhole, Save} from 'lucide-vue-next'

const form = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const loading = ref(false)
const errors = ref({})
const successMessage = ref('')

const submit = async () =>
{
    loading.value = true
    errors.value = {}
    successMessage.value = ''

    try
    {
        const response = await axios.post(
            Routes.admin.account.change_password,
            form.value
        )

        if (response.data.success)
        {
            successMessage.value = response.data.message || 'رمز عبور با موفقیت تغییر کرد.'

            form.value = {
                current_password: '',
                password: '',
                password_confirmation: '',
            }
        }
    } catch (error)
    {
        if (error.response?.status === 422)
        {
            errors.value = error.response.data.errors || {}
        }
        else
        {
            errors.value = {
                general: [
                    error.response?.data?.message || 'خطا در تغییر رمز عبور'
                ]
            }
        }
    } finally
    {
        loading.value = false
    }
}
</script>

<template>
    <div class="mx-auto max-w-xl space-y-5">

        <div>
            <h1 class="text-xl font-bold text-slate-800">
                تغییر رمز عبور
            </h1>

            <p class="mt-1 text-sm text-slate-400">
                برای تغییر رمز عبور، اطلاعات زیر را تکمیل کنید.
            </p>
        </div>

        <form
            @submit.prevent="submit"
            class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        >

            <div
                v-if="successMessage"
                class="rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700"
            >
                {{ successMessage }}
            </div>

            <div
                v-if="errors.general"
                class="rounded-lg bg-red-50 p-3 text-sm text-red-600"
            >
                {{ errors.general[0] }}
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    رمز عبور فعلی
                </label>

                <input
                    v-model="form.current_password"
                    type="password"
                    autocomplete="current-password"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-teal-500"
                />

                <p v-if="errors.current_password" class="mt-1 text-xs text-red-500">
                    {{ errors.current_password[0] }}
                </p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    رمز عبور جدید
                </label>

                <input
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-teal-500"
                />

                <p v-if="errors.password" class="mt-1 text-xs text-red-500">
                    {{ errors.password[0] }}
                </p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    تکرار رمز عبور جدید
                </label>

                <input
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-teal-500"
                />
            </div>

            <button
                type="submit"
                :disabled="loading"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <Save :size="17"/>

                {{ loading ? 'در حال ذخیره...' : 'تغییر رمز عبور' }}
            </button>

        </form>
    </div>
</template>