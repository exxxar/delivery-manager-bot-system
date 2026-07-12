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

const path: string = '/suppliers'


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

        // 🔹 НОВЫЙ: активные поставщики
        async fetchActive(month: string, page = 1, size = 30) {
            this.loading = true
            this.error = null
            this.currentMode = 'active'
            this.currentMonth = month

            try {
                const params = new URLSearchParams()
                params.append('month', month)
                params.append('page', String(page))
                params.append('per_page', String(size))

                const { data } = await makeAxiosFactory(`${path}/active?${params.toString()}`, 'GET')
                this.items = data.data
                this.pagination = {
                    current_page: data.current_page,
                    per_page: data.per_page,
                    total: data.total,
                    last_page: data.last_page,
                    from: data.from,
                    to: data.to,
                    prev_page_url: data.prev_page_url,
                    next_page_url: data.next_page_url,
                    first_page_url: data.first_page_url,
                    last_page_url: data.last_page_url,
                }
                this.stats = data.stats
            } catch (e: any) {
                this.error = e?.message || 'Не удалось загрузить активных поставщиков'
            } finally {
                this.loading = false
            }
        },

        // 🔹 НОВЫЙ: неактивные поставщики
        async fetchInactive(month: string, page = 1, size = 30) {
            this.loading = true
            this.error = null
            this.currentMode = 'inactive'
            this.currentMonth = month

            try {
                const params = new URLSearchParams()
                params.append('month', month)
                params.append('page', String(page))
                params.append('per_page', String(size))

                const { data } = await makeAxiosFactory(`${path}/inactive?${params.toString()}`, 'GET')
                this.items = data.data
                this.pagination = {
                    current_page: data.current_page,
                    per_page: data.per_page,
                    total: data.total,
                    last_page: data.last_page,
                    from: data.from,
                    to: data.to,
                    prev_page_url: data.prev_page_url,
                    next_page_url: data.next_page_url,
                    first_page_url: data.first_page_url,
                    last_page_url: data.last_page_url,
                }
                this.stats = data.stats
            } catch (e: any) {
                this.error = e?.message || 'Не удалось загрузить неактивных поставщиков'
            } finally {
                this.loading = false
            }
        },

        // 🔹 НОВЫЙ: пагинация по URL для активных/неактивных
        async fetchByUrl(url: string) {
            this.loading = true
            this.error = null

            try {
                const { data } = await makeAxiosFactory(url, 'GET')
                this.items = data.data
                this.pagination = {
                    current_page: data.current_page,
                    per_page: data.per_page,
                    total: data.total,
                    last_page: data.last_page,
                    from: data.from,
                    to: data.to,
                    prev_page_url: data.prev_page_url,
                    next_page_url: data.next_page_url,
                    first_page_url: data.first_page_url,
                    last_page_url: data.last_page_url,
                }

                // Если есть статистика (для активных/неактивных)
                if (data.stats) {
                    this.stats = data.stats
                }
            } catch (e: any) {
                this.error = e?.message || 'Не удалось загрузить данные'
            } finally {
                this.loading = false
            }
        },
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
        async fetchAllByPage(page = 1, size = 30) {
            const { data } = await makeAxiosFactory(`${path}?page=${page}&size=${size}`, 'GET')
            this.items = data.data
            this.pagination = data
        },
        // @ts-ignore

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
