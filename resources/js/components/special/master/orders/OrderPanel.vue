<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import OrderDetailsTab from './tabs/OrderDetailsTab.vue'
import OrderExpertActionTab from './tabs/OrderExpertActionTab.vue'

/*
|--------------------------------------------------------------------------
| Props & Emits
|--------------------------------------------------------------------------
*/

const props = defineProps({
    orderId: {
        type: [Number, String],
        required: true,
    },

    readOnly: {
        type: Boolean,
        default: false,
    },

    initialTab: {
        type: String,
        default: 'details',
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

const emit = defineEmits(['saved', 'close'])


/*
|--------------------------------------------------------------------------
| Tabs
|--------------------------------------------------------------------------
*/

const tabs = [
    { key: 'details', title: 'جزئیات سفارش' },
    { key: 'action', title: 'اقدام کارشناس' },
]

const activeTab = ref(props.initialTab)


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading = ref(false)
const error = ref(null)
const order = ref(null)

const orderCode = computed(() => order.value?.code ?? '')

const orderStatusLabel = computed(() => order.value?.status_label ?? '')

// تب اقدام کارشناس فقط وقتی معنی دارد که سفارش قابل تغییر باشد
const canTakeAction = computed(() =>
    !props.readOnly &&
    !!order.value &&
    !['delivered', 'cancelled', 'returned'].includes(order.value.status)
)


/*
|--------------------------------------------------------------------------
| Load Order
|--------------------------------------------------------------------------
*/

async function getData({ silent = false } = {})
{
    if (!silent)
    {
        loading.value = true
    }

    error.value = null

    try
    {
        const response = await axios.post(
            Routes.orders.jspanel,
            {
                order_id: props.orderId,
            }
        )

        order.value = response.data.data?.order ?? null
    }
    catch (err)
    {
        console.error('خطا در دریافت اطلاعات سفارش:', err)

        error.value =
            err?.response?.data?.message ??
            'خطا در دریافت اطلاعات سفارش.'
    }
    finally
    {
        loading.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Child Events
|--------------------------------------------------------------------------
*/

// بعد از تغییر وضعیت، اطلاعات سفارش بدون لودینگ کامل دوباره خوانده می‌شود
async function onStatusChanged(data)
{
    await getData({ silent: true })

    props.onSaved?.(data)
    emit('saved', data)
}


/*
|--------------------------------------------------------------------------
| Watch
|--------------------------------------------------------------------------
*/

// اگر تب اقدام کارشناس دیگر قابل استفاده نیست، به تب جزئیات برگرد
watch(canTakeAction, (can) =>
{
    if (!can && activeTab.value === 'action')
    {
        activeTab.value = 'details'
    }
})


/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

function closePanel()
{
    window[props.jsPanelVariableName]?.close()

    emit('close')
}


/*
|--------------------------------------------------------------------------
| Mount
|--------------------------------------------------------------------------
*/

onMounted(() =>
{
    getData()
})
</script>


<template>

    <div
        class="flex h-full flex-col bg-slate-50"
        dir="rtl"
    >

        <!-- Header -->

        <div class="border-b border-slate-200 bg-white px-6 py-4">

            <div class="flex min-w-0 items-center justify-between gap-3">

                <div class="flex min-w-0 items-center gap-3">

                    <h2 class="shrink-0 text-lg font-bold text-slate-800">
                        {{ readOnly ? 'مشاهده سفارش' : 'جزئیات سفارش' }}
                    </h2>

                    <template v-if="orderCode">
                        <span class="text-slate-300">|</span>
                        <span dir="ltr" class="truncate text-sm font-semibold text-slate-500">
                            {{ orderCode }}
                        </span>
                    </template>

                </div>

                <span
                    v-if="orderStatusLabel"
                    class="shrink-0 rounded-lg bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"
                >
                    {{ orderStatusLabel }}
                </span>

            </div>

        </div>


        <!-- Tabs -->

        <div class="border-b border-slate-200 bg-white px-6">

            <div class="flex gap-6">

                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    class="relative py-4 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-40"
                    :class="
                        activeTab === tab.key
                            ? 'text-teal-600'
                            : 'text-slate-400 hover:text-slate-700'
                    "
                    :disabled="tab.key === 'action' && !canTakeAction"
                    @click="activeTab = tab.key"
                >

                    {{ tab.title }}

                    <span
                        v-if="activeTab === tab.key"
                        class="absolute inset-x-0 bottom-0 h-0.5 rounded-full bg-teal-500"
                    ></span>

                </button>

            </div>

        </div>


        <!-- Content -->

        <div class="flex-1 overflow-auto px-4 pt-4 pb-4">

            <!-- Loading -->

            <div
                v-if="loading"
                class="flex min-h-[300px] items-center justify-center"
            >
                <i class="fa-solid fa-spinner fa-spin ml-2 text-xl text-teal-600"></i>
                <span class="text-sm text-slate-500">
                    در حال بارگذاری...
                </span>
            </div>

            <!-- Error -->

            <div
                v-else-if="error"
                class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
            >
                {{ error }}
            </div>

            <template v-else-if="order">

                <OrderDetailsTab
                    v-if="activeTab === 'details'"
                    :order="order"
                />

                <OrderExpertActionTab
                    v-else-if="activeTab === 'action' && canTakeAction"
                    :order-id="orderId"
                    :order="order"
                    @saved="onStatusChanged"
                    @close="closePanel"
                />

            </template>

        </div>

    </div>

</template>