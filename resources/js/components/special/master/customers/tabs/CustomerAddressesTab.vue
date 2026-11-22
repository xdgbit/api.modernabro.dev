<script setup>
import {ref, computed, onMounted} from 'vue'
import axios from 'axios'
import {
    MapPin,
    Star,
    Plus,
    Pencil,
    Trash2,
    X,
    Check,
} from 'lucide-vue-next'


/*
|--------------------------------------------------------------------------
| Props & Emits
|--------------------------------------------------------------------------
*/

const props = defineProps({
    customerId: {
        type: [Number, String],
        required: true,
    },

    readOnly: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['changed'])


/*
|--------------------------------------------------------------------------
| Constants
|--------------------------------------------------------------------------
*/

const inputClass =
    'w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100 disabled:cursor-not-allowed disabled:bg-slate-100'

const emptyForm = () => ({
    title: '',
    receiver_name: '',
    receiver_mobile: '',
    province_id: null,
    city_id: null,
    address: '',
    postal_code: '',
    is_default: false,
})


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading = ref(false)
const saving = ref(false)
const deletingId = ref(null)
const error = ref(null)
const errors = ref({})

const addresses = ref([])

const provinces = ref([])
const cities = ref([])
const loadingProvinces = ref(false)
const loadingCities = ref(false)

// حالت فرم: null = بسته، 'new' = افزودن، شناسه = ویرایش
const editingId = ref(null)
const form = ref(emptyForm())

const isFormOpen = computed(() => editingId.value !== null)
const isNew = computed(() => editingId.value === 'new')
const canEdit = computed(() => !props.readOnly)

function fieldError(field)
{
    return errors.value?.[field]?.[0] ?? ''
}


/*
|--------------------------------------------------------------------------
| Locations
|--------------------------------------------------------------------------
*/

async function loadProvinces()
{
    loadingProvinces.value = true

    try
    {
        const response = await axios.get(Routes.locations.provinces)
        provinces.value = response.data.data?.provinces ?? []
    } catch (err)
    {
        console.error('خطا در دریافت استان‌ها:', err)
        error.value =
            err?.response?.data?.message ??
            'دریافت استان‌ها انجام نشد.'
    } finally
    {
        loadingProvinces.value = false
    }
}

async function loadCities(provinceId)
{
    cities.value = []

    if (!provinceId)
    {
        return
    }

    loadingCities.value = true

    try
    {
        const response = await axios.get(Routes.locations.cities(provinceId))
        cities.value = response.data.data?.cities ?? []
    } catch (err)
    {
        console.error('خطا در دریافت شهرها:', err)
        error.value =
            err?.response?.data?.message ??
            'دریافت شهرها انجام نشد.'
    } finally
    {
        loadingCities.value = false
    }
}

// تغییر استان، شهر قبلی پاک و شهرهای استان جدید لود می‌شود
function onProvinceChange()
{
    form.value.city_id = null
    loadCities(form.value.province_id)
}


/*
|--------------------------------------------------------------------------
| Load Addresses
|--------------------------------------------------------------------------
*/

async function loadAddresses()
{
    loading.value = true
    error.value = null

    try
    {
        const response = await axios.get(Routes.customer.address.index(props.customerId))
        addresses.value = response.data.data?.addresses ?? []
    } catch (err)
    {
        console.error('خطا در دریافت آدرس‌ها:', err)

        error.value =
            err?.response?.data?.message ??
            'خطا در دریافت آدرس‌های مشتری.'
    } finally
    {
        loading.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Form Open / Close
|--------------------------------------------------------------------------
*/

function openNew()
{
    errors.value = {}
    error.value = null
    form.value = emptyForm()
    cities.value = []

    // اولین آدرس مشتری به‌طور خودکار پیش‌فرض می‌شود
    form.value.is_default = addresses.value.length === 0

    editingId.value = 'new'
}

function openEdit(address)
{
    errors.value = {}
    error.value = null

    const provinceId = address.province_id != null
        ? Number(address.province_id)
        : null

    form.value = {
        title: address.title ?? '',
        receiver_name: address.receiver_name ?? '',
        receiver_mobile: address.receiver_mobile ?? '',
        province_id: provinceId,
        city_id: address.city_id != null ? Number(address.city_id) : null,
        address: address.address ?? '',
        postal_code: address.postal_code ?? '',
        is_default: !!address.is_default,
    }

    editingId.value = address.id

    loadCities(provinceId)
}

function closeForm()
{
    editingId.value = null
    errors.value = {}
    error.value = null
    cities.value = []
}


/*
|--------------------------------------------------------------------------
| Save (create / update)
|--------------------------------------------------------------------------
*/

function buildPayload()
{
    return {
        title: form.value.title.trim() || null,
        receiver_name: form.value.receiver_name.trim() || null,
        receiver_mobile: form.value.receiver_mobile.trim() || null,
        province_id: form.value.province_id || null,
        city_id: form.value.city_id || null,
        address: form.value.address.trim(),
        postal_code: form.value.postal_code.trim() || null,
        is_default: !!form.value.is_default,
    }
}

async function saveAddress()
{
    if (!canEdit.value || saving.value)
    {
        return
    }

    if (!form.value.address.trim())
    {
        errors.value = {address: ['نشانی را وارد کنید.']}
        return
    }

    errors.value = {}
    error.value = null
    saving.value = true

    try
    {
        const payload = buildPayload()

        if (isNew.value)
        {
            await axios.post(
                Routes.customer.address.store(props.customerId),
                payload
            )
        }
        else
        {
            await axios.patch(
                Routes.customer.address.update(props.customerId, editingId.value),
                payload
            )
        }

        window.$toast?.success(
            isNew.value ? 'آدرس با موفقیت ثبت شد.' : 'آدرس با موفقیت ویرایش شد.'
        )

        closeForm()
        await loadAddresses()
        emit('changed')
    } catch (err)
    {
        console.error('خطا در ذخیره آدرس:', err)

        if (err?.response?.status === 422)
        {
            errors.value = err.response.data.errors ?? {}
        }
        else
        {
            error.value =
                err?.response?.data?.message ??
                'ذخیره آدرس انجام نشد.'
        }
    } finally
    {
        saving.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Set Default
|--------------------------------------------------------------------------
*/

async function setDefault(address)
{
    if (!canEdit.value || address.is_default)
    {
        return
    }

    try
    {
        await axios.patch(
            Routes.customer.address.set_default(props.customerId, address.id)
        )

        await loadAddresses()
        emit('changed')
    } catch (err)
    {
        console.error('خطا در تعیین آدرس پیش‌فرض:', err)

        error.value =
            err?.response?.data?.message ??
            'تعیین آدرس پیش‌فرض انجام نشد.'
    }
}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

async function deleteAddress(address)
{
    if (!canEdit.value)
    {
        return
    }

    const confirmed = window.confirm('آیا از حذف این آدرس اطمینان دارید؟')

    if (!confirmed)
    {
        return
    }

    deletingId.value = address.id
    error.value = null

    try
    {
        await axios.delete(
            Routes.customer.address.destroy(props.customerId, address.id)
        )

        window.$toast?.success('آدرس حذف شد.')

        await loadAddresses()
        emit('changed')
    } catch (err)
    {
        console.error('خطا در حذف آدرس:', err)

        error.value =
            err?.response?.data?.message ??
            'حذف آدرس انجام نشد.'
    } finally
    {
        deletingId.value = null
    }
}


/*
|--------------------------------------------------------------------------
| Mount
|--------------------------------------------------------------------------
*/

onMounted(() =>
{
    loadProvinces()
    loadAddresses()
})
</script>


<template>

    <div
        class="mx-auto max-w-4xl space-y-4"
        dir="rtl"
    >

        <!-- Top bar -->

        <div class="flex items-center justify-between">

            <div>
                <h3 class="font-semibold text-slate-800">آدرس‌های مشتری</h3>
                <p class="text-xs text-slate-400">
                    آدرس‌های ثبت‌شده برای ارسال سفارش
                </p>
            </div>

            <button
                v-if="canEdit && !isFormOpen"
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-700"
                @click="openNew"
            >
                <Plus :size="16"/>
                افزودن آدرس
            </button>

        </div>


        <!-- Global error -->

        <div
            v-if="error"
            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
        >
            {{ error }}
        </div>


        <!-- Loading -->

        <div
            v-if="loading"
            class="flex min-h-[200px] items-center justify-center"
        >
            <i class="fa-solid fa-spinner fa-spin text-xl text-teal-600"></i>
        </div>


        <template v-else>

            <!-- Form (new / edit) -->

            <div
                v-if="isFormOpen"
                class="rounded-2xl border border-teal-200 bg-white p-5 shadow-sm"
            >

                <div class="mb-4 flex items-center justify-between">
                    <h4 class="font-semibold text-slate-800">
                        {{ isNew ? 'آدرس جدید' : 'ویرایش آدرس' }}
                    </h4>
                    <button
                        type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        @click="closeForm"
                    >
                        <X :size="16"/>
                    </button>
                </div>

                <div class="grid gap-4 md:grid-cols-2">

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">عنوان آدرس</label>
                        <input
                            v-model="form.title"
                            type="text"
                            placeholder="مثلاً منزل، محل کار"
                            :disabled="saving"
                            :class="inputClass"
                        >
                        <p v-if="fieldError('title')" class="mt-1 text-xs text-red-500">{{ fieldError('title') }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">نام گیرنده</label>
                        <input
                            v-model="form.receiver_name"
                            type="text"
                            :disabled="saving"
                            :class="inputClass"
                        >
                        <p v-if="fieldError('receiver_name')" class="mt-1 text-xs text-red-500">{{ fieldError('receiver_name') }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">موبایل گیرنده</label>
                        <input
                            v-model="form.receiver_mobile"
                            type="tel"
                            dir="ltr"
                            inputmode="tel"
                            placeholder="09xxxxxxxxx"
                            :disabled="saving"
                            :class="inputClass"
                        >
                        <p v-if="fieldError('receiver_mobile')" class="mt-1 text-xs text-red-500">{{ fieldError('receiver_mobile') }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">کد پستی</label>
                        <input
                            v-model="form.postal_code"
                            type="text"
                            dir="ltr"
                            inputmode="numeric"
                            :disabled="saving"
                            :class="inputClass"
                        >
                        <p v-if="fieldError('postal_code')" class="mt-1 text-xs text-red-500">{{ fieldError('postal_code') }}</p>
                    </div>

                    <AppSelect
                        v-model="form.province_id"
                        field="province_id"
                        label="استان"
                        placeholder="انتخاب استان"
                        search-placeholder="جستجوی استان..."
                        :options="provinces"
                        value-key="id"
                        :option-label="p => p.title"
                        :search-keys="['title']"
                        :disabled="saving || loadingProvinces"
                        :error="fieldError('province_id')"
                        @change="onProvinceChange"
                    />

                    <AppSelect
                        v-model="form.city_id"
                        field="city_id"
                        label="شهر"
                        :placeholder="loadingCities ? 'در حال بارگذاری...' : 'انتخاب شهر'"
                        search-placeholder="جستجوی شهر..."
                        :options="cities"
                        value-key="id"
                        :option-label="c => c.title"
                        :search-keys="['title']"
                        :disabled="saving || !form.province_id || loadingCities"
                        :error="fieldError('city_id')"
                    />

                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">نشانی کامل</label>
                        <textarea
                            v-model="form.address"
                            rows="3"
                            :disabled="saving"
                            :class="inputClass"
                        ></textarea>
                        <p v-if="fieldError('address')" class="mt-1 text-xs text-red-500">{{ fieldError('address') }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                            <input
                                v-model="form.is_default"
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                                :disabled="saving"
                            >
                            تعیین به عنوان آدرس پیش‌فرض
                        </label>
                    </div>

                </div>

                <div class="mt-5 flex items-center justify-end gap-3">

                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                        :disabled="saving"
                        @click="closeForm"
                    >
                        انصراف
                    </button>

                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="saving"
                        @click="saveAddress"
                    >
                        <i :class="saving ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-floppy-disk'"></i>
                        <span>{{ saving ? 'در حال ذخیره...' : 'ذخیره آدرس' }}</span>
                    </button>

                </div>

            </div>


            <!-- Empty -->

            <div
                v-else-if="!addresses.length"
                class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-12 text-center text-sm text-slate-400"
            >
                این مشتری آدرسی ثبت نکرده است.
            </div>


            <!-- List -->

            <div
                v-for="address in addresses"
                :key="address.id"
                class="rounded-2xl border bg-white p-5 shadow-sm"
                :class="address.is_default ? 'border-teal-300' : 'border-slate-200'"
            >

                <div class="mb-3 flex items-center justify-between gap-3">

                    <div class="flex min-w-0 items-center gap-2">
                        <MapPin :size="16" class="shrink-0 text-teal-600"/>
                        <span class="truncate font-semibold text-slate-800">
                            {{ address.title || 'آدرس' }}
                        </span>
                        <span
                            v-if="address.is_default"
                            class="inline-flex shrink-0 items-center gap-1 rounded-lg bg-teal-50 px-2 py-0.5 text-xs font-semibold text-teal-700"
                        >
                            <Star :size="11"/>
                            پیش‌فرض
                        </span>
                    </div>

                    <div
                        v-if="canEdit"
                        class="flex shrink-0 items-center gap-1"
                    >

                        <button
                            v-if="!address.is_default"
                            type="button"
                            title="تعیین به عنوان پیش‌فرض"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-teal-50 hover:text-teal-600"
                            @click="setDefault(address)"
                        >
                            <Check :size="16"/>
                        </button>

                        <button
                            type="button"
                            title="ویرایش"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-amber-50 hover:text-amber-600 disabled:opacity-40"
                            :disabled="isFormOpen"
                            @click="openEdit(address)"
                        >
                            <Pencil :size="16"/>
                        </button>

                        <button
                            type="button"
                            title="حذف"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 disabled:opacity-40"
                            :disabled="deletingId === address.id"
                            @click="deleteAddress(address)"
                        >
                            <i
                                v-if="deletingId === address.id"
                                class="fa-solid fa-spinner fa-spin text-xs"
                            ></i>
                            <Trash2 v-else :size="16"/>
                        </button>

                    </div>

                </div>

                <div class="grid gap-3 text-sm sm:grid-cols-2">

                    <div>
                        <div class="text-xs text-slate-400">گیرنده</div>
                        <div class="mt-0.5 text-slate-800">{{ address.receiver_name ?? '—' }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-slate-400">موبایل گیرنده</div>
                        <div dir="ltr" class="mt-0.5 text-slate-800">{{ address.receiver_mobile ?? '—' }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-slate-400">استان / شهر</div>
                        <div class="mt-0.5 text-slate-800">
                            {{ [address.province, address.city].filter(Boolean).join(' / ') || '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-slate-400">کد پستی</div>
                        <div dir="ltr" class="mt-0.5 text-slate-800">{{ address.postal_code ?? '—' }}</div>
                    </div>

                    <div class="sm:col-span-2">
                        <div class="text-xs text-slate-400">نشانی</div>
                        <div class="mt-0.5 leading-7 text-slate-800">{{ address.address }}</div>
                    </div>

                </div>

            </div>

        </template>

    </div>

</template>