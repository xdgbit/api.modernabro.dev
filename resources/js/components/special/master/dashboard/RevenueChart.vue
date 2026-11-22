<script setup>
import { TrendingUp } from 'lucide-vue-next'

const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور']

const values = [32, 46, 39, 58, 67, 81]

const maxValue = Math.max(...values)

const points = values
.map((value, index) => {
    const x = 20 + index * 55
    const y = 150 - (value / maxValue) * 120

    return `${x},${y}`
})
.join(' ')
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-800">
                    درآمد
                </h2>

                <p class="mt-1 text-xs text-slate-400">
                    روند درآمد شش ماه اخیر
                </p>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                <TrendingUp :size="19" />
            </div>
        </div>

        <div class="mt-5">
            <div class="text-2xl font-bold text-slate-800">
                ۸۴,۵۰۰,۰۰۰
                <span class="text-sm font-normal text-slate-400">
                    تومان
                </span>
            </div>

            <div class="mt-4 h-48 w-full overflow-hidden">
                <svg
                    viewBox="0 0 300 170"
                    class="h-full w-full"
                    preserveAspectRatio="none"
                >
                    <line
                        x1="0"
                        y1="150"
                        x2="300"
                        y2="150"
                        stroke="#e2e8f0"
                        stroke-width="1"
                    />

                    <line
                        x1="0"
                        y1="110"
                        x2="300"
                        y2="110"
                        stroke="#e2e8f0"
                        stroke-width="1"
                    />

                    <line
                        x1="0"
                        y1="70"
                        x2="300"
                        y2="70"
                        stroke="#e2e8f0"
                        stroke-width="1"
                    />

                    <polyline
                        :points="points"
                        fill="none"
                        stroke="#0ea5a4"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>

            <div class="mt-2 flex justify-between">
                <span
                    v-for="month in months"
                    :key="month"
                    class="text-[11px] text-slate-400"
                >
                    {{ month }}
                </span>
            </div>
        </div>
    </div>
</template>