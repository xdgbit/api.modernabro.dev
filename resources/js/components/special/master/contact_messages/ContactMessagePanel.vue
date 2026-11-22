<template>
    <div
        dir="rtl"
        class="w-full p-4"
    >

        <!-- Loading -->
        <div
            v-if="loading"
            class="flex min-h-[250px] items-center justify-center"
        >
            <i class="fa-solid fa-spinner fa-spin text-2xl text-[#e94b5f]"></i>
        </div>


        <!-- Error / Not found -->
        <div
            v-else-if="!message"
            class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
        >
            {{ error ?? 'پیام پیدا نشد.' }}
        </div>


        <!-- Content -->
        <div
            v-else
            class="space-y-5"
        >

            <!-- Status -->
            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-4">

                <div class="flex items-center gap-3">

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg"
                        :class="message.is_read
                            ? 'bg-emerald-100 text-emerald-600'
                            : 'bg-red-100 text-red-600'"
                    >
                        <i
                            class="fa-solid"
                            :class="message.is_read ? 'fa-envelope-open' : 'fa-envelope'"
                        ></i>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-gray-800">
                            {{ message.is_read ? 'خوانده‌شده' : 'خوانده‌نشده' }}
                        </div>

                        <div
                            v-if="message.jalali_submitted_at"
                            dir="ltr"
                            class="mt-1 text-right text-xs text-gray-500"
                        >
                            {{ message.jalali_submitted_at }}
                        </div>
                    </div>

                </div>

            </div>


            <!-- Sender -->
            <div class="grid gap-3 sm:grid-cols-2">

                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="text-xs text-gray-400">نام و نام خانوادگی</div>
                    <div class="mt-1 text-sm font-semibold text-gray-800">
                        {{ message.full_name }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="text-xs text-gray-400">شماره موبایل</div>
                    <a
                        :href="`tel:${message.mobile}`"
                        dir="ltr"
                        class="mt-1 block text-right text-sm font-semibold text-[#14b8a6] hover:text-[#0d9488]"
                    >
                        {{ message.mobile }}
                    </a>
                </div>

            </div>


            <!-- Subject -->
            <div class="rounded-xl border border-gray-200 p-4">
                <div class="text-xs text-gray-400">موضوع</div>
                <div class="mt-1 text-sm font-semibold text-gray-800">
                    {{ message.subject }}
                </div>
            </div>


            <!-- Message -->
            <div class="rounded-xl border border-gray-200 p-4">
                <div class="text-xs text-gray-400">متن پیام</div>
                <p class="mt-2 whitespace-pre-line break-words text-sm leading-7 text-gray-700">
                    {{ message.message }}
                </p>
            </div>


            <!-- General Error -->
            <div
                v-if="error"
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
            >
                {{ error }}
            </div>


            <!-- Actions -->
            <div class="flex flex-col gap-3 sm:flex-row">

                <button
                    type="button"
                    :disabled="toggling || deleting"
                    class="flex flex-1 items-center justify-center gap-2 rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60"
                    @click="toggleRead"
                >
                    <i
                        v-if="toggling"
                        class="fa-solid fa-spinner fa-spin"
                    ></i>

                    <span>
                        {{ message.is_read ? 'علامت‌گذاری به‌عنوان خوانده‌نشده' : 'علامت‌گذاری به‌عنوان خوانده‌شده' }}
                    </span>
                </button>

                <button
                    type="button"
                    :disabled="toggling || deleting"
                    class="flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-gray-400"
                    @click="deleteMessage"
                >
                    <i
                        v-if="deleting"
                        class="fa-solid fa-spinner fa-spin"
                    ></i>

                    <span>حذف پیام</span>
                </button>

            </div>

        </div>

    </div>
</template>


<script setup>
import {ref, onMounted} from 'vue'
import axios from 'axios'
import {toast as $toast} from 'vue-sonner'
import Swal from 'sweetalert2'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    messageId: {
        type: [Number, String],
        required: true,
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
const toggling = ref(false)
const deleting = ref(false)
const error = ref(null)
const message = ref(null)


/*
|--------------------------------------------------------------------------
| Get Data (سمت سرور پیام را خوانده‌شده می‌کند)
|--------------------------------------------------------------------------
*/

const getData = async () =>
{
    loading.value = true
    error.value = null

    try
    {
        const response = await axios.post(
            Routes.contact_message.jspanel,
            {contact_message_id: props.messageId}
        )

        message.value =
            response.data?.data?.contact_message ??
            response.data?.data ??
            null

        // چون با باز شدن، پیام خوانده می‌شود، جدول و آمار باید تازه شوند
        props.onSaved?.(response.data)

    } catch (err)
    {
        error.value =
            err.response?.data?.message ??
            'خطا در دریافت اطلاعات پیام.'

        $toast.error(error.value)

    } finally
    {
        loading.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Toggle Read
|--------------------------------------------------------------------------
*/

const toggleRead = async () =>
{
    toggling.value = true
    error.value = null

    try
    {
        const response = await axios.post(
            Routes.contact_message.toggle_read(props.messageId)
        )

        message.value = {
            ...message.value,
            is_read: !!response.data?.data?.is_read,
        }

        $toast.success(response.data?.message ?? 'وضعیت پیام تغییر کرد.')
        props.onSaved?.(response.data)

    } catch (err)
    {
        error.value =
            err.response?.data?.message ??
            'خطا در تغییر وضعیت پیام.'

        $toast.error(error.value)

    } finally
    {
        toggling.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const deleteMessage = async () =>
{
    const result = await Swal.fire({
        title: 'حذف پیام؟',
        text: 'آیا از حذف این پیام مطمئن هستید؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'لغو',
        confirmButtonColor: '#d33',
        reverseButtons: true,
    })

    if (!result.isConfirmed)
    {
        return
    }

    deleting.value = true
    error.value = null

    try
    {
        const response = await axios.delete(
            Routes.contact_message.delete(props.messageId)
        )

        $toast.success(response.data?.message ?? 'پیام با موفقیت حذف شد.')
        props.onSaved?.(response.data)
        window[props.jsPanelVariableName]?.close()
    }
    catch (err)
    {
        error.value =
            err.response?.data?.message ??
            'حذف پیام با خطا مواجه شد.'

        $toast.error(error.value)
    }
    finally
    {
        deleting.value = false
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