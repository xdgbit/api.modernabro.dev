<script setup>

import {
    computed,
    onMounted,
    ref,
} from 'vue'

import axios from 'axios'

import RolePanel from './RolePanel.vue'
import {
    KeyRound,
    Pencil,
    Plus,
    Search,
    ShieldCheck,
    Trash2,
    Users,
} from 'lucide-vue-next'


const roles = ref([])
const rolesLoading = ref(false)
const searchText = ref('')

const MAX_VISIBLE_PERMISSIONS = 4

const cardAccents = [
    { bg: 'bg-teal-50', text: 'text-teal-600', ring: 'ring-teal-100' },
    { bg: 'bg-violet-50', text: 'text-violet-600', ring: 'ring-violet-100' },
    { bg: 'bg-amber-50', text: 'text-amber-600', ring: 'ring-amber-100' },
    { bg: 'bg-sky-50', text: 'text-sky-600', ring: 'ring-sky-100' },
    { bg: 'bg-rose-50', text: 'text-rose-600', ring: 'ring-rose-100' },
]


const filteredRoles = computed(() =>
{
    const q = searchText.value.trim().toLowerCase()

    if (!q) return roles.value

    return roles.value.filter(role =>
        role.name?.toLowerCase().includes(q) ||
        role.slug?.toLowerCase().includes(q) ||
        role.description?.toLowerCase().includes(q)
    )
})


const stats = computed(() =>
{
    const uniquePermissions = new Set()

    roles.value.forEach(role =>
    {
        (role.permissions ?? []).forEach(p => uniquePermissions.add(p.id ?? p.name))
    })

    return {
        totalRoles: roles.value.length,
        activeRoles: roles.value.filter(r => r.is_active).length,
        totalPermissions: uniquePermissions.size,
        assignedUsers: roles.value.reduce((sum, r) => sum + (r.users_count ?? 0), 0),
    }
})


async function loadRoles()
{
    rolesLoading.value = true

    try
    {
        const response = await axios.get(Routes.admin.expert.roles)
        console.log('sadsadsad',response.data)
        if (response.data.success)
        {
            roles.value = response.data.data.roles ?? []
        }
    } catch (error)
    {
        console.error('خطا در دریافت نقش‌ها:', error)
    } finally
    {
        rolesLoading.value = false
    }
}


function open_role_panel(role = null)
{
    window.JsPanelV4({
        component: RolePanel,
        modal: false,
        reload: true,
        variableName: 'jsPanelVariableRole',
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'درحال بارگذاری ...',
        headerTitle: role ? 'ویرایش نقش' : 'نقش جدید',
        ajaxData: {},

        props: {
            roleId: role?.id ?? null,

            jsPanelVariableName: 'jsPanelVariableRole',

            onSaved: () =>
            {
                loadRoles()
            },
        },

        onclose_function: false,
        just_one_panel: true,
        container: 'window',
    })
}


async function delete_role(role)
{
    if (role.users_count > 0)
    {
        window.alert('این نقش به کاربر یا کاربرانی اختصاص دارد و قابل حذف نیست.')
        return
    }

    if (!window.confirm(`نقش «${role.name}» حذف شود؟`)) return

    try
    {
        await axios.post(Routes.admin.role.delete, { id: role.id })
        await loadRoles()
    } catch (error)
    {
        console.error('خطا در حذف نقش:', error)
    }
}


function accent_for(index)
{
    return cardAccents[index % cardAccents.length]
}


onMounted(() =>
{
    loadRoles()
})

</script>


