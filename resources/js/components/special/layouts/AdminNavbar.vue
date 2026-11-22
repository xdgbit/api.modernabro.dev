<script setup>
import {ref, computed, onMounted, onBeforeUnmount} from 'vue'
import {useRoute} from 'vue-router'
import {
    LayoutDashboard,
    ShoppingCart,
    Package,
    Tags,
    Boxes,
    Users,
    Star,
    Ticket,
    Truck,
    Wallet,
    Settings,
    Headset,
    FileText,
    ShieldCheck,
    ChevronDown,
    MessageSquareText,
    LayoutGrid,
    Layers,
    Percent,
} from 'lucide-vue-next'
import {subscribeExpertBadgeUpdates} from '@/services/pushEvent'

const route = useRoute()

const isSuperAdmin = window.authUserExpert?.is_super_admin === true

const orderBadge = ref(0)
const isBadgeLoading = ref(false)

const getOrderBadge = async () =>
{
    isBadgeLoading.value = true
    try
    {
        const response = await axios.post(Routes.dashboard.get_badge_data)
        // سفارش‌های در انتظار پردازش
        orderBadge.value = Number(response.data.data.order_badge ?? 0)
    } catch (error)
    {
        console.error('Error fetching order badge:', error)
    } finally
    {
        isBadgeLoading.value = false
    }
}

let unsubscribeBadgeUpdates = null

// کدام منوی کشویی باز است (null یعنی هیچ‌کدام)
const openMenu = ref(null)

const toggleMenu = (key) =>
{
    openMenu.value = openMenu.value === key ? null : key
}

const closeMenus = () =>
{
    openMenu.value = null
}

/* ----------------------------------------------------------
   آیتم‌های اصلی (همیشه داخل نوار نمایش داده می‌شوند)
---------------------------------------------------------- */
const mainItems = [
    {title: 'داشبورد', path: '/dashboard', icon: LayoutDashboard},
    {title: 'سفارش‌ها', path: '/orders', icon: ShoppingCart, badgeKey: 'orders'},
    {title: 'محصولات', path: '/products', icon: Package},
    {title: 'دسته‌بندی‌ها', path: '/product/categories', icon: Layers},
    {title: 'موجودی انبار', path: '/inventory', icon: Boxes},
    {title: 'مشتریان', path: '/customers', icon: Users},
    {title: 'نظرات محصولات', path: '/product/reviews', icon: Star},
    {title: 'پیام‌های تماس با ما', path: '/contact-messages', icon: MessageSquareText},
    {title: 'کارشناسان', path: '/experts', icon: Headset, superAdminOnly: true},
]

/* ----------------------------------------------------------
   منوهای کشویی
   برای اضافه کردن آیتم جدید به هر گروه، فقط یک خط به children اضافه کنید.
   برای ساخت منوی کشویی جدید، یک گروه جدید به این آرایه اضافه کنید.
---------------------------------------------------------- */
const dropdownMenus = [
    {
        key: 'marketing',
        title: 'بازاریابی و فروش',
        icon: Percent,
        children: [
            {title: 'کدهای تخفیف', path: '/discount-codes', icon: Ticket},
            {title: 'مقالات', path: '/articles', icon: FileText},
        ],
    },
    {
        key: 'management',
        title: 'مدیریت فروشگاه',
        icon: LayoutGrid,
        children: [
            {title: 'برندها', path: '/brands', icon: Tags},
            {title: 'روش‌های ارسال', path: '/shipping-methods', icon: Truck},
            {title: 'روش‌های پرداخت', path: '/payment-methods', icon: Wallet},
            {title: 'تنظیمات', path: '/settings', icon: Settings},
            {
                title: 'نقش‌ها و دسترسی‌ها',
                path: '/roles-permissions',
                icon: ShieldCheck,
                superAdminOnly: true,
            },
        ],
    },
]

// فیلتر آیتم‌های مخصوص سوپر ادمین و حذف منوهایی که خالی شده‌اند
const visibleMainItems = computed(() =>
    mainItems.filter((item) => !item.superAdminOnly || isSuperAdmin)
)

const visibleDropdownMenus = computed(() =>
    dropdownMenus
    .map((menu) => ({
        ...menu,
        children: menu.children.filter(
            (child) => !child.superAdminOnly || isSuperAdmin
        ),
    }))
    .filter((menu) => menu.children.length > 0)
)

