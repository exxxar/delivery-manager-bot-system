import { defineStore } from 'pinia'
import { makeAxiosFactory } from './utillites/makeAxiosFactory'

export interface Sale {
    id: number
    title: string
    description?: string
    status: 'pending' | 'assigned' | 'completed' | 'rejected'
    due_date?: string
    sale_date?: string
    quantity?: number
    total_price?: number
    agent_id?: number
    customer_id?: number
    supplier_id?: number
    product_id?: number
}

const path: string = '/bot-api/sales'


export const useSalesStore = defineStore('sales', {
    state: () => ({
        items: [] as Sale[],
        loading: false,
        error: null as string | null,
        sort: { field: 'id', direction: 'asc' } as { field: string; direction: 'asc' | 'desc' }
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
                this.error = e?.message || 'Failed to load sales'
            } finally {
                this.loading = false
            }
        },
        async fetchFiltered(page = 1) {
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

            const { data } = await makeAxiosFactory(`${path}?${params.toString()}`, 'GET')
            this.items = data.data
            this.pagination = data
            return true
        },

        setFilters(filters: Record<string, any>) {
            this.filters = filters
        },

        setSort(field: string, direction: 'asc' | 'desc') {
            this.sort = { field, direction }
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
                return data as Sale
            } catch (e: any) {
                this.error = e?.message || 'Failed to load sale'
                throw e
            }
        },

        async create(payload: Omit<Sale, 'id'>) {
            const { data } = await makeAxiosFactory(`${path}`, 'POST', payload)
            this.items.push(data)
            return data as Sale
        },

        async update(id: number, payload: Partial<Sale>) {
            const { data } = await makeAxiosFactory(`${path}/${id}`, 'PUT', payload)
            const idx = this.items.findIndex(x => x.id === id)
            if (idx !== -1) this.items[idx] = data
            return data as Sale
        },
        // 🔹 Подтверждение сделки
        async confirmDeal() {
            if (!this.selectedSale) return
            try {
                const { data } = await makeAxiosFactory(`${path}/${this.selectedSale.id}`, 'PUT', {
                    ...this.selectedSale,
                    status: 'completed',
                    sale_date: this.dealForm.sale_date,
                    quantity: this.dealForm.quantity,
                    total_price: this.dealForm.total_price
                })
                // обновляем список
                await this.fetchAllByPage(this.pagination?.current_page || 1)
                return data
            } catch (error: any) {
                console.error('Ошибка подтверждения сделки:', error)
                throw error
            }
        },

        // 🔹 Удаление продажи
        async deleteSale(id: number) {
            if (!this.selectedSale) return
            try {
                await makeAxiosFactory(`${path}/${id}`, 'DELETE')
                this.items = this.items.filter(s => s.id !== id)
                return true
            } catch (error: any) {
                console.error('Ошибка удаления продажи:', error)
                throw error
            }
        },
        async cancelDeal(sale: any) {
            try {
                const { data } = await makeAxiosFactory(`${path}/${sale.id}`, 'PUT', {
                    ...sale,
                    status: 'rejected'
                })
                // обновляем список на текущей странице
                await this.fetchAllByPage(this.pagination?.current_page || 1)
                return data
            } catch (error: any) {
                console.error('Ошибка отмены сделки:', error)
                throw error
            }
        }

    },
})
