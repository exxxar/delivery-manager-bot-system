import { defineStore } from 'pinia'
import { makeAxiosFactory } from './utillites/makeAxiosFactory'

export interface ProductCategory {
    id: number
    name: string
    description?: string
}

const path: string = '/bot-api/product-categories'

export const useProductCategoriesStore = defineStore('productCategories', {
    state: () => ({
        items: [] as ProductCategory[],
        loading: false,
        error: null as string | null,
    }),
    getters: {
        byId: (s) => (id: number) => s.items.find(c => c.id === id),
    },
    actions: {
        // @ts-ignore
        async fetchAll() {
            this.loading = true
            this.error = null
            try {
                const { data } = await makeAxiosFactory(`${path}`, 'GET')
                this.items = data
            } catch (e: any) {
                this.error = e?.message || 'Failed to load categories'
            } finally {
                this.loading = false
            }
        },
        // @ts-ignore
        async fetchProductsByCategory(page = 1) {
            const { data } = await makeAxiosFactory(`${path}/with-products?page=${page}`, 'GET')
            this.items = data.data
            this.pagination = data
        },

        // @ts-ignore
        async loadMoreProductsInCategory(supplierId, page) {
            const { data } = await makeAxiosFactory(`${path}/fetch-next-products/${supplierId}/products?page=${page}`, 'GET')
            const category = this.items.find(s => s.id === supplierId)
            category.products.push(...data.data)
        },
        // @ts-ignore
        async fetchAllByPage(page = 1) {
            const { data } = await makeAxiosFactory(`${path}?page=${page}`, 'GET')
            this.items = data.data
            this.pagination = data
        },
        // @ts-ignore
        async fetchByUrl(url: string) {
            const { data } = await makeAxiosFactory(url, 'GET')
            this.items = data.data
            this.pagination = data
        },
        async fetchOne(id: number) {
            try {
                const { data } = await makeAxiosFactory(`${path}/${id}`, 'GET')
                return data as ProductCategory
            } catch (e: any) {
                this.error = e?.message || 'Failed to load category'
                throw e
            }
        },

        async create(payload: Omit<ProductCategory, 'id'>) {
            const { data } = await makeAxiosFactory(`${path}`, 'POST', payload)
            this.items.push(data)
            return data as ProductCategory
        },

        async update(id: number, payload: Partial<ProductCategory>) {
            const { data } = await makeAxiosFactory(`${path}/${id}`, 'PUT', payload)
            const idx = this.items.findIndex(c => c.id === id)
            if (idx !== -1) this.items[idx] = data
            return data as ProductCategory
        },
// @ts-ignore
        async remove(id: number) {
            await makeAxiosFactory(`${path}/${id}`, 'DELETE')
            this.items = this.items.filter(c => c.id !== id)
        },
    },
})