// آیا یکی از زیرمنوهای این گروه، صفحه فعلی است؟
const isGroupActive = (menu) =>
    menu.children.some((child) => route.path === child.path)

const handleClickOutside = (event) =>
{
    if (!event.target.closest('[data-dropdown]'))
    {
        closeMenus()
    }
}

onMounted(() =>
{
    document.addEventListener('click', handleClickOutside)

    getOrderBadge()

    unsubscribeBadgeUpdates = subscribeExpertBadgeUpdates(() =>
    {
        getOrderBadge()
    })
})

onBeforeUnmount(() =>
{
    document.removeEventListener('click', handleClickOutside)
    unsubscribeBadgeUpdates?.()
})
</script>

<template>
    <nav class="relative border-b border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <div class="flex min-w-max px-3 lg:px-6">

                <!-- آیتم‌های اصلی -->
                <router-link
                    v-for="item in visibleMainItems"
                    :key="item.path"
                    :to="item.path"
                    class="group relative flex h-14 shrink-0 items-center gap-2 px-4 text-sm text-slate-500 transition hover:bg-slate-50 hover:text-slate-800"
                    active-class="!text-teal-600"
                >
                    <component :is="item.icon" :size="18" :stroke-width="1.8"/>

                    <span>{{ item.title }}</span>

                    <!-- بج تعداد سفارش‌های در انتظار -->
                    <span
                        v-if="item.badgeKey === 'orders' && (orderBadge > 0 || isBadgeLoading)"
                        class="inline-flex h-8 min-w-8 items-center justify-center rounded-full bg-gradient-to-br from-red-500 to-rose-600 px-2 text-sm font-extrabold leading-none text-white shadow-md shadow-red-500/30 ring-2 ring-white"
                    >
                        <span
                            v-if="isBadgeLoading"
                            class="block h-5 w-5 animate-spin rounded-full border-[3px] border-white/30 border-t-white"
                        ></span>
                        <template v-else>
                            {{ orderBadge > 99 ? '۹۹+' : orderBadge.toLocaleString('fa-IR') }}
                        </template>
                    </span>

                    <!-- Hover indicator -->
                    <span class="absolute inset-x-3 bottom-0 h-0.5 scale-x-0 bg-teal-500 transition group-hover:scale-x-50"/>

                    <!-- Active indicator -->
                    <span
                        v-if="route.path === item.path"
                        class="absolute inset-x-3 bottom-0 h-0.5 bg-teal-500"
                    />
                </router-link>

                <!-- منوهای کشویی -->
                <div
                    v-for="menu in visibleDropdownMenus"
                    :key="menu.key"
                    data-dropdown
                    class="relative shrink-0"
                >
                    <button
                        type="button"
                        @click="toggleMenu(menu.key)"
                        :class="[
                            'flex h-14 items-center gap-2 px-4 text-sm transition hover:bg-slate-50 hover:text-slate-800',
                            isGroupActive(menu) ? 'text-teal-600' : 'text-slate-500'
                        ]"
                    >
                        <component :is="menu.icon" :size="18" :stroke-width="1.8"/>

                        <span>{{ menu.title }}</span>

                        <ChevronDown
                            :size="16"
                            :stroke-width="1.8"
                            :class="['transition-transform duration-200', openMenu === menu.key ? 'rotate-180' : '']"
                        />
                    </button>

                    <!-- دراپ‌داون -->
                    <div
                        v-if="openMenu === menu.key"
                        class="fixed z-[99999] w-56 rounded-lg border border-slate-200 bg-white p-1 shadow-xl"
                    >
                        <router-link
                            v-for="child in menu.children"
                            :key="child.path"
                            :to="child.path"
                            @click="closeMenus"
                            :class="[
                                'flex items-center gap-3 rounded-md px-3 py-2.5 text-sm transition hover:bg-slate-50 hover:text-teal-600',
                                route.path === child.path ? 'text-teal-600' : 'text-slate-600'
                            ]"
                        >
                            <component :is="child.icon" :size="17" :stroke-width="1.8"/>
                            <span>{{ child.title }}</span>
                        </router-link>
                    </div>
                </div>

            </div>
        </div>
    </nav>
</template>