<script setup>

import {
    BedDouble,
} from 'lucide-vue-next'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    unitStatus: {
        type: Object,
        default: () => ({
            available: 0,
            reserved: 0,
            in_use: 0,
            cleaning: 0,
            total: 0,
        }),
    },

    loading: {
        type: Boolean,
        default: false,
    },

})


/*
|--------------------------------------------------------------------------
| Rooms
|--------------------------------------------------------------------------
*/

const rooms = [
    {
        key: 'available',
        title: 'اتاق‌های آزاد',
        class: 'bg-emerald-50 text-emerald-600',
        alwaysShow: true,
    },
    {
        key: 'reserved',
        title: 'رزرو شده',
        class: 'bg-blue-50 text-blue-600',
        alwaysShow: true,
    },
    {
        key: 'in_use',
        title: 'در حال استفاده',
        class: 'bg-amber-50 text-amber-600',
        alwaysShow: true,
    },
    {
        key: 'cleaning',
        title: 'در حال نظافت',
        class: 'bg-violet-50 text-violet-600',
        alwaysShow: true,
    },
    {
        key: 'maintenance',
        title: 'تعمیرات',
        class: 'bg-red-50 text-red-600',
        alwaysShow: false,
    },
    {
        key: 'inactive',
        title: 'غیرفعال',
        class: 'bg-slate-100 text-slate-600',
        alwaysShow: false,
    },
]
/*
|--------------------------------------------------------------------------
| Get Value
|--------------------------------------------------------------------------
*/

function getValue(key)
{
    return Number(
        props.unitStatus?.[key] ?? 0
    ).toLocaleString('fa-IR')
}
</script>
<template>
    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >
        <!-- Header -->
        <div
            class="flex items-center justify-between"
        >

            <div>
                <h2
                    class="text-base font-bold text-slate-800"
                >
                    وضعیت اتاق‌ها
                </h2>
                <p
                    class="mt-1 text-xs text-slate-400"
                >
                    وضعیت فعلی اتاق‌های هتل
                </p>
            </div>
            <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600"
            >

                <BedDouble :size="19"/>
            </div>
        </div>
        <div
            v-if="loading"
            class="mt-5 flex h-32 items-center justify-center text-sm text-slate-400"
        >
            <i
                class="fa-solid fa-spinner fa-spin ml-2 text-teal-600"
            ></i>
            در حال بارگذاری...
        </div>
        <div
            v-else
            class="mt-5 grid grid-cols-2 gap-3"
        >
            <template
                v-for="room in rooms"
                :key="room.key"
            >
                <div
                    v-if="
                        room.alwaysShow ||
                        Number(unitStatus?.[room.key] ?? 0) > 0
                    "
                    class="rounded-xl border border-slate-100 p-4"
                >
                    <div
                        class="flex items-center justify-between gap-2"
                    >
                        <span
                            class="text-xs text-slate-500"
                        >
                            {{ room.title }}
                        </span>
                        <span
                            class="rounded-lg px-2 py-1 text-xs font-bold"
                            :class="room.class"
                        >
                            {{ getValue(room.key) }}
                        </span>
                    </div>
                </div>
            </template>
        </div>
        <!-- Total -->
        <div
            v-if="!loading"
            class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4"
        >
            <span
                class="text-xs text-slate-400"
            >
                مجموع واحدها
            </span>
            <span
                class="text-sm font-bold text-slate-700"
            >
                {{ getValue('total') }}
            </span>
        </div>
    </div>
</template>