<script setup>
import {reactive, ref, onMounted} from 'vue'
import {
    Search,
    RotateCcw,
    SlidersHorizontal,
    ChevronDown,
} from 'lucide-vue-next'

const emit = defineEmits(['apply'])

const filters = reactive({
    title: '',          // نام دسته‌بندی
    slug: '',           // نامک (Slug)
    type: '',           // نوع: root | child
    parent_id: '',      // دسته والد (فقط برای زیرمجموعه‌ها)
})

const typeOptions = [
    {value: 'root', label: 'دسته اصلی'},
    {value: 'child', label: 'زیرمجموعه'},
]

// لیست دسته‌های اصلی برای فیلتر والد
const parentOptions = ref([])

async function loadParents()
{
    try
    {
        const response = await axios.post(
            Routes.product.categories.jspanel,
            {category_id: null}
        )

        parentOptions.value = response.data.data?.parents ?? []
    } catch (error)
    {
        console.error('خطا در دریافت دسته‌های والد:', error)
    }
}

function applyFilters()
{
    const data = {}

    Object.entries(filters).forEach(([key, value]) =>
    {
        const normalized = String(value ?? '').trim()

        if (normalized !== '')
        {
            data[key] = normalized
        }
    })

    // اگر نوع «دسته اصلی» انتخاب شده، والد معنی ندارد
    if (data.type === 'root')
    {
        delete data.parent_id
    }

    emit('apply', data)
}

function resetFilters()
{
    Object.keys(filters).forEach(key =>
    {
        filters[key] = ''
    })

    emit('apply', {})
}

onMounted(() =>
{
    loadParents()
})
</script>

<template>
    <details class="rounded-2xl border border-slate-200 bg-white shadow-sm group mb-2 mt-1 mx-1">
        <summary class="flex cursor-pointer list-none items-center justify-between px-3 py-3 transition hover:bg-slate-50">
            <div class="flex items-center gap-3">
                <SlidersHorizontal :size="18" class="text-teal-600"/>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">
                        فیلتر و جستجوی دسته‌بندی‌ها
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
                        نام دسته‌بندی
                    </label>
                    <input
                        v-model="filters.title"
                        type="text"
                        placeholder="نام دسته‌بندی..."
                        class="filter-input"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold text-slate-600">
                        نامک (Slug)
                    </label>
                    <input
                        v-model="filters.slug"
                        type="text"
                        dir="ltr"
                        placeholder="slug..."
                        class="filter-input"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold text-slate-600">
                        نوع دسته
                    </label>
                    <select v-model="filters.type" class="filter-input">
                        <option value="">همه دسته‌ها</option>
                        <option
                            v-for="option in typeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold text-slate-600">
                        دسته والد
                    </label>
                    <select
                        v-model="filters.parent_id"
                        :disabled="filters.type === 'root'"
                        class="filter-input disabled:bg-slate-100"
                    >
                        <option value="">همه والدها</option>
                        <option
                            v-for="parent in parentOptions"
                            :key="parent.id"
                            :value="parent.id"
                        >
                            {{ parent.title }}
                        </option>
                    </select>
                </div>

            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-3">
                <button
                    type="button"
                    @click="resetFilters"
                    class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
                >
                    <RotateCcw :size="15"/>
                    پاک کردن فیلترها
                </button>

                <button
                    type="submit"
                    class="inline-flex h-10 items-center gap-2 rounded-xl bg-teal-600 px-5 text-xs font-bold text-white transition hover:bg-teal-700"
                >
                    <Search :size="15"/>
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