<script setup>
import { ref, computed } from 'vue'

import ProductSeoTab from './tabs/ProductSeoTab.vue'
import ProductInfoTab from './tabs/ProductInfoTab.vue'
import ProductImagesTab from './tabs/ProductImagesTab.vue'

const props = defineProps({
    productId: {
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

const activeTab = ref('info')
const currentProductId = ref(props.productId)
const productTitle = ref('')

// اگر تغییری ذخیره شده باشد، بعد از بسته شدن پنل لیست رفرش می‌شود
const hasChanges = ref(false)

const isEdit = computed(() => !!currentProductId.value)

const tabs = [
    { key: 'info', title: 'اطلاعات محصول' },
    { key: 'images', title: 'عکس‌ها' },
    { key: 'seo', title: 'سئو' },
]

function isTabDisabled(key)
{
    return !currentProductId.value && key !== 'info'
}

// بعد از لود اولیه: فقط عنوان هدر پر می‌شود
function onProductLoaded(data)
{
    productTitle.value = data.title
}

// بعد از ذخیره اطلاعات: شناسه برای تب‌های دیگر ست می‌شود
function onProductSaved(data)
{
    currentProductId.value = data.id
    productTitle.value = data.title
    hasChanges.value = true
}

function closePanel()
{
    if (hasChanges.value)
    {
        props.onSaved?.()
        emit('saved')
    }

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
                            ? 'مشاهده محصول'
                            : isEdit
                                ? 'ویرایش محصول'
                                : 'محصول جدید'
                    }}
                </h2>

                <template v-if="productTitle">
                    <span class="text-slate-300">|</span>
                    <p class="truncate text-sm font-medium text-slate-500">
                        {{ productTitle }}
                    </p>
                </template>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-slate-200 bg-white px-6">
            <div class="flex gap-6">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    class="relative py-4 text-sm font-medium transition"
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
        <div class="flex-1 overflow-auto px-4 pt-4">
            <ProductInfoTab
                v-if="activeTab === 'info'"
                :product-id="currentProductId"
                :read-only="readOnly"
                @saved="onProductSaved"
                @loaded="onProductLoaded"
                @close="closePanel"
            />

            <ProductImagesTab
                v-else-if="activeTab === 'images'"
                :product-id="currentProductId"
                :read-only="readOnly"
                @saved="hasChanges = true"
            />

            <ProductSeoTab
                v-else-if="activeTab === 'seo'"
                :product-id="currentProductId"
                :read-only="readOnly"
                @saved="hasChanges = true"
            />
        </div>
    </div>
</template>