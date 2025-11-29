import {createRouter, createWebHashHistory} from 'vue-router'
// @ts-ignore
import MenuPage from '../Pages/MenuPage.vue'
import AgentPage from '../Pages/AgentPage.vue'
import HelpPage from '../Pages/HelpPage.vue'
import AdminPage from '../Pages/AdminPage.vue'
import SupplierPage from '../Pages/SupplierPage.vue'
import ProductPage from '../Pages/ProductPage.vue'
import UserPage from '../Pages/UserPage.vue'
// @ts-ignore
import ExcelExportPage from '../Pages/ExcelExportPage.vue'
// @ts-ignore
import SalePage from '../Pages/SalePage.vue'
import AdminTasksPage from '../Pages/AdminTasksPage.vue'
import AgentTaskPage from '../Pages/AgentTaskPage.vue'
import ProductCategoryPage from '../Pages/ProductCategoryPage.vue'
import BlockedPage from '../Pages/BlockedPage.vue'

const routes = [
    {
        path: '/blocked',
        name: 'BlockedPage',
        component: BlockedPage,
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


const router = createRouter({
    history: createWebHashHistory(),
    routes,
})

export default router
