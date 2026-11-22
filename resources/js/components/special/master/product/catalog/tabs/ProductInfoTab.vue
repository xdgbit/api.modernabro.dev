<script setup>
import {ref, computed, onMounted} from 'vue'

const props = defineProps({
    productId: {
        type: [Number, String],
        default: null,
    },

    readOnly: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['saved', 'loaded', 'close'])

const statusOptions = [
    {value: 'active', label: 'فعال'},
    {value: 'inactive', label: 'غیرفعال'},
]

const starOptions = [
    {value: 0, label: 'بدون ستاره'},
    {value: 1, label: '۱ ستاره'},
    {value: 2, label: '۲ ستاره'},
    {value: 3, label: '۳ ستاره'},
    {value: 4, label: '۴ ستاره'},
    {value: 5, label: '۵ ستاره'},
]

const inputClass =
    'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100 disabled:cursor-not-allowed disabled:bg-slate-100'

const loading = ref(false)
const saving = ref(false)
const error = ref(null)

const brands = ref([])
const categories = ref([])

// امتیاز کاربران (فقط خواندنی)
const userRating = ref({
    average: 0,
    count: 0,
})

const userRatingDistribution = ref({
    5: 0,
    4: 0,
    3: 0,
    2: 0,
    1: 0,
})

const form = ref({
    name: '',
    sku: '',
    brand_id: '',
    category_id: '',
    status: 'active',
    price: '',
    sale_price: '',
    stock: 0,
    low_stock_threshold: 0,
    // درجه‌بندی دستی محصول توسط مدیریت؛ صفر یعنی بدون ستاره
    star_rating: 0,
    description: '',
})
const canSave = computed(() =>
    !props.readOnly && !saving.value && !loading.value
)
const isEdit = computed(() => !!props.productId)

const stockStatus = computed(() =>
{
    const stock = Number(form.value.stock || 0)
    const threshold = Number(form.value.low_stock_threshold || 0)

    if (stock <= 0)
    {
        return {label: 'ناموجود', class: 'bg-rose-100 text-rose-700'}
    }

    if (stock <= threshold)
    {
        return {label: 'کم‌موجودی', class: 'bg-amber-100 text-amber-700'}
    }

    return {label: 'موجود', class: 'bg-emerald-100 text-emerald-700'}
})

const hasSalePrice = computed(() =>
    form.value.sale_price !== '' &&
    form.value.sale_price !== null
)

const discountPercent = computed(() =>
{
    const price = Number(form.value.price || 0)
    const sale = Number(form.value.sale_price || 0)

    if (!hasSalePrice.value || price <= 0 || sale >= price)
    {
        return 0
    }

    return Math.round(((price - sale) / price) * 100)
})

// ---------- امتیازدهی ----------

const adminStars = computed(() => Number(form.value.star_rating || 0))

const hasUserRating = computed(() => userRating.value.count > 0)

// امتیازی که در سایت نمایش داده می‌شود: اولویت با ستاره‌های مدیریت است
const displayedRating = computed(() =>
{
    if (adminStars.value > 0)
    {
        return {
            source: 'admin',
            value: adminStars.value,
            label: 'درجه‌بندی مدیریت',
        }
    }

    if (hasUserRating.value)
    {
        return {
            source: 'user',
            value: userRating.value.average,
            label: `امتیاز کاربران (${formatNumber(userRating.value.count)} نظر)`,
        }
    }

    return {
        source: 'none',
        value: 0,
        label: 'بدون امتیاز',
    }
})

const userDistributionRows = computed(() =>
    [5, 4, 3, 2, 1].map(star =>
    {
        const count = Number(userRatingDistribution.value[star] ?? 0)
        const percent = userRating.value.count
            ? Math.round((count / userRating.value.count) * 100)
            : 0

        return {star, count, percent}
    })
)

function isStarFilled(index, value)
{
    return index <= Math.round(Number(value || 0))
}

function formatNumber(value, options = {})
{
    return Number(value ?? 0).toLocaleString('fa-IR', options)
}

function formatAmount(value)
{
    return formatNumber(value)
}

function formatAverage(value)
{
    return formatNumber(value, {
        minimumFractionDigits: 1,
        maximumFractionDigits: 1,
    })
}

// ---------- دریافت و ذخیره ----------

async function getData()
{
    loading.value = true
    error.value = null

    try
    {
        const response = await axios.post(
            Routes.product.jspanel,
            {
                product_id: props.productId || null,
            }
        )

        const data = response.data.data

        brands.value = data.brands ?? []
        categories.value = data.categories ?? []

        if (!data.product)
        {
            return
        }

        const product = data.product

        form.value = {
            name: product.name ?? '',
            sku: product.sku ?? '',
            brand_id: product.brand_id ?? '',
            category_id: product.category_id ?? '',
            status: product.status ?? 'active',
            price: product.price ?? '',
            sale_price: product.sale_price ?? '',
            stock: product.stock ?? 0,
            low_stock_threshold: product.low_stock_threshold ?? 0,
            star_rating: Number(product.star_rating ?? 0),
            description: product.description ?? '',
        }

        userRating.value = {
            average: Number(product.user_rating_average ?? 0),
            count: Number(product.user_rating_count ?? 0),
        }

        userRatingDistribution.value = {
            5: Number(product.user_rating_distribution?.[5] ?? 0),
            4: Number(product.user_rating_distribution?.[4] ?? 0),
            3: Number(product.user_rating_distribution?.[3] ?? 0),
            2: Number(product.user_rating_distribution?.[2] ?? 0),
            1: Number(product.user_rating_distribution?.[1] ?? 0),
        }

        emit('loaded', {
            id: product.id,
            title: product.name ?? '',
        })
    } catch (err)
    {
        console.error('خطا در دریافت اطلاعات محصول:', err)

        error.value =
            err?.response?.data?.message ??
            'خطا در دریافت اطلاعات محصول.'
    } finally
    {
        loading.value = false
    }
}

function validate()
{
    error.value = null

    if (!form.value.name.trim())
    {
        error.value = 'نام محصول را وارد کنید.'
        return false
    }

    if (!form.value.sku.trim())
    {
        error.value = 'کد محصول (SKU) را وارد کنید.'
        return false
    }

    if (form.value.price === '' || Number(form.value.price) < 0)
    {
        error.value = 'قیمت محصول معتبر نیست.'
        return false
    }

    if (hasSalePrice.value && Number(form.value.sale_price) < 0)
    {
        error.value = 'قیمت فروش ویژه نمی‌تواند منفی باشد.'
        return false
    }

    if (
        hasSalePrice.value &&
        Number(form.value.sale_price) >= Number(form.value.price)
    )
    {
        error.value = 'قیمت فروش ویژه باید کمتر از قیمت اصلی باشد.'
        return false
    }

    if (Number(form.value.stock) < 0)
    {
        error.value = 'موجودی انبار نمی‌تواند منفی باشد.'
        return false
    }

    if (Number(form.value.low_stock_threshold) < 0)
    {
        error.value = 'حداقل موجودی هشدار نمی‌تواند منفی باشد.'
        return false
    }

    if (
        Number(form.value.star_rating) < 0 ||
        Number(form.value.star_rating) > 5
    )
    {
        error.value = 'تعداد ستاره باید بین ۰ تا ۵ باشد.'
        return false
    }

    return true
}

async function saveProduct()
{
    if (props.readOnly || !validate())
    {
        return
    }

    saving.value = true
    error.value = null

    try
    {
        const payload = {
            name: form.value.name.trim(),
            sku: form.value.sku.trim(),
            brand_id: form.value.brand_id || null,
            category_id: form.value.category_id || null,
            status: form.value.status,
            price: Number(form.value.price),
            sale_price: hasSalePrice.value
                ? Number(form.value.sale_price)
                : null,
            stock: Number(form.value.stock),
            low_stock_threshold: Number(form.value.low_stock_threshold),
            star_rating: Number(form.value.star_rating),
            description: form.value.description || null,
        }

        const response = isEdit.value
            ? await axios.patch(
                Routes.product.update(props.productId),
                payload
            )
            : await axios.post(
                Routes.product.store,
                payload
            )

        const id =
            response.data?.data?.product?.id ??
            response.data?.data?.id ??
            props.productId

        $toast.success(
            isEdit.value
                ? 'اطلاعات محصول با موفقیت ویرایش شد.'
                : 'محصول با موفقیت ثبت شد.'
        )

        emit('saved', {
            id,
            title: payload.name,
        })
    } catch (err)
    {
        console.error('خطا در ذخیره محصول:', err)

        const errors = err?.response?.data?.errors
        const firstError = errors
            ? Object.values(errors)[0]?.[0]
            : null

        error.value =
            firstError ??
            err?.response?.data?.message ??
            'ذخیره محصول انجام نشد.'
    } finally
    {
        saving.value = false
    }
}

onMounted(getData)
</script>

<template>
    <div>
        <!-- Loading -->
        <div
            v-if="loading"
            class="flex min-h-[400px] items-center justify-center"
        >
            <i class="fa-solid fa-spinner fa-spin ml-2 text-xl text-teal-600"></i>
            <span class="text-sm text-slate-500">در حال بارگذاری...</span>
        </div>

        <div v-else class="mx-auto max-w-4xl space-y-5">
            <!-- Error -->
            <div
                v-if="error"
                class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
            >
                {{ error }}
            </div>

            <!-- Status header -->
            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">
                    اطلاعات پایه، قیمت، موجودی و امتیاز محصول
                </p>

                <span
                    class="shrink-0 rounded-lg px-3 py-1 text-xs font-bold"
                    :class="stockStatus.class"
                >
                    {{ stockStatus.label }}
                </span>
            </div>

            <!-- Rating -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

                <!-- Admin star rating -->
                <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-5">
                    <div class="mb-3">
                        <h4 class="text-sm font-bold text-slate-700">
                            درجه‌بندی محصول
                        </h4>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            اولویت نمایش با ستاره‌های تعیین‌شده توسط مدیریت است؛ در غیر این صورت، امتیاز کاربران نمایش داده می‌شود.
                        </p>
                    </div>

                    <label
                        for="product-star-rating"
                        class="mb-2 block text-sm font-medium text-slate-600"
                    >
                        تعداد ستاره محصول
                    </label>

                    <select
                        id="product-star-rating"
                        v-model.number="form.star_rating"
                        :disabled="readOnly"
                        :class="inputClass"
                    >
                        <option
                            v-for="option in starOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>

                    <div class="mt-3 flex items-center gap-1" aria-label="پیش‌نمایش تعداد ستاره">
                        <i
                            v-for="star in 5"
                            :key="star"
                            class="fa-solid fa-star text-lg"
                            :class="star <= adminStars ? 'text-amber-400' : 'text-slate-300'"
                        ></i>
                        <span class="mr-2 text-xs text-slate-500">
                            {{ adminStars ? `${formatNumber(adminStars)} از ۵ ستاره` : 'بدون درجه‌بندی' }}
                        </span>
                    </div>
                </div>

                <!-- User rating (read-only) -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h4 class="text-sm font-bold text-slate-700">
                            امتیاز کاربران
                        </h4>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">
                            فقط خواندنی
                        </span>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-slate-800">
                                {{ hasUserRating ? formatAverage(userRating.average) : '-' }}
                            </div>
                            <div class="mt-1 text-[11px] text-slate-400">
                                از ۵
                            </div>
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center gap-1">
                                <i
                                    v-for="star in 5"
                                    :key="star"
                                    class="fa-solid fa-star text-base"
                                    :class="isStarFilled(star, userRating.average) ? 'text-amber-400' : 'text-slate-300'"
                                ></i>
                            </div>
                            <div class="mt-1 text-xs text-slate-500">
                                {{ formatNumber(userRating.count) }} نظر ثبت شده
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2">
                        <div
                            v-for="row in userDistributionRows"
                            :key="row.star"
                            class="flex items-center gap-3 text-xs text-slate-500"
                        >
                            <span class="w-12 shrink-0">{{ formatNumber(row.star) }} ستاره</span>
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full bg-amber-400 transition-all"
                                    :style="{width: row.percent + '%'}"
                                ></div>
                            </div>
                            <span class="w-10 shrink-0 text-left">{{ formatNumber(row.count) }}</span>
                        </div>
                    </div>

                    <div
                        v-if="!hasUserRating"
                        class="mt-4 rounded-xl border border-dashed border-slate-300 px-4 py-3 text-center text-xs text-slate-400"
                    >
                        هنوز نظری برای این محصول ثبت نشده است.
                    </div>
                </div>
            </div>

            <!-- Displayed rating preview -->
            <div class="flex items-center justify-between rounded-xl border border-teal-200 bg-teal-50 px-4 py-3">
                <span class="text-xs text-teal-700">امتیاز نمایش‌داده‌شده در سایت</span>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-teal-700">{{ displayedRating.label }}</span>
                    <span
                        v-if="displayedRating.source !== 'none'"
                        class="text-sm font-bold text-teal-700"
                    >
                        {{ displayedRating.source === 'admin'
                        ? formatNumber(displayedRating.value)
                        : formatAverage(displayedRating.value) }}
                    </span>
                </div>
            </div>

            <!-- Basic info -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-5 font-bold text-slate-800">اطلاعات پایه</h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            نام محصول
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="نام محصول را وارد کنید"
                            :disabled="readOnly"
                            :class="inputClass"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            کد محصول (SKU)
                        </label>
                        <input
                            v-model="form.sku"
                            type="text"
                            dir="ltr"
                            placeholder="SKU-0001"
                            :disabled="readOnly"
                            :class="inputClass"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            وضعیت انتشار
                        </label>

                        <div class="flex items-center gap-6 rounded-xl border border-slate-300 bg-white px-4 py-3">
                            <label
                                v-for="option in statusOptions"
                                :key="option.value"
                                class="flex items-center gap-2 text-sm text-slate-700"
                                :class="readOnly ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'"
                            >
                                <input
                                    v-model="form.status"
                                    type="radio"
                                    name="product-status"
                                    :value="option.value"
                                    :disabled="readOnly"
                                    class="h-4 w-4 accent-teal-600"
                                />
                                {{ option.label }}
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            برند
                        </label>
                        <AppSelect
                            v-model="form.brand_id"
                            field="brand_id"
                            placeholder="انتخاب برند"
                            search-placeholder="جستجوی برند..."
                            :options="brands"
                            value-key="id"
                            :option-label="p => p.title"
                            :search-keys="['title']"
                            :disabled="readOnly"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            دسته‌بندی
                        </label>
                        <AppSelect
                            v-model="form.category_id"
                            field="category_id"
                            placeholder="انتخاب دسته‌بندی"
                            search-placeholder="جستجوی دسته‌بندی..."
                            :options="categories"
                            value-key="id"
                            :option-label="p => p.title"
                            :search-keys="['title']"
                            :disabled="readOnly"
                        />
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-5 font-bold text-slate-800">قیمت‌گذاری</h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            قیمت اصلی
                        </label>
                        <AppInputNumeric
                            v-model="form.price"
                            field="price"
                            dir="ltr"
                            :disabled="readOnly"
                            placeholder="قیمت اصلی محصول را وارد نمایید"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            قیمت فروش ویژه
                        </label>
                        <AppInputNumeric
                            v-model="form.sale_price"
                            field="sale_price"
                            dir="ltr"
                            :disabled="readOnly"
                            placeholder="خالی بگذارید اگر تخفیف ندارد"
                        />
                    </div>
                </div>

                <div
                    v-if="discountPercent > 0"
                    class="mt-4 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3"
                >
                    <span class="text-xs text-emerald-700">میزان تخفیف</span>
                    <span class="text-sm font-bold text-emerald-700">
                        {{ formatAmount(discountPercent) }}٪
                    </span>
                </div>
            </div>

            <!-- Inventory -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-5 font-bold text-slate-800">موجودی انبار</h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            موجودی فعلی
                        </label>
                        <input
                            v-model.number="form.stock"
                            type="number"
                            min="0"
                            dir="ltr"
                            :disabled="readOnly"
                            :class="inputClass"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            حداقل موجودی هشدار
                        </label>
                        <input
                            v-model.number="form.low_stock_threshold"
                            type="number"
                            min="0"
                            dir="ltr"
                            :disabled="readOnly"
                            :class="inputClass"
                        />
                    </div>
                </div>

                <p class="mt-3 text-xs text-slate-400">
                    وقتی موجودی به این عدد یا کمتر برسد، محصول در بخش «نیازمند تأمین موجودی» نمایش داده می‌شود.
                </p>
            </div>

            <!-- Description -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-5 font-bold text-slate-800">توضیحات محصول</h3>

                <textarea
                    v-model="form.description"
                    rows="4"
                    placeholder="توضیحات کوتاه درباره محصول..."
                    :disabled="readOnly"
                    :class="inputClass"
                ></textarea>
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 z-40 rounded-2xl border border-slate-200 bg-white p-4 shadow-lg">
                <div class="flex gap-3">
                    <template v-if="!readOnly">
                        <button
                            type="button"
                            :disabled="saving"
                            class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:opacity-50"
                            @click="emit('close')"
                        >
                            انصراف
                        </button>

                        <button
                            type="button"
                            :disabled="!canSave"
                            class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="saveProduct"
                        >
                            <i
                                v-if="saving"
                                class="fa-solid fa-spinner fa-spin ml-2"
                            ></i>

                            {{
                                saving
                                    ? 'در حال ذخیره...'
                                    : isEdit
                                        ? 'ذخیره تغییرات'
                                        : 'ثبت محصول'
                            }}
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>