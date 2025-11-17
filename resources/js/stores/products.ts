import { defineStore } from 'pinia'
import { makeAxiosFactory } from './utillites/makeAxiosFactory'

export interface Product {
    id: number
    name: string
    description?: string
    price: number
    count?: number
    supplier_id?: number
    product_category_id?: number
}

const path: string = '/bot-api/products'

export const useProductsStore = defineStore('products', {
    state: () => ({
        items: [] as Product[],
        loading: false,
        error: null as string | null,
    }),
    getters: {
        byId: (s) => (id: number) => s.items.find(p => p.id === id),
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
                this.error = e?.message || 'Failed to load products'
            } finally {
                this.loading = false
            }
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
                return data as Product
            } catch (e: any) {
                this.error = e?.message || 'Failed to load product'
                throw e
            }
        },

        async create(payload: Omit<Product, 'id'>) {
            const { data } = await makeAxiosFactory(`${path}`, 'POST', payload)
            this.items.push(data)
            return data as Product
        },

        async update(id: number, payload: Partial<Product>) {
            const { data } = await makeAxiosFactory(`${path}/${id}`, 'PUT', payload)
            const idx = this.items.findIndex(p => p.id === id)
            if (idx !== -1) this.items[idx] = data
            return data as Product
        },
// @ts-ignore
        async remove(id: number) {
            await makeAxiosFactory(`${path}/${id}`, 'DELETE')
            this.items = this.items.filter(p => p.id !== id)
        },
    },
})
