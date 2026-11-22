<script setup>
import { useRouter } from 'vue-router'

import {
    Bell,
    ChevronDown,
    LogOut,
    LockKeyhole,
} from 'lucide-vue-next'

import {
    ref,
    onMounted,
    onBeforeUnmount,
} from 'vue'

const router = useRouter()
const profileMenuRef = ref(null)
const showProfileMenu = ref(false)

const userExpert = window.authUserExpert ?? null
const isSuperAdmin = userExpert?.is_super_admin === true

const fullName = [
    userExpert?.first_name,
    userExpert?.last_name,
].filter(Boolean).join(' ') || 'مدیر سیستم'

const UserName = userExpert?.username || 'کارشناس'

const handleClickOutside = (event) => {
    if (!profileMenuRef.value) {
        return
    }

    if (!profileMenuRef.value.contains(event.target)) {
        showProfileMenu.value = false
    }
}

const changePassword = () => {
    showProfileMenu.value = false

    router.push({
        name: 'admin.change-password',
    })
}

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', handleClickOutside)
})

const toggleProfileMenu = () => {
    showProfileMenu.value = !showProfileMenu.value
}

const logout = async () => {
    try {
        await axios.post(Routes.auth.admin.logout)

        window.location.href = Routes.auth.admin.login.index
    } catch (error) {
        console.error(error)
    }
}
</script>

<template>
    <header class="border-b border-[#b4b9c1] bg-gradient-to-l from-[#dfe2e6] via-[#cfd3d9] to-[#c3c8cf] text-slate-800 shadow-md">
        <div class="mx-auto flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">

            <!-- Brand -->
            <div class="flex items-center gap-3">
                <img
                    :src="'/images/logo.png'"
                    alt="لوگوی سامانه"
                    class="h-12 w-12 shrink-0 object-contain"
                />

                <div>
                    <div class="text-sm font-bold text-slate-800">
                        سامانه مدیریت فروشگاه
                    </div>

                    <div class="mt-0.5 text-[11px] text-slate-600">
                        پنل مدیریت
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">

                <!-- Notifications -->
                <button
                    type="button"
                    aria-label="اعلان‌ها"
                    class="relative flex h-10 w-10 items-center justify-center rounded-xl text-slate-700 transition hover:bg-[#bcc1c9] hover:text-slate-900"
                >
                    <Bell :size="19" />

                    <span
                        class="absolute right-2 top-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-[#cfd3d9]"
                    />
                </button>

                <div class="mx-2 h-7 w-px bg-[#9ea4ad]" />

                <!-- Profile -->
                <div
                    ref="profileMenuRef"
                    class="relative"
                >
                    <button
                        type="button"
                        aria-label="منوی کاربر"
                        class="flex cursor-pointer items-center gap-3 rounded-xl px-2 py-1.5 transition hover:bg-[#bcc1c9]"
                        @click="toggleProfileMenu"
                    >
                        <!-- User Info -->
                        <div class="hidden text-right sm:block">
                            <div class="text-xs font-semibold text-slate-800">
                                {{ fullName }}
                            </div>
                        </div>

                        <!-- Avatar -->
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#e9ebee] text-sm font-bold text-slate-700 ring-1 ring-[#8f959e]"
                        >
                            {{ fullName.charAt(0) }}
                        </div>

                        <ChevronDown
                            :size="16"
                            :class="[
                                'text-slate-600 transition-transform duration-200',
                                showProfileMenu ? 'rotate-180' : '',
                            ]"
                        />
                    </button>

                    <!-- Dropdown -->
                    <div
                        v-if="showProfileMenu"
                        class="absolute left-0 top-full z-50 mt-2 w-56 overflow-hidden rounded-xl border border-[#d5d9de] bg-[#faf8f5] py-1 text-stone-700 shadow-xl"
                    >
                        <!-- User Header -->
                        <div class="border-b border-[#ebe6df] px-4 py-3">
                            <div class="text-sm font-semibold text-stone-800">
                                {{ fullName }}
                            </div>

                            <div class="mt-1 text-xs text-stone-500">
                                {{ UserName }}
                            </div>
                        </div>

                        <!-- Change Password -->
                        <button
                            v-if="isSuperAdmin"
                            type="button"
                            class="flex w-full cursor-pointer items-center gap-3 px-4 py-3 text-right text-sm text-stone-700 transition hover:bg-[#f0ebe4]"
                            @click="changePassword"
                        >
                            <LockKeyhole :size="17" />
                            <span>
                                تغییر رمز عبور
                            </span>
                        </button>

                        <div class="my-1 border-t border-[#ebe6df]" />

                        <!-- Logout -->
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center gap-3 px-4 py-3 text-right text-sm text-red-600 transition hover:bg-red-50"
                            @click="logout"
                        >
                            <LogOut :size="17" />

                            <span>
                                خروج از حساب
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>