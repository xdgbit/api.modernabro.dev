<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import Swal from 'sweetalert2'
import axios from 'axios'
import ReviewDatatable from './ReviewDatatable.vue'
import {
    Star,
    CheckCircle2,
    XCircle,
    Hourglass,
    MessageSquare,
    Search,
    Building2,
    Loader2,
} from 'lucide-vue-next'


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const DEFAULT_FILTERS = {
    actioned_type: '',
    rating: '',
    search: '',
    product_id: '',
}

const filters = ref({ ...DEFAULT_FILTERS })
const reviewDatatableRef = ref(null)

const stats = ref({
    total: 0,
    pending: 0,
    approved: 0,
    rejected: 0,
    average_rating: 0,
})

const pendingReviews = ref([])
const statsLoading = ref(false)
const actionLoadingId = ref(null)
const productOptions = ref([])

let searchTimer = null


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const filteredDatatableUrl = computed(() =>
{
    const params = new URLSearchParams()

    Object.entries(filters.value).forEach(([key, value]) =>
    {
        if (value !== '' && value !== null && value !== undefined)
        {
            params.append(key, value)
        }
    })

    const query = params.toString()

    return query
        ? `${Routes.product.reviews.datatable}?${query}`
        : Routes.product.reviews.datatable
})

const statCards = computed(() => [
    {
        key: 'total',
        label: 'کل نظرات',
        value: stats.value.total,
        icon: MessageSquare,
        iconClass: 'bg-slate-100 text-slate-600',
    },
    {
        key: 'approved',
        label: 'تأیید شده',
        value: stats.value.approved,
        icon: CheckCircle2,
        iconClass: 'bg-emerald-100 text-emerald-600',
    },
    {
        key: 'pending',
        label: 'در انتظار تأیید',
        value: stats.value.pending,
        icon: Hourglass,
        iconClass: 'bg-amber-100 text-amber-600',
    },
    {
        key: 'rejected',
        label: 'رد شده',
        value: stats.value.rejected,
        icon: XCircle,
        iconClass: 'bg-rose-100 text-rose-600',
    },
    {
        key: 'average_rating',
        label: 'میانگین امتیاز',
        value: stats.value.average_rating,
        icon: Star,
        iconClass: 'bg-teal-100 text-teal-600',
    },
])


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function formatNumber(value)
{
    return Number(value ?? 0).toLocaleString('fa-IR')
}

function formatStatValue(card)
{
    if (card.key === 'average_rating')
    {
        return Number(card.value ?? 0).toLocaleString('fa-IR', {
            minimumFractionDigits: 1,
            maximumFractionDigits: 1,
        })
    }

    return formatNumber(card.value)
}

function starsArray(rating)
{
    const value = Math.round(Number(rating ?? 0))

    return Array.from({ length: 5 }, (_, index) => index < value)
}


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

function applyFilter(key, value)
{
    filters.value = {
        ...filters.value,
        [key]: value,
    }
}

function onSearchInput(event)
{
    clearTimeout(searchTimer)

    searchTimer = setTimeout(() =>
    {
        applyFilter('search', event.target.value.trim())
    }, 400)
}

function resetFilters()
{
    filters.value = { ...DEFAULT_FILTERS }
}


/*
|--------------------------------------------------------------------------
| Data Loading
|--------------------------------------------------------------------------
*/

async function loadStats()
{
    statsLoading.value = true

    try
    {
        const response = await axios.get(Routes.product.reviews.stats)

        if (response.data.success)
        {
            const data = response.data.data ?? {}

            stats.value = {
                total: data.total ?? 0,
                approved: data.approved ?? 0,
                pending: data.pending ?? 0,
                rejected: data.rejected ?? 0,
                average_rating: data.average_rating ?? 0,
            }

            pendingReviews.value = data.pending_reviews ?? []
        }
    }
    catch (error)
    {
        console.error('خطا در دریافت آمار نظرات:', error)
    }
    finally
    {
        statsLoading.value = false
    }
}

async function loadproducts()
{
    try
    {
        const response = await axios.get(Routes.product.options)

        if (response.data.success)
        {
            productOptions.value = response.data.data ?? []
        }
    }
    catch (error)
    {
        console.error('خطا در دریافت لیست محصولات:', error)
    }
}

function refreshAll()
{
    reviewDatatableRef.value?.refreshGrid()
    loadStats()
}

/*
|--------------------------------------------------------------------------
| Review Actions (کارت‌های نیازمند بررسی)
|--------------------------------------------------------------------------
*/

const REVIEW_ACTION_CONFIG = {
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

async function runReviewAction(review, action)
{
    if (!review?.id || actionLoadingId.value) return

    const config = REVIEW_ACTION_CONFIG[action]

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

    actionLoadingId.value = review.id

    try
    {
        await axios.post(Routes.product.reviews[action](review.id))

        $toast.success(config.successMessage)

        refreshAll()
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
        actionLoadingId.value = null
    }
}

function approveReview(review)
{
    return runReviewAction(review, 'approve')
}

function rejectReview(review)
{
    return runReviewAction(review, 'rejected')
}




/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() =>
{
    loadStats()
    loadproducts()
})

onBeforeUnmount(() =>
{
    clearTimeout(searchTimer)
})
</script>


