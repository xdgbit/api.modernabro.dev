<script setup>
import {ref, onMounted} from 'vue'
import axios from 'axios'
import ContactMessageDatatable from './ContactMessageDatatable.vue'
import {Inbox, Mail, MailOpen, CalendarDays} from 'lucide-vue-next'

const datatable_ref = ref(null)
const statsLoading = ref(false)
const stats = ref({total: 0, unread: 0, read: 0, today: 0})

const cards = [
    {key: 'total',  label: 'کل پیام‌ها',     icon: Inbox,        text: 'text-slate-800', box: 'bg-teal-50 text-teal-600'},
    {key: 'unread', label: 'خوانده‌نشده',    icon: Mail,         text: 'text-red-600',   box: 'bg-red-50 text-red-600'},
    {key: 'read',   label: 'خوانده‌شده',     icon: MailOpen,     text: 'text-green-600', box: 'bg-green-50 text-green-600'},
    {key: 'today',  label: 'پیام‌های امروز', icon: CalendarDays, text: 'text-blue-600',  box: 'bg-blue-50 text-blue-600'},
]

async function loadStats() {
    statsLoading.value = true
    try {
        const response = await axios.get(Routes.contact_message.get_data)
        if (response.data.success) {
            const d = response.data.data ?? {}
            stats.value = {
                total: d.total ?? 0,
                unread: d.unread ?? 0,
                read: d.read ?? 0,
                today: d.today ?? 0,
            }
        }
    } catch (error) {
        console.error('خطا در دریافت آمار پیام‌ها:', error)
    } finally {
        statsLoading.value = false
    }
}

// بعد از هر تغییر (خواندن / حذف) جدول و آمار تازه شوند
function onChanged() {
    datatable_ref.value?.refreshGrid()
    loadStats()
}

onMounted(loadStats)
</script>

<template>
    <div class="space-y-6">

        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800">پیام‌های تماس</h1>
            <p class="mt-1 text-sm text-slate-400">مشاهده و مدیریت پیام‌های ارسال‌شده از فرم  تماس با ما</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="card in cards"
                :key="card.key"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-400">{{ card.label }}</p>

                        <div
                            v-if="statsLoading"
                            class="mt-3 h-8 w-16 animate-pulse rounded-lg bg-slate-100"
                        ></div>
                        <p v-else class="mt-2 text-2xl font-bold" :class="card.text">
                            {{ stats[card.key] }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl"
                        :class="card.box"
                    >
                        <component :is="card.icon" :size="22"/>
                    </div>
                </div>
            </div>
        </div>

        <ContactMessageDatatable ref="datatable_ref" @changed="onChanged"/>
    </div>
</template>