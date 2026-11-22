<script setup>
import { computed } from 'vue'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
})


/*
|--------------------------------------------------------------------------
| Format Helpers
|--------------------------------------------------------------------------
*/

function formatAmount(value)
{
    if (value === null || value === undefined || value === '')
    {
        return '—'
    }

    return `${Number(value).toLocaleString('fa-IR')} تومان`
}

function formatNumber(value)
{
    return Number(value ?? 0).toLocaleString('fa-IR')
}


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const items = computed(() => props.order.items ?? [])

const itemsCount = computed(() =>
    items.value.reduce((sum, item) => sum + Number(item.quantity || 0), 0)
)

const hasDiscount = computed(() =>
    Number(props.order.discount_amount || 0) > 0
)

const hasShipping = computed(() =>
    Number(props.order.shipping_cost || 0) > 0
)

function itemUnitPrice(item)
{
    return item.sale_price ?? item.unit_price
}

// خط‌خوردگی فقط وقتی نشان داده می‌شود که قیمت فروش واقعاً از قیمت اصلی کمتر باشد
function itemHasSale(item)
{
    return (
        item.sale_price !== null &&
        item.sale_price !== undefined &&
        Number(item.sale_price) < Number(item.unit_price)
    )
}
</script>


<template>

    <div
        class="mx-auto max-w-4xl space-y-5"
        dir="rtl"
    >

        <!-- اطلاعات سفارش -->

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <h3 class="mb-5 font-bold text-slate-800">
                اطلاعات سفارش
            </h3>

            <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">

                <div>
                    <div class="text-xs text-slate-400">شماره سفارش</div>
                    <div dir="ltr" class="mt-1 font-semibold text-slate-800">
                        {{ order.code }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-400">تاریخ ثبت</div>
                    <div class="mt-1 font-semibold text-slate-800">
                        {{ order.jalali_created_at ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-400">مشتری</div>
                    <div class="mt-1 font-semibold text-slate-800">
                        {{ order.customer?.full_name ?? '—' }}
                    </div>
                    <div
                        v-if="order.customer?.mobile_number"
                        dir="ltr"
                        class="mt-0.5 text-xs text-slate-500"
                    >
                        {{ order.customer.mobile_number }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-400">روش پرداخت</div>
                    <div class="mt-1 font-semibold text-slate-800">
                        {{ order.payment_method?.title ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-400">روش ارسال</div>
                    <div class="mt-1 font-semibold text-slate-800">
                        {{ order.shipping_method?.title ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-400">کد رهگیری پستی</div>
                    <div dir="ltr" class="mt-1 font-semibold text-slate-800">
                        {{ order.tracking_code || '—' }}
                    </div>
                </div>

            </div>

        </div>


        <!-- گیرنده و آدرس -->

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <h3 class="mb-5 font-bold text-slate-800">
                گیرنده و آدرس ارسال
            </h3>

            <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">

                <div>
                    <div class="text-xs text-slate-400">نام گیرنده</div>
                    <div class="mt-1 font-semibold text-slate-800">
                        {{ order.receiver_name }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-400">موبایل گیرنده</div>
                    <div dir="ltr" class="mt-1 font-semibold text-slate-800">
                        {{ order.receiver_mobile }}
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <div class="text-xs text-slate-400">آدرس</div>
                    <div class="mt-1 leading-7 text-slate-800">
                        {{ order.shipping_address }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-400">کد پستی</div>
                    <div dir="ltr" class="mt-1 font-semibold text-slate-800">
                        {{ order.postal_code || '—' }}
                    </div>
                </div>

            </div>

            <div
                v-if="order.description"
                class="mt-5 rounded-xl bg-slate-50 p-4 text-sm text-slate-600"
            >
                <span class="block text-xs font-semibold text-slate-400">توضیحات مشتری</span>
                <p class="mt-1 leading-7">{{ order.description }}</p>
            </div>

        </div>


        <!-- اقلام سفارش -->

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">

                <h3 class="font-bold text-slate-800">
                    اقلام سفارش
                </h3>

                <span class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ formatNumber(itemsCount) }} عدد
                </span>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-right text-sm">

                    <thead class="bg-slate-50 text-xs text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">محصول</th>
                        <th class="px-5 py-3 font-semibold">SKU</th>
                        <th class="px-5 py-3 font-semibold">قیمت واحد</th>
                        <th class="px-5 py-3 text-center font-semibold">تعداد</th>
                        <th class="px-5 py-3 font-semibold">جمع</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                    <tr
                        v-for="item in items"
                        :key="item.id"
                    >

                        <td class="px-5 py-3 font-medium text-slate-800">
                            {{ item.product_name }}
                        </td>

                        <td dir="ltr" class="px-5 py-3 text-xs text-slate-500">
                            {{ item.product_sku }}
                        </td>

                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">
                                {{ formatAmount(itemUnitPrice(item)) }}
                            </div>
                            <div
                                v-if="itemHasSale(item)"
                                class="text-xs text-slate-400 line-through"
                            >
                                {{ formatAmount(item.unit_price) }}
                            </div>
                        </td>

                        <td class="px-5 py-3 text-center font-semibold text-slate-800">
                            {{ formatNumber(item.quantity) }}
                        </td>

                        <td class="px-5 py-3 font-bold text-slate-800">
                            {{ formatAmount(item.total_price) }}
                        </td>

                    </tr>

                    <tr v-if="!items.length">
                        <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-400">
                            این سفارش قلمی ندارد.
                        </td>
                    </tr>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- جمع‌بندی مبالغ -->

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <h3 class="mb-5 font-bold text-slate-800">
                جمع‌بندی مبالغ
            </h3>

            <div class="space-y-3 text-sm">

                <div class="flex items-center justify-between">
                    <span class="text-slate-500">جمع اقلام</span>
                    <span class="font-medium text-slate-800">{{ formatAmount(order.subtotal) }}</span>
                </div>

                <div
                    v-if="hasDiscount"
                    class="flex items-center justify-between"
                >
                    <span class="text-emerald-600">
                        تخفیف
                        <span
                            v-if="order.discount_code"
                            dir="ltr"
                            class="mr-1 text-xs"
                        >
                            ({{ order.discount_code.code }})
                        </span>
                    </span>
                    <span class="font-medium text-emerald-600">
                        − {{ formatAmount(order.discount_amount) }}
                    </span>
                </div>

                <div
                    v-if="hasShipping"
                    class="flex items-center justify-between"
                >
                    <span class="text-slate-500">هزینه ارسال</span>
                    <span class="font-medium text-slate-800">{{ formatAmount(order.shipping_cost) }}</span>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                    <span class="font-bold text-slate-700">مبلغ نهایی</span>
                    <span class="text-lg font-bold text-teal-700">{{ formatAmount(order.total_amount) }}</span>
                </div>

            </div>

        </div>


        <!-- یادداشت داخلی -->

        <div
            v-if="order.admin_note"
            class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800"
        >
            <span class="block text-xs font-semibold text-amber-600">یادداشت داخلی</span>
            <p class="mt-1 leading-7">{{ order.admin_note }}</p>
        </div>

    </div>

</template>