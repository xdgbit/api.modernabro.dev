<script setup>

import {
    computed,
    onMounted,
    ref,
} from 'vue'

import axios from 'axios'

import {
    CheckCircle2,
    KeyRound,
    ShieldCheck,
} from 'lucide-vue-next'


/* ======================================================
   Props
====================================================== */

const props = defineProps({

    roleId: {
        type: [Number, String],
        default: null,
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


/* ======================================================
   State
====================================================== */

const loading = ref(false)
const saving = ref(false)
const permissionsLoading = ref(false)

const errors = ref({})

const permissions = ref([])

const form = ref({

    name: '',
    slug: '',
    description: '',
    is_active: true,

    permission_ids: [],

})


/* ======================================================
   Edit Mode
====================================================== */

const isEdit = ref(
    !!props.roleId
)


/* ======================================================
   Computed
====================================================== */

const groupedPermissions = computed(() =>
{
    const groups = {}

    permissions.value.forEach(perm =>
    {
        const groupName = perm.group || 'سایر'

        if (!groups[groupName])
        {
            groups[groupName] = []
        }

        groups[groupName].push(perm)
    })

    return groups
})


const allSelected = computed(() =>
{
    return (
        permissions.value.length > 0 &&
        form.value.permission_ids.length === permissions.value.length
    )
})


/* ======================================================
   Load Permissions
====================================================== */

async function loadPermissions()
{
    permissionsLoading.value = true

    try
    {
        const response = await axios.get(Routes.admin.role.permissions)

        if (response.data.success)
        {
            permissions.value = response.data.data.permissions ?? []
        }

    } catch (error)
    {
        console.error(
            'خطا در دریافت دسترسی‌ها:',
            error
        )
    } finally
    {
        permissionsLoading.value = false
    }
}


/* ======================================================
   Load Role
====================================================== */

async function loadRole()
{
    if (!props.roleId)
    {
        return
    }

    loading.value = true

    try
    {
        const response = await axios.get(
            Routes.admin.role.show(
                props.roleId
            )
        )

        if (response.data.success)
        {
            const role = response.data.data.role

            form.value = {

                name: role.name ?? '',
                slug: role.slug ?? '',
                description: role.description ?? '',

                is_active: Boolean(
                    Number(role.is_active)
                ),

                permission_ids:
                    (role.permissions ?? []).map(
                        p => p.id
                    ),

            }
        }

    } catch (error)
    {
        console.error(
            'خطا در دریافت اطلاعات نقش:',
            error
        )
    } finally
    {
        loading.value = false
    }
}


/* ======================================================
   Permission Selection
====================================================== */

function isPermissionSelected(id)
{
    return form.value.permission_ids.includes(id)
}


function togglePermission(id)
{
    const index = form.value.permission_ids.indexOf(id)

    if (index === -1)
    {
        form.value.permission_ids.push(id)
    }
    else
    {
        form.value.permission_ids.splice(index, 1)
    }
}


function toggleAll()
{
    if (allSelected.value)
    {
        form.value.permission_ids = []
    }
    else
    {
        form.value.permission_ids = permissions.value.map(
            p => p.id
        )
    }
}


function toggleGroup(groupName)
{
    const ids = groupedPermissions.value[groupName].map(p => p.id)

    const allInGroupSelected = ids.every(
        id => form.value.permission_ids.includes(id)
    )

    if (allInGroupSelected)
    {
        form.value.permission_ids = form.value.permission_ids.filter(
            id => !ids.includes(id)
        )
    }
    else
    {
        const merged = new Set([
            ...form.value.permission_ids,
            ...ids,
        ])

        form.value.permission_ids = [...merged]
    }
}


function isGroupSelected(groupName)
{
    const ids = groupedPermissions.value[groupName].map(p => p.id)

    return ids.every(
        id => form.value.permission_ids.includes(id)
    )
}


/* ======================================================
   Save
====================================================== */

async function save()
{
    errors.value = {}
    saving.value = true

    try
    {
        const url = props.roleId
            ? Routes.admin.role.update(props.roleId)
            : Routes.admin.role.store

        const payload = {
            name: form.value.name,
            slug: form.value.slug,
            description: form.value.description,
            is_active: form.value.is_active,
            permission_ids: form.value.permission_ids,
        }

        const response = props.roleId
            ? await axios.patch(url, payload)
            : await axios.post(url, payload)

        if (response.data.success)
        {
            if (typeof props.onSaved === 'function')
            {
                props.onSaved(response.data.data)
            }

            closePanel()
        }

    } catch (error)
    {
        if (error.response?.data?.errors)
        {
            errors.value = error.response.data.errors
        }
        else
        {
            console.error(
                'خطا در ذخیره نقش:',
                error
            )
        }
    } finally
    {
        saving.value = false
    }
}


/* ======================================================
   Close Panel
====================================================== */

function closePanel()
{
    const panel = window[props.jsPanelVariableName]

    if (panel)
    {
        panel.close()
    }
}


/* ======================================================
   Field Error
====================================================== */

function fieldError(field)
{
    return (
        errors.value?.[field]?.[0] ??
        ''
    )
}


/* ======================================================
   Mounted
====================================================== */

onMounted(async () =>
{
    loading.value = true

    await Promise.all([
        loadPermissions(),
        loadRole(),
    ])

    loading.value = false
})

</script>


<template>

    <div
        class="min-h-full bg-slate-50 p-5"
        dir="rtl"
    >

        <!-- Loading -->

        <div
            v-if="loading"
            class="flex min-h-[300px] items-center justify-center"
        >

            <i
                class="fa fa-spinner fa-spin text-xl text-teal-600"
            ></i>

        </div>


        <!-- Form -->

        <form
            v-else
            class="space-y-5"
            autocomplete="off"
            @submit.prevent="save"
        >

            <!-- ==================================================
                 اطلاعات نقش
            ================================================== -->

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="mb-5 flex items-center gap-2">

                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600"
                    >
                        <ShieldCheck :size="18"/>
                    </div>

                    <div>
                        <h3 class="font-semibold text-slate-800">
                            اطلاعات نقش
                        </h3>
                        <p class="text-xs text-slate-400">
                            نام، شناسه و توضیحات نقش
                        </p>
                    </div>

                </div>


                <div class="grid gap-4 md:grid-cols-2">

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">
                            نام نقش
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            name="role_name"
                            autocomplete="off"
                            placeholder="مثلاً: مدیر محتوا"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                        >

                        <p v-if="fieldError('name')" class="mt-1 text-xs text-red-500">
                            {{ fieldError('name') }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">
                            شناسه (slug)
                        </label>

                        <input
                            v-model="form.slug"
                            type="text"
                            name="role_slug"
                            autocomplete="off"
                            dir="ltr"
                            placeholder="content-manager"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 font-mono text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                        >

                        <p v-if="fieldError('slug')" class="mt-1 text-xs text-red-500">
                            {{ fieldError('slug') }}
                        </p>
                    </div>

                </div>


                <div class="mt-4">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        توضیحات
                    </label>

                    <textarea
                        v-model="form.description"
                        rows="3"
                        name="role_description"
                        placeholder="توضیح کوتاه درباره سطح دسترسی این نقش"
                        class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                    ></textarea>

                    <p v-if="fieldError('description')" class="mt-1 text-xs text-red-500">
                        {{ fieldError('description') }}
                    </p>
                </div>

            </div>


            <!-- ==================================================
                 دسترسی‌ها
            ================================================== -->

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="mb-5 flex items-center justify-between">

                    <div class="flex items-center gap-2">

                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
                        >
                            <KeyRound :size="18"/>
                        </div>

                        <div>
                            <h3 class="font-semibold text-slate-800">
                                دسترسی‌ها
                            </h3>
                            <p class="text-xs text-slate-400">
                                انتخاب شده: {{ form.permission_ids.length }} از {{ permissions.length }}
                            </p>
                        </div>

                    </div>

                    <button
                        type="button"
                        :disabled="permissionsLoading || permissions.length === 0"
                        class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="toggleAll"
                    >
                        {{ allSelected ? 'لغو انتخاب همه' : 'انتخاب همه' }}
                    </button>

                </div>


                <div
                    v-if="permissionsLoading"
                    class="space-y-3"
                >
                    <div
                        v-for="n in 3"
                        :key="n"
                        class="h-16 animate-pulse rounded-xl bg-slate-100"
                    />
                </div>


                <div
                    v-else-if="permissions.length === 0"
                    class="py-8 text-center text-sm text-slate-400"
                >
                    دسترسی‌ای برای انتخاب وجود ندارد
                </div>


                <div
                    v-else
                    class="space-y-4"
                >

                    <div
                        v-for="(items, groupName) in groupedPermissions"
                        :key="groupName"
                        class="rounded-xl border border-slate-200 p-4"
                    >

                        <div class="mb-3 flex items-center justify-between">

                            <span class="text-sm font-semibold text-slate-700">
                                {{ groupName }}
                            </span>

                            <button
                                type="button"
                                class="text-xs font-medium text-teal-600 hover:text-teal-700"
                                @click="toggleGroup(groupName)"
                            >
                                {{ isGroupSelected(groupName) ? 'لغو انتخاب گروه' : 'انتخاب گروه' }}
                            </button>

                        </div>


                        <div class="grid gap-2 sm:grid-cols-2">

                            <label
                                v-for="perm in items"
                                :key="perm.id"
                                class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2.5 transition hover:border-slate-200"
                            >

                                <input
                                    type="checkbox"
                                    :checked="isPermissionSelected(perm.id)"
                                    class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                                    @change="togglePermission(perm.id)"
                                >

                                <div class="min-w-0">
                                    <div class="text-sm text-slate-700">
                                        {{ perm.label ?? perm.name }}
                                    </div>
                                    <div
                                        class="truncate font-mono text-[11px] text-slate-400"
                                        dir="ltr"
                                    >
                                        {{ perm.name }}
                                    </div>
                                </div>

                            </label>

                        </div>

                    </div>

                </div>

                <p v-if="fieldError('permission_ids')" class="mt-3 text-xs text-red-500">
                    {{ fieldError('permission_ids') }}
                </p>

            </div>


            <!-- ==================================================
                 وضعیت نقش
            ================================================== -->

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <label
                    class="flex cursor-pointer items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-4 transition hover:border-gray-300"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600"
                        >
                            <CheckCircle2 :size="20"/>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-gray-800">
                                نقش فعال باشد
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                نقش غیرفعال به کاربران جدید اختصاص داده نمی‌شود
                            </div>
                        </div>

                    </div>


                    <div class="relative">

                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            name="role_is_active"
                            autocomplete="off"
                            class="peer sr-only"
                        >

                        <div
                            class="h-6 w-11 rounded-full bg-gray-300 transition peer-checked:bg-[#14b8a6]"
                        ></div>

                        <div
                            class="absolute right-1 top-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:-translate-x-5"
                        ></div>

                    </div>

                </label>

            </div>


            <!-- ==================================================
                 دکمه‌ها
            ================================================== -->

            <div class="flex items-center justify-end gap-3">

                <button
                    type="button"
                    class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                    @click="closePanel"
                >
                    انصراف
                </button>

                <button
                    type="submit"
                    :disabled="saving"
                    class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60"
                >

                    <i
                        v-if="saving"
                        class="fa fa-spinner fa-spin"
                    ></i>

                    <span>
                        {{
                            saving
                                ? 'در حال ذخیره...'
                                : (isEdit ? 'ذخیره تغییرات' : 'ایجاد نقش')
                        }}
                    </span>

                </button>

            </div>

        </form>

    </div>

</template>