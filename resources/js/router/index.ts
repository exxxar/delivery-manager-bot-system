import {createRouter, createWebHashHistory} from 'vue-router'

import { defineAsyncComponent } from 'vue'
import {useUsersStore} from "../stores/users";

const MenuPage = defineAsyncComponent(() => import('../Pages/MenuPage.vue'))
const AgentPage = defineAsyncComponent(() => import('../Pages/AgentPage.vue'))
const HelpPage = defineAsyncComponent(() => import('../Pages/HelpPage.vue'))
const AdminPage = defineAsyncComponent(() => import('../Pages/AdminPage.vue'))
const SupplierPage = defineAsyncComponent(() => import('../Pages/SupplierPage.vue'))
const ProductPage = defineAsyncComponent(() => import('../Pages/ProductPage.vue'))
const UserPage = defineAsyncComponent(() => import('../Pages/UserPage.vue'))
const ExcelExportPage = defineAsyncComponent(() => import('../Pages/ExcelExportPage.vue'))
const BirthdayPage = defineAsyncComponent(() => import('../Pages/BirthdayPage.vue'))
const SalePage = defineAsyncComponent(() => import('../Pages/SalePage.vue'))
const AdminTasksPage = defineAsyncComponent(() => import('../Pages/AdminTasksPage.vue'))
const AgentTaskPage = defineAsyncComponent(() => import('../Pages/AgentTaskPage.vue'))
const ProductCategoryPage = defineAsyncComponent(() => import('../Pages/ProductCategoryPage.vue'))
const BlockedPage = defineAsyncComponent(() => import('../Pages/BlockedPage.vue'))
const AuthPage = defineAsyncComponent(() => import('../Pages/AuthPage.vue'))


const routes = [
    {
        path: '/login',
        name: 'AuthPage',
        component: AuthPage,
    },
    {
        path: '/reports',
        name: 'ReportsPage',
        component: () => import('../Pages/ReportsPage.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/user-logs',
        name: 'UserLogsPage',
        component: () => import('@/Pages/UserLogsPage.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/blocked',
        name: 'BlockedPage',
        component: BlockedPage,
    },
    {
        path: '/birth',
        name: 'BirthdayPage',
        component: BirthdayPage,
    },
    {
        path: '/',
        name: 'MenuPage',
        component: MenuPage,
    },
    {
        path: '/help',
        name: 'HelpPage',
        component: HelpPage,
    },
    {
        path: '/sales',
        name: 'SalePage',
        component: SalePage,
    },

    {
        path: '/admin-tasks',
        name: 'AdminTasksPage',
        component: AdminTasksPage,
    },
    {
        path: '/agent-tasks',
        name: 'AgentTaskPage',
        component: AgentTaskPage,
    },
    {
        path: '/agents',
        name: 'AgentPage',
        component: AgentPage,
    },
    {
        path: '/admins',
        name: 'AdminPage',
        component: AdminPage,
    },
    {
        path: '/users',
        name: 'UserPage',
        component: UserPage,
    },
    {
        path: '/suppliers',
        name: 'SupplierPage',
        component: SupplierPage,
    },
    {
        path: '/products',
        name: 'ProductPage',
        component: ProductPage,
    },

    {
        path: '/excel-export',
        name: 'ExcelExportPage',
        component: ExcelExportPage,
    },
    {
        path: '/product-categories',
        name: 'ProductCategoryPage',
        component: ProductCategoryPage,
    },
]


let router = createRouter({
    history: createWebHashHistory(),
    routes,

})

router.beforeEach(async (to) => {

    const auth = useUsersStore();

    if (!auth)
        window.location.href = "/login"

});

// @ts-ignore
export default router