<template>

    <div class="space-y-5">

        <!-- Header -->

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-800 lg:text-2xl">
                    مدیریت نظرات محصولات
                </h1>

                <p class="mt-1 text-sm text-slate-400">
                    بررسی، تأیید و مدیریت نظرات محصولات
                </p>
            </div>
        </div>


        <!-- Stats -->

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">

            <div
                v-for="card in statCards"
                :key="card.key"
                class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                    :class="card.iconClass"
                >
                    <Loader2 v-if="statsLoading" :size="18" class="animate-spin" />
                    <component v-else :is="card.icon" :size="18" />
                </div>

                <div class="min-w-0">
                    <div class="text-xs text-slate-400">{{ card.label }}</div>
                    <div class="mt-0.5 text-lg font-bold text-slate-800">
                        {{ formatStatValue(card) }}
                    </div>
                </div>
            </div>

        </div>


        <!-- Pending Reviews -->

        <div
            v-if="stats.pending > 0"
            class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm"
        >

            <div class="mb-2 flex items-center gap-2">
                <h2 class="text-base font-bold text-slate-800">
                    نیازمند بررسی
                </h2>

                <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-amber-100 px-2 text-xs font-bold text-amber-700">
                    {{ formatNumber(stats.pending) }}
                </span>
            </div>

            <div class="max-h-[320px] overflow-y-auto overflow-x-hidden pr-1">

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">

                    <div
                        v-for="review in pendingReviews"
                        :key="review.id"
                        class="flex min-w-0 flex-col gap-2 rounded-lg border border-slate-100 bg-slate-50 p-3"
                    >

                        <!-- product + Rating -->

                        <div class="flex items-center justify-between gap-2">

                            <div class="flex min-w-0 items-center gap-1.5">
                                <Building2 :size="14" class="shrink-0 text-slate-400" />
                                <span class="truncate text-xs font-bold text-slate-700">
                                    {{ review.product_name || 'بدون نام' }}
                                </span>
                            </div>

                            <div class="flex shrink-0 items-center gap-0.5" dir="ltr">
                                <Star
                                    v-for="(filled, index) in starsArray(review.rating)"
                                    :key="index"
                                    :size="12"
                                    :class="filled ? 'fill-amber-400 text-amber-400' : 'text-slate-300'"
                                />
                            </div>

                        </div>


                        <!-- Author + Date -->

                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span class="truncate font-medium text-slate-600">
                                {{ review.author_name || 'مهمان' }}
                            </span>
                            <span class="shrink-0" dir="ltr">{{ review.created_at }}</span>
                        </div>


                        <!-- Comment -->

                        <p class="line-clamp-3 text-xs leading-relaxed text-slate-600">
                            {{ review.comment }}
                        </p>


                        <!-- Actions -->

                        <div class="mt-auto flex items-center gap-2 pt-1">

                            <button
                                type="button"
                                :disabled="actionLoadingId === review.id"
                                class="inline-flex h-8 flex-1 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-60"
                                @click="approveReview(review)"
                            >
                                <Loader2 v-if="actionLoadingId === review.id" :size="13" class="animate-spin" />
                                <CheckCircle2 v-else :size="13" />
                                تأیید
                            </button>

                            <button
                                type="button"
                                :disabled="actionLoadingId === review.id"
                                class="inline-flex h-8 flex-1 items-center justify-center gap-1.5 rounded-lg border border-rose-200 bg-white text-xs font-semibold text-rose-600 transition hover:bg-rose-50 disabled:opacity-60"
                                @click="rejectReview(review)"
                            >
                                <XCircle :size="13" />
                                رد
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Datatable -->

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex flex-col gap-1 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-sm font-bold text-slate-800">
                    فهرست نظرات
                </h2>
            </div>

            <div class="p-1">

                <!-- Filters -->

                <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">

                    <div class="relative lg:col-span-2">
                        <Search :size="15" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            type="text"
                            placeholder="جستجو در نام، متن نظر یا محصول..."
                            class="h-10 w-full rounded-xl border border-slate-200 bg-white pr-9 pl-3 text-sm text-slate-700 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                            :value="filters.search"
                            @input="onSearchInput"
                        />
                    </div>

                    <select
                        class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-teal-500"
                        :value="filters.product_id"
                        @change="applyFilter('product_id', $event.target.value)"
                    >
                        <option value="">همه محصولات</option>
                        <option
                            v-for="item in productOptions"
                            :key="item.id"
                            :value="item.id"
                        >
                            {{ item.title }}
                        </option>
                    </select>

                    <select
                        class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-teal-500"
                        :value="filters.actioned_type"
                        @change="applyFilter('actioned_type', $event.target.value)"
                    >
                        <option value="">همه وضعیت‌ها</option>
                        <option value="pending">در انتظار تأیید</option>
                        <option value="approved">تأیید شده</option>
                        <option value="rejected">رد شده</option>
                    </select>

                    <div class="flex gap-2">
                        <select
                            class="h-10 min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-teal-500"
                            :value="filters.rating"
                            @change="applyFilter('rating', $event.target.value)"
                        >
                            <option value="">همه امتیازها</option>
                            <option v-for="n in 5" :key="n" :value="n">{{ formatNumber(n) }} ستاره</option>
                        </select>

                        <button
                            type="button"
                            class="h-10 shrink-0 rounded-xl px-3 text-sm font-medium text-slate-500 transition hover:bg-slate-100"
                            @click="resetFilters"
                        >
                            پاک کردن
                        </button>
                    </div>

                </div>

                <ReviewDatatable
                    :key="filteredDatatableUrl"
                    ref="reviewDatatableRef"
                    :api-url="filteredDatatableUrl"
                    @changed="loadStats"
                />

            </div>

        </div>

    </div>

</template>