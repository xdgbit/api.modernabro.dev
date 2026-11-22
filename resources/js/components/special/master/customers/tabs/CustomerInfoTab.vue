<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import {
    User,
    Phone,
    Mail,
    Lock,
    CheckCircle2,
} from 'lucide-vue-next'

/*
|--------------------------------------------------------------------------
| Props & Emits
|--------------------------------------------------------------------------
*/

const props = defineProps({
    customerId: {
        type: [Number, String],
        default: null,
    },

    readOnly: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['loaded', 'saved', 'close'])


/*
|--------------------------------------------------------------------------
| Constants
|--------------------------------------------------------------------------
*/

const inputClass =
    'w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100 disabled:cursor-not-allowed disabled:bg-slate-100'


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const errors = ref({})

const form = ref({
    first_name: '',
    last_name: '',
    mobile_number: '',
    email: '',
    password: '',
    is_active: true,
})

const isEdit = computed(() => !!props.customerId)

const canSave = computed(() =>
    !props.readOnly && !saving.value && !loading.value
)

function fieldError(field)
{
    return errors.value?.[field]?.[0] ?? ''
}


/*
|--------------------------------------------------------------------------
| Load
|--------------------------------------------------------------------------
*/

async function loadCustomer()
{
    if (!props.customerId)
    {
        return
    }

    loading.value = true
    error.value = null

    try
    {
        const response = await axios.get(
            Routes.customer.show(props.customerId)
        )

        const customer =
            response.data.data?.customer ?? response.data.data ?? {}

        form.value = {
            first_name: customer.first_name ?? '',
            last_name: customer.last_name ?? '',
            mobile_number: customer.mobile_number ?? '',
            email: customer.email ?? '',
            password: '',
            is_active: Boolean(Number(customer.is_active ?? 1)),
        }

        emit('loaded', customer)
    }
    catch (err)
    {
        console.error('خطا در دریافت اطلاعات مشتری:', err)

        error.value =
            err?.response?.data?.message ??
            'خطا در دریافت اطلاعات مشتری.'
    }
    finally
    {
        loading.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

function buildPayload()
{
    const payload = {
        first_name: form.value.first_name.trim(),
        last_name: form.value.last_name.trim(),
        mobile_number: form.value.mobile_number.trim(),
        email: form.value.email.trim() || null,
        is_active: !!form.value.is_active,
    }

    // رمز فقط وقتی ارسال می‌شود که کاربر آن را وارد کرده باشد
    if (form.value.password)
    {
        payload.password = form.value.password
    }

    return payload
}

async function save()
{
    if (!canSave.value)
    {
        return
    }

    errors.value = {}
    error.value = null
    saving.value = true

    try
    {
        const payload = buildPayload()

        const response = isEdit.value
            ? await axios.patch(
                Routes.customer.update(props.customerId),
                payload
            )
            : await axios.post(
                Routes.customer.store,
                payload
            )

        const customer =
            response.data.data?.customer ?? response.data.data ?? {}

        form.value.password = ''

        window.$toast?.success('اطلاعات مشتری با موفقیت ذخیره شد.')

        emit('saved', customer)
    }
    catch (err)
    {
        console.error('خطا در ذخیره مشتری:', err)

        if (err?.response?.status === 422)
        {
            errors.value = err.response.data.errors ?? {}
        }
        else
        {
            error.value =
                err?.response?.data?.message ??
                'ذخیره مشتری انجام نشد.'
        }
    }
    finally
    {
        saving.value = false
    }
}

onMounted(loadCustomer)
</script>


<template>

    <div
        class="mx-auto max-w-4xl space-y-5"
        dir="rtl"
    >

        <!-- Loading -->

        <div
            v-if="loading"
            class="flex min-h-[300px] items-center justify-center"
        >
            <i class="fa-solid fa-spinner fa-spin text-xl text-teal-600"></i>
        </div>


        <template v-else>

            <!-- Error -->

            <div
                v-if="error"
                class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
            >
                {{ error }}
            </div>


            <!-- Personal -->

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="mb-5 flex items-center gap-2">

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                        <User :size="18"/>
                    </div>

                    <div>
                        <h3 class="font-semibold text-slate-800">اطلاعات مشتری</h3>
                        <p class="text-xs text-slate-400">اطلاعات پایه مشتری را وارد کنید</p>
                    </div>

                </div>

                <div class="grid gap-4 md:grid-cols-2">

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">نام</label>
                        <input
                            v-model="form.first_name"
                            type="text"
                            autocomplete="off"
                            placeholder="نام مشتری"
                            :disabled="readOnly"
                            :class="inputClass"
                        >
                        <p v-if="fieldError('first_name')" class="mt-1 text-xs text-red-500">
                            {{ fieldError('first_name') }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">نام خانوادگی</label>
                        <input
                            v-model="form.last_name"
                            type="text"
                            autocomplete="off"
                            placeholder="نام خانوادگی مشتری"
                            :disabled="readOnly"
                            :class="inputClass"
                        >
                        <p v-if="fieldError('last_name')" class="mt-1 text-xs text-red-500">
                            {{ fieldError('last_name') }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">شماره موبایل</label>
                        <div class="relative">
                            <Phone :size="17" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                            <input
                                v-model="form.mobile_number"
                                type="tel"
                                inputmode="tel"
                                dir="ltr"
                                autocomplete="off"
                                placeholder="09xxxxxxxxx"
                                :disabled="readOnly"
                                :class="[inputClass, 'pr-10']"
                            >
                        </div>
                        <p v-if="fieldError('mobile_number')" class="mt-1 text-xs text-red-500">
                            {{ fieldError('mobile_number') }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">ایمیل</label>
                        <div class="relative">
                            <Mail :size="17" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                            <input
                                v-model="form.email"
                                type="email"
                                dir="ltr"
                                autocomplete="off"
                                placeholder="example@email.com"
                                :disabled="readOnly"
                                :class="[inputClass, 'pr-10']"
                            >
                        </div>
                        <p v-if="fieldError('email')" class="mt-1 text-xs text-red-500">
                            {{ fieldError('email') }}
                        </p>
                    </div>

                </div>

            </div>


            <!-- Account -->

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="mb-5 flex items-center gap-2">

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <Lock :size="18"/>
                    </div>

                    <div>
                        <h3 class="font-semibold text-slate-800">اطلاعات حساب</h3>
                        <p class="text-xs text-slate-400">اطلاعات ورود مشتری</p>
                    </div>

                </div>

                <label class="mb-1.5 block text-sm font-medium text-slate-700">
                    {{ isEdit ? 'رمز عبور جدید' : 'رمز عبور' }}
                </label>

                <input
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    dir="ltr"
                    :placeholder="isEdit ? 'در صورت نیاز وارد کنید' : 'رمز عبور'"
                    :disabled="readOnly"
                    :class="inputClass"
                >

                <p v-if="fieldError('password')" class="mt-1 text-xs text-red-500">
                    {{ fieldError('password') }}
                </p>

            </div>


            <!-- Status -->

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <label
                    class="flex cursor-pointer items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-4 transition hover:border-gray-300"
                    :class="readOnly ? 'pointer-events-none opacity-70' : ''"
                >

                    <div class="flex items-center gap-3">

                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                            <CheckCircle2 :size="20"/>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-gray-800">مشتری فعال باشد</div>
                            <div class="mt-1 text-xs text-gray-500">
                                مشتری فعال بتواند از خدمات سایت استفاده کند
                            </div>
                        </div>

                    </div>

                    <div class="relative">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="peer sr-only"
                        >
                        <div class="h-6 w-11 rounded-full bg-gray-300 transition peer-checked:bg-[#14b8a6]"></div>
                        <div class="absolute right-1 top-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:-translate-x-5"></div>
                    </div>

                </label>

            </div>


            <!-- Footer -->

            <div class="sticky bottom-0 z-40 rounded-2xl border border-slate-200 bg-white p-4 shadow-lg">

                <div class="flex items-center justify-end gap-3">

                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                        @click="emit('close')"
                    >
                        انصراف
                    </button>

                    <button
                        v-if="!readOnly"
                        type="button"
                        :disabled="!canSave"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60"
                        @click="save"
                    >
                        <i
                            :class="saving ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-floppy-disk'"
                        ></i>
                        <span>{{ saving ? 'در حال ذخیره...' : (isEdit ? 'ذخیره تغییرات' : 'ثبت مشتری') }}</span>
                    </button>

                </div>

            </div>

        </template>

    </div>

</template>