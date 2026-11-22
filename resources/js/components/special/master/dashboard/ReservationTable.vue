<script setup>
import { MoreHorizontal } from 'lucide-vue-next'

const reservations = [
    {
        id: '#RES-1024',
        guest: 'علی محمدی',
        room: 'اتاق 204',
        date: '1405/05/25',
        amount: '12,500,000',
        status: 'تایید شده',
    },
    {
        id: '#RES-1023',
        guest: 'سارا احمدی',
        room: 'سوئیت 301',
        date: '1405/05/25',
        amount: '18,200,000',
        status: 'در انتظار',
    },
    {
        id: '#RES-1022',
        guest: 'رضا کریمی',
        room: 'اتاق 109',
        date: '1405/05/24',
        amount: '9,800,000',
        status: 'تایید شده',
    },
    {
        id: '#RES-1021',
        guest: 'نگار حسینی',
        room: 'اتاق 402',
        date: '1405/05/24',
        amount: '15,600,000',
        status: 'لغو شده',
    },
    {
        id: '#RES-1020',
        guest: 'محمد رضایی',
        room: 'اتاق 203',
        date: '1405/05/23',
        amount: '11,200,000',
        status: 'تایید شده',
    },
]
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <div>
                <h2 class="text-base font-bold text-slate-800">
                    آخرین رزروها
                </h2>

                <p class="mt-1 text-xs text-slate-400">
                    آخرین رزروهای ثبت شده در سیستم
                </p>
            </div>

            <button
                type="button"
                class="text-sm font-medium text-teal-600 transition hover:text-teal-700"
            >
                مشاهده همه
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-right">
                <thead>
                <tr class="border-b border-slate-100 bg-slate-50/70">
                    <th class="px-5 py-3 text-xs font-medium text-slate-400">
                        کد رزرو
                    </th>

                    <th class="px-5 py-3 text-xs font-medium text-slate-400">
                        مهمان
                    </th>

                    <th class="px-5 py-3 text-xs font-medium text-slate-400">
                        اتاق
                    </th>

                    <th class="px-5 py-3 text-xs font-medium text-slate-400">
                        تاریخ
                    </th>

                    <th class="px-5 py-3 text-xs font-medium text-slate-400">
                        مبلغ
                    </th>

                    <th class="px-5 py-3 text-xs font-medium text-slate-400">
                        وضعیت
                    </th>

                    <th class="px-5 py-3" />
                </tr>
                </thead>

                <tbody>
                <tr
                    v-for="reservation in reservations"
                    :key="reservation.id"
                    class="border-b border-slate-100 last:border-0"
                >
                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-700">
                        {{ reservation.id }}
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                        {{ reservation.guest }}
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                        {{ reservation.room }}
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-500">
                        {{ reservation.date }}
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-700">
                        {{ reservation.amount }}
                        <span class="text-xs text-slate-400">
                                تومان
                            </span>
                    </td>

                    <td class="whitespace-nowrap px-5 py-4">
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="{
                                    'bg-emerald-50 text-emerald-600':
                                        reservation.status === 'تایید شده',

                                    'bg-amber-50 text-amber-600':
                                        reservation.status === 'در انتظار',

                                    'bg-red-50 text-red-600':
                                        reservation.status === 'لغو شده',
                                }"
                            >
                                {{ reservation.status }}
                            </span>
                    </td>

                    <td class="px-5 py-4">
                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        >
                            <MoreHorizontal :size="18" />
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>