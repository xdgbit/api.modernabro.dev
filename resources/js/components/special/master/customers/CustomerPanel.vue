<script setup>
import { ref, computed } from 'vue'
import CustomerInfoTab from './tabs/CustomerInfoTab.vue'
import CustomerOrdersTab from './tabs/CustomerOrdersTab.vue'
import CustomerPaymentsTab from './tabs/CustomerPaymentsTab.vue'
import CustomerAddressesTab from './tabs/CustomerAddressesTab.vue'

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
    { key: 'info', title: 'اطلاعات مشتری' },
    { key: 'addresses', title: 'آدرس‌ها' },
    { key: 'orders', title: 'سفارشات' },
    { key: 'payments', title: 'پرداخت‌ها' },
]

const activeTab = ref('info')


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

// بعد از ساخت مشتری جدید، شناسه از پاسخ سرور گرفته می‌شود
const currentCustomerId = ref(props.customerId)
const customerName = ref('')

const isEdit = computed(() => !!currentCustomerId.value)

// تب‌های جانبی تا قبل از وجود مشتری در دیتابیس غیرفعال‌اند
function isTabDisabled(key)
{
    return !currentCustomerId.value && key !== 'info'
}


/*
|--------------------------------------------------------------------------
| Child Events
|--------------------------------------------------------------------------
*/

function formatName(customer)
{
    return [customer?.first_name, customer?.last_name]
    .filter(Boolean)
    .join(' ')
}

function onCustomerLoaded(customer)
{
    customerName.value = formatName(customer)
}

function onCustomerSaved(customer)
{
    currentCustomerId.value = customer?.id ?? currentCustomerId.value
    customerName.value = formatName(customer) || customerName.value

    props.onSaved?.(customer)
    emit('saved', customer)
}


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
</script>


<template>

    <div
        class="flex h-full flex-col bg-slate-50"
        dir="rtl"
    >

        <!-- Header -->

        <div class="border-b border-slate-200 bg-white px-6 py-4">

            <div class="flex min-w-0 items-center gap-3">

                <h2 class="shrink-0 text-lg font-bold text-slate-800">
                    {{
                        readOnly
                            ? 'مشاهده مشتری'
                            : isEdit
                                ? 'ویرایش مشتری'
                                : 'مشتری جدید'
                    }}
                </h2>

                <template v-if="customerName">
                    <span class="text-slate-300">|</span>
                    <p class="truncate text-sm font-medium text-slate-500">
                        {{ customerName }}
                    </p>
                </template>

            </div>

        </div>


        <!-- Tabs -->

        <div class="border-b border-slate-200 bg-white px-6">

            <div class="flex gap-6 overflow-x-auto">

                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    class="relative shrink-0 py-4 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-40"
                    :class="
                        activeTab === tab.key
                            ? 'text-teal-600'
                            : 'text-slate-400 hover:text-slate-700'
                    "
                    :disabled="isTabDisabled(tab.key)"
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

        <div class="flex-1 overflow-auto px-4 pb-4 pt-4">

            <CustomerInfoTab
                v-if="activeTab === 'info'"
                :customer-id="currentCustomerId"
                :read-only="readOnly"
                @loaded="onCustomerLoaded"
                @saved="onCustomerSaved"
                @close="closePanel"
            />

            <CustomerAddressesTab
                v-else-if="activeTab === 'addresses' && currentCustomerId"
                :customer-id="currentCustomerId"
            />

            <CustomerOrdersTab
                v-else-if="activeTab === 'orders' && currentCustomerId"
                :customer-id="currentCustomerId"
            />

            <CustomerPaymentsTab
                v-else-if="activeTab === 'payments' && currentCustomerId"
                :customer-id="currentCustomerId"
            />

        </div>

    </div>

</template>