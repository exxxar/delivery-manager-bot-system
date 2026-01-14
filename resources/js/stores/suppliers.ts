import { defineStore } from 'pinia'
import { makeAxiosFactory } from './utillites/makeAxiosFactory'
import {Agent} from "./agents";

export interface Supplier {
    id: number
    name: string
    description?: string
    address?: string
    phone?: string
    work_phone?: string
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
        sort: { field: 'id', direction: 'desc' } as { field: string; direction: 'asc' | 'desc' },
        pagination:null,
    }),
    getters: {
        byId: (s) => (id: number) => s.items.find(x => x.id === id),
    },
    actions: {
        setFilters(filters: Record<string, any>) {
            // @ts-ignore
            this.filters = filters
        },

        setSort(field: string, direction: 'asc' | 'desc') {
            this.sort = { field, direction }
        },
        async fetchFiltered(page = 1, size = 30) {
            const params = new URLSearchParams()

            // фильтры
            // @ts-ignore
            Object.entries(this.filters).forEach(([key, value]) => {
                if (value !== null && value !== undefined && value !== '') {
                    params.append(key, String(value))
                }
            })

            // сортировка
            params.append('sort_field', this.sort.field)
            params.append('sort_direction', this.sort.direction)

            // пагинация
            params.append('page', String(page))
            params.append('size', String(size))

            const { data } = await makeAxiosFactory(`${path}?${params.toString()}`, 'GET')
            this.items = data.data
            this.pagination = data
            return true
        },
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
                return data as Supplier
            } catch (e: any) {
                this.error = e?.message || 'Failed to load supplier'
                throw e
            }
        },
        // @ts-ignore
        async fetchSupplierWithProducts(page = 1) {
            const { data } = await makeAxiosFactory(`${path}/with-products?page=${page}`, 'GET')
            this.items = data.data
            this.pagination = data
        },

        // @ts-ignore
        async loadMoreSupplierProducts(supplierId, page) {
            const { data } = await makeAxiosFactory(`/fetch-next-products/${supplierId}/products?page=${page}`, 'GET')
            const supplier = this.items.find(s => s.id === supplierId)
            // @ts-ignore
            supplier.products.push(...data.data)
        },
        async create(payload: Omit<Supplier, 'id'>) {
            const { data } = await makeAxiosFactory(`${path}`, 'POST', payload)
            this.items.push(data)
            return data as Supplier
        },
        async toggleFavorites(id: number) {
            const {data} = await makeAxiosFactory(`${path}/toggle-favorite`, 'POST',{
                id: id
            })
           // this.items = data.data
            //this.pagination = data
        },
        // @ts-ignore
        async removeAll(ids: number[]) {
            await makeAxiosFactory(`${path}/remove-all`, 'POST', {
                ids: ids
            })
            ids.forEach(id => {
                this.items = this.items.filter(c => c.id !== id)
            })
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
