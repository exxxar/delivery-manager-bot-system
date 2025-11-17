import { defineStore } from 'pinia'
import { makeAxiosFactory } from './utillites/makeAxiosFactory'

export interface Supplier {
    id: number
    name: string
    description?: string
    address?: string
    phone?: string
    percent?: number
    birthday?: string
    email?: string
}

const path: string = '/bot-api/suppliers'


export const useSuppliersStore = defineStore('suppliers', {
    state: () => ({
        items: [] as Supplier[],
        loading: false,
        error: null as string | null,
    }),
    getters: {
        byId: (s) => (id: number) => s.items.find(x => x.id === id),
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
                this.error = e?.message || 'Failed to load suppliers'
            } finally {
                this.loading = false
            }
        },
        // @ts-ignore
        async fetchAllByPage(page = 1) {
            const { data } = await makeAxiosFactory(`${path}/?page=${page}`, 'GET')
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
                return data as Supplier
            } catch (e: any) {
                this.error = e?.message || 'Failed to load supplier'
                throw e
            }
        },

        async create(payload: Omit<Supplier, 'id'>) {
            const { data } = await makeAxiosFactory(`${path}`, 'POST', payload)
            this.items.push(data)
            return data as Supplier
        },

        async update(id: number, payload: Partial<Supplier>) {
            const { data } = await makeAxiosFactory(`${path}/${id}`, 'PUT', payload)
            const idx = this.items.findIndex(x => x.id === id)
            if (idx !== -1) this.items[idx] = data
            return data as Supplier
        },
        // @ts-ignore
        async remove(id: number) {
            await makeAxiosFactory(`${path}/${id}`, 'DELETE')
            this.items = this.items.filter(x => x.id !== id)
        },
    },
})
