<script setup>
import { computed, ref } from 'vue'
import Swal from 'sweetalert2'
import { CheckCircle2, XCircle, Loader2 } from 'lucide-vue-next'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    params: {
        type: Object,
        required: true,
    },
})


/*
|--------------------------------------------------------------------------
| Config
|--------------------------------------------------------------------------
*/

const ACTION_CONFIG = {
    approve: {
        title: 'تأیید نظر؟',
        text: 'آیا از تأیید این نظر مطمئن هستید؟',
        icon: 'question',
        confirmButtonText: 'بله، تأیید کن',
        confirmButtonColor: '#059669',
        successMessage: 'نظر با موفقیت تأیید شد.',
    },
    rejected: {
        title: 'رد نظر؟',
        text: 'آیا از رد این نظر مطمئن هستید؟',
        icon: 'warning',
        confirmButtonText: 'بله، رد کن',
        confirmButtonColor: '#d33',
        successMessage: 'نظر با موفقیت رد شد.',
    },
}


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const busyAction = ref(null)

// از computed استفاده می‌شود تا با تغییر ردیف، مقدار به‌روز بماند
const review = computed(() => props.params.data ?? {})

const canApprove = computed(() => review.value.actioned_type !== 'approved')
const canReject = computed(() => review.value.actioned_type !== 'rejected')


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

async function runAction(action)
{
    if (busyAction.value || !review.value?.id) return

    const config = ACTION_CONFIG[action]

    const result = await Swal.fire({
        title: config.title,
        text: config.text,
        icon: config.icon,
        showCancelButton: true,
        confirmButtonText: config.confirmButtonText,
        cancelButtonText: 'لغو',
        confirmButtonColor: config.confirmButtonColor,
        reverseButtons: true,
    })

    if (!result.isConfirmed) return

    busyAction.value = action

    try
    {
        await axios.post(Routes.product.reviews[action](review.value.id))
        $toast.success(config.successMessage)

        // رفرش جدول و آمار از صفحه اصلی
        props.params.onChanged?.()
    }
    catch (error)
    {
        console.error(`خطا در عملیات ${action} نظر:`, error)

        $toast.error(
            error.response?.data?.message ??
            'عملیات با خطا مواجه شد. لطفاً دوباره تلاش کنید.'
        )
    }
    finally
    {
        busyAction.value = null
    }
}

function approve()
{
    return runAction('approve')
}

function rejected()
{
    return runAction('rejected')
}
</script>

<template>
    <div class="flex h-full items-center gap-1.5">

        <button
            v-if="canApprove"
            type="button"
            :disabled="busyAction !== null"
            title="تأیید"
            class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-600 text-white transition hover:bg-emerald-700 disabled:opacity-60"
            @click="approve"
        >
            <Loader2 v-if="busyAction === 'approve'" :size="13" class="animate-spin" />
            <CheckCircle2 v-else :size="14" />
        </button>

        <button
            v-if="canReject"
            type="button"
            :disabled="busyAction !== null"
            title="رد"
            class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-rose-200 bg-white text-rose-600 transition hover:bg-rose-50 disabled:opacity-60"
            @click="rejected"
        >
            <Loader2 v-if="busyAction === 'rejected'" :size="13" class="animate-spin" />
            <XCircle v-else :size="14" />
        </button>

    </div>
</template>