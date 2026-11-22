import {createRouter, createWebHistory} from 'vue-router'

const router = createRouter({
    history: createWebHistory('/admin/'),

    routes: [
        {
            path: '/test',
            name: 'admin.test',
            component: () => import('@/Test.vue'),
        },
        {
            path: '/login',
            name: 'admin.login',
            component: () => import('@/components/special/auth/Login.vue'),
        },
        {
            path: '/',
            component: () => import('@/components/special/layouts/AdminLayout.vue'),

            children: [
                {
                    path: '',
                    redirect: '/dashboard',
                },
                {
                    path: 'dashboard',
                    name: 'admin.dashboard',
                    component: () => import('@/components/special/master/dashboard/Dashboard.vue'),
                },
                {
                    path: 'products',
                    name: 'admin.products',
                    component: () => import('@/components/special/master/product/catalog/Main.vue'),
                },
                {
                    path: 'product/reviews',
                    name: 'admin.product.reviews',
                    component: () => import('@/components/special/master/product/reviews/Main.vue'),
                },
                {
                    path: 'product/categories',
                    name: 'admin.product.categories',
                    component: () => import('@/components/special/master/product/categories/Main.vue'),
                },
                {
                    path: 'inventory',
                    name: 'admin.inventory',
                    component: () => import('@/components/special/master/inventory/Main.vue'),
                },
                {
                    path: 'orders',
                    name: 'admin.orders',
                    component: () => import('@/components/special/master/orders/Main.vue'),
                },
                {
                    path: 'customers',
                    name: 'admin.customers',
                    component: () => import('@/components/special/master/customers/Main.vue'),
                },
                {
                    path: 'contact-messages',
                    name: 'admin.contact-messages',
                    component: () => import('@/components/special/master/contact_messages/Main.vue'),
                },
                {
                    path: 'settings',
                    name: 'admin.settings',
                    component: () => import('@/components/special/master/settings/Main.vue'),
                },
                {
                    path: 'articles',
                    name: 'admin.articles',
                    component: () => import('@/components/special/master/articles/Main.vue'),
                },
                {
                    path: 'article-categories',
                    name: 'admin.article-categories',
                    component: () => import('@/components/special/master/article_categories/Main.vue'),
                },
                {
                    path: 'experts',
                    name: 'admin.experts',
                    component: () => import('@/components/special/master/experts/Main.vue'),
                    meta: {
                        superAdminOnly: true,
                    },
                },
                {
                    path: 'roles-permissions',
                    name: 'admin.roles-permissions',
                    component: () => import('@/components/special/master/roles_permissions/Main.vue'),
                    meta: {
                        superAdminOnly: true,
                    },
                },
                {
                    path: 'no-access',
                    name: 'admin.no-access',
                    component: () => import('@/components/special/auth/NoAccess.vue'),
                },
                {
                    path: 'payment-methods',
                    name: 'admin.payment-methods',
                    component: () => import('@/components/special/master/payment_methods/Main.vue'),
                },
                {
                    path: 'change-password',
                    name: 'admin.change-password',
                    component: () => import('@/components/special/master/account/ChangePassword.vue'),
                    meta: {
                        superAdminOnly: true,
                    },
                },
            ],
        },
    ],
})

router.beforeEach((to, from, next) =>
{
    if (
        to.meta.superAdminOnly &&
        window.authUserExpert?.is_super_admin !== true
    )
    {
        return next({
            name: 'admin.no-access',
        })
    }

    next()
})

export default router
