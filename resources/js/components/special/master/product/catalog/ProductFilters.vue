<script setup>
import { reactive } from 'vue'
import {
    Search,
    RotateCcw,
    SlidersHorizontal,
    ChevronDown,
} from 'lucide-vue-next'

const emit = defineEmits(['apply'])

const filters = reactive({
    sku: '',            // کد محصول (SKU)
    name: '',           // نام محصول
    brand: '',          // برند
    category: '',      // دسته‌بندی
    status: '',         // وضعیت انتشار
    stock_status: '',   // وضعیت موجودی
    price_from: '',     // حداقل قیمت
    price_to: '',       // حداکثر قیمت
})

const statuses = [
    { value: 'active', label: 'فعال' },
    { value: 'inactive', label: 'غیرفعال' },
]

const stockStatuses = [
    { value: 'in_stock', label: 'موجود' },
    { value: 'low_stock', label: 'کم‌موجودی' },
    { value: 'out_of_stock', label: 'ناموجود' },
]

function applyFilters() {
    const data = {}

    Object.entries(filters).forEach(([key, value]) => {
        const normalized = String(value ?? '').trim()

        if (normalized !== '') {
            data[key] = normalized
        }
    })

    emit('apply', data)
}

function resetFilters() {
    Object.keys(filters).forEach(key => {
        filters[key] = ''
    })

    emit('apply', {})
}
</script>

<template>
    <details class="rounded-2xl border border-slate-200 bg-white shadow-sm group mb-2 mt-1 mx-1">
        <summary class="flex cursor-pointer list-none items-center justify-between px-3 py-3 transition hover:bg-slate-50">
            <div class="flex items-center gap-3">
                <SlidersHorizontal :size="18" class="text-teal-600" />
                <div>
                    <h3 class="text-sm font-bold text-slate-800">
                        فیلتر و جستجوی محصولات
                    </h3>
                </div>
            </div>
            <ChevronDown
                :size="20"
                class="text-slate-500 transition-transform duration-200 group-open:rotate-180"
            />
        </summary>

        <form @submit.prevent="applyFilters">
            <div class="grid grid-cols-1 gap-4 border-t border-slate-100 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div>
                    <label class="mb-2 block text-xs font-semibold text-slate-600">
                        کد محصول (SKU)
                    </label>
                    <input
                        v-model="filters.sku"
                        type="text"
                        dir="ltr"
                        placeholder="SKU..."
                        class="filter-input"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold text-slate-600">
                        نام محصول
                    </label>
                    <input
                        v-model="filters.name"
                        type="text"
                        placeholder="نام محصول..."
                        class="filter-input"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold text-slate-600">
                        وضعیت انتشار
                    </label>
                    <select v-model="filters.status" class="filter-input">
                        <option value="">همه وضعیت‌ها</option>
                        <option
                            v-for="status in statuses"
                            :key="status.value"
                            :value="status.value"
                        >
                            {{ status.label }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold text-slate-600">
                        وضعیت موجودی
                    </label>
                    <select v-model="filters.stock_status" class="filter-input">
                        <option value="">همه موجودی‌ها</option>
                        <option
                            v-for="stock in stockStatuses"
                            :key="stock.value"
                            :value="stock.value"
                        >
                            {{ stock.label }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold text-slate-600">
                        برند
                    </label>
                    <input
                        v-model="filters.brand"
                        type="text"
                        placeholder="نام برند..."
                        class="filter-input"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold text-slate-600">
                        دسته‌بندی
                    </label>
                    <input
                        v-model="filters.category"
                        type="text"
                        placeholder="نام دسته‌بندی..."
                        class="filter-input"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold text-slate-600">
                        قیمت از (تومان)
                    </label>
                    <input
                        v-model="filters.price_from"
                        type="number"
                        min="0"
                        dir="ltr"
                        placeholder="0"
                        class="filter-input"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold text-slate-600">
                        قیمت تا (تومان)
                    </label>
                    <input
                        v-model="filters.price_to"
                        type="number"
                        min="0"
                        dir="ltr"
                        placeholder="0"
                        class="filter-input"
                    />
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-3">
                <button
                    type="button"
                    @click="resetFilters"
                    class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
                >
                    <RotateCcw :size="15" />
                    پاک کردن فیلترها
                </button>

                <button
                    type="submit"
                    class="inline-flex h-10 items-center gap-2 rounded-xl bg-teal-600 px-5 text-xs font-bold text-white transition hover:bg-teal-700"
                >
                    <Search :size="15" />
                    اعمال فیلتر
                </button>
            </div>
        </form>
    </details>
</template>

<style scoped>
.filter-input {
    width: 100%;
    height: 40px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: white;
    padding: 0 12px;
    font-size: 12px;
    color: #334155;
    outline: none;
    transition: border-color .2s;
}

.filter-input:focus {
    border-color: #0d9488;
}

.filter-input::placeholder {
    color: #94a3b8;
}

summary::-webkit-details-marker {
    display: none;
}
</style>