<template>

    <div class="space-y-5">


        <!-- Header -->

        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >

            <div>

                <h1
                    class="text-xl font-bold tracking-tight text-slate-800 lg:text-2xl"
                >
                    مدیریت نقش‌ها و دسترسی‌ها
                </h1>

                <p
                    class="mt-1 text-sm text-slate-400"
                >
                    تعریف نقش‌ها، تخصیص دسترسی‌ها و مشاهده کاربران هر نقش
                </p>

            </div>


            <button
                type="button"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700"
                @click="open_role_panel()"
            >

                <Plus :size="17"/>

                نقش جدید

            </button>

        </div>


        <!-- Stats -->

        <div
            class="grid grid-cols-2 gap-4 lg:grid-cols-4"
        >

            <div
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div class="text-xs text-slate-400">کل نقش‌ها</div>
                <strong class="mt-1 block text-xl font-bold text-slate-800">
                    {{ stats.totalRoles }}
                </strong>
            </div>


            <div
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div class="text-xs text-slate-400">نقش‌های فعال</div>
                <strong class="mt-1 block text-xl font-bold text-emerald-600">
                    {{ stats.activeRoles }}
                </strong>
            </div>


            <div
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div class="text-xs text-slate-400">کل دسترسی‌ها</div>
                <strong class="mt-1 block text-xl font-bold text-violet-600">
                    {{ stats.totalPermissions }}
                </strong>
            </div>


            <div
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div class="text-xs text-slate-400">کاربران دارای نقش</div>
                <strong class="mt-1 block text-xl font-bold text-teal-600">
                    {{ stats.assignedUsers }}
                </strong>
            </div>

        </div>


        <!-- Toolbar -->

        <div
            class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"
        >

            <h2 class="text-sm font-bold text-slate-800">
                فهرست نقش‌ها
            </h2>

            <div class="relative w-full sm:w-72">

                <Search
                    :size="16"
                    class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"
                />

                <input
                    v-model="searchText"
                    type="text"
                    placeholder="جستجوی نقش..."
                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pr-9 pl-3 text-sm text-slate-700 outline-none transition focus:border-teal-400 focus:bg-white"
                />

            </div>

        </div>


        <!-- Loading -->

        <div
            v-if="rolesLoading"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"
        >
            <div
                v-for="n in 3"
                :key="n"
                class="h-56 animate-pulse rounded-2xl border border-slate-200 bg-white"
            />
        </div>


        <!-- Empty -->

        <div
            v-else-if="filteredRoles.length === 0"
            class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center"
        >
            <ShieldCheck :size="36" class="text-slate-300"/>
            <p class="mt-3 text-sm font-semibold text-slate-600">
                نقشی یافت نشد
            </p>
            <p class="mt-1 text-xs text-slate-400">
                برای شروع یک نقش جدید تعریف کنید
            </p>
        </div>


        <!-- Role Cards -->

        <div
            v-else
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"
        >

            <div
                v-for="(role, index) in filteredRoles"
                :key="role.id"
                class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md"
            >

                <!-- Card Header -->

                <div class="flex items-start justify-between gap-3">

                    <div class="flex items-center gap-3">

                        <div
                            :class="[
                                accent_for(index).bg,
                                accent_for(index).text,
                                accent_for(index).ring,
                            ]"
                            class="flex h-11 w-11 items-center justify-center rounded-xl ring-4"
                        >
                            <ShieldCheck :size="20"/>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-slate-800">
                                {{ role.name }}
                            </h3>
                            <p class="mt-0.5 font-mono text-[11px] text-slate-400" dir="ltr">
                                {{ role.slug }}
                            </p>
                        </div>

                    </div>


                    <span
                        :class="role.is_active
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-slate-100 text-slate-500'"
                        class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                    >
                        {{ role.is_active ? 'فعال' : 'غیرفعال' }}
                    </span>

                </div>


                <!-- Description -->

                <p class="mt-4 min-h-[2.5rem] text-xs leading-6 text-slate-500">
                    {{ role.description || 'بدون توضیح' }}
                </p>


                <!-- Permissions -->

                <div class="mt-4">

                    <div class="mb-2 flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                        <KeyRound :size="13"/>
                        دسترسی‌ها ({{ role.permissions?.length ?? 0 }})
                    </div>

                    <div class="flex flex-wrap gap-1.5">

                        <span
                            v-for="perm in (role.permissions ?? []).slice(0, MAX_VISIBLE_PERMISSIONS)"
                            :key="perm.id ?? perm.name"
                            class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] text-slate-600"
                        >
                            {{ perm.label ?? perm.name }}
                        </span>

                        <span
                            v-if="(role.permissions?.length ?? 0) > MAX_VISIBLE_PERMISSIONS"
                            class="rounded-lg bg-teal-50 px-2 py-1 text-[11px] font-semibold text-teal-700"
                        >
                            +{{ role.permissions.length - MAX_VISIBLE_PERMISSIONS }}
                        </span>

                        <span
                            v-if="!role.permissions?.length"
                            class="text-[11px] text-slate-400"
                        >
                            دسترسی‌ای تعریف نشده
                        </span>

                    </div>

                </div>


                <!-- Footer -->

                <div
                    class="mt-auto flex items-center justify-between border-t border-slate-100 pt-4 mt-5"
                >

                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <Users :size="14"/>
                        {{ role.users_count ?? 0 }} کاربر
                    </div>


                    <div class="flex items-center gap-1">

                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-teal-50 hover:text-teal-600"
                            title="ویرایش"
                            @click="open_role_panel(role)"
                        >
                            <Pencil :size="15"/>
                        </button>

                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-rose-50 hover:text-rose-600"
                            title="حذف"
                            @click="delete_role(role)"
                        >
                            <Trash2 :size="15"/>
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

</template>