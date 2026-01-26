import {defineStore} from 'pinia'
import {makeAxiosFactory} from './utillites/makeAxiosFactory'
import axios from "axios/index";

export interface Sale {
    id: number
    title: string
    description?: string
    status: 'pending' | 'assigned' | 'completed' | 'rejected'
    due_date?: string
    sale_date?: string
    quantity?: number
    total_price?: number
    payment_type?: number
    payment_document_name?: string
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
        filters: null,
        error: null as string | null,
        sort: {field: 'id', direction: 'desc'} as { field: string; direction: 'asc' | 'desc' }
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
                const {data} = await makeAxiosFactory(`${path}`, 'GET')
                this.items = data
            } catch (e: any) {
                this.error = e?.message || 'Failed to load sales'
            } finally {
                this.loading = false
            }
        },
        async acceptAll(ids: number[]) {
            await makeAxiosFactory(`${path}/accept-all`, 'POST', {
                ids: ids
            })

        },
        async selfSalesFiltered(page = 1, size = 30) {
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

            const {data} = await makeAxiosFactory(`${path}/self-sales?${params.toString()}`, 'GET')
            this.items = data.data
            this.pagination = data
            return true
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

            const {data} = await makeAxiosFactory(`${path}?${params.toString()}`, 'GET')
            this.items = data.data
            this.pagination = data
            return true
        },

        setFilters(filters: Record<string, any>) {
            this.filters = filters
        },

        setSort(field: string, direction: 'asc' | 'desc') {
            this.sort = {field, direction}
        },
        // @ts-ignore
        async fetchAllByPage(page = 1) {
            const {data} = await makeAxiosFactory(`${path}?page=${page}`, 'GET')
            this.items = data.data
            this.pagination = data
        },
        // @ts-ignore
        async fetchByUrl(url: string) {
            const {data} = await makeAxiosFactory(url, 'GET')
            this.items = data.data
            this.pagination = data
        },
        async sendPaymentDocumentToTg(id: number) {
            try {
                const {data} = await makeAxiosFactory(`${path}/payment-document/${id}`, 'GET')
                return true
            } catch (e: any) {
                this.error = e?.message || 'Failed to load sale'
                throw e
            }
        },
        async fetchOne(id: number) {
            try {
                const {data} = await makeAxiosFactory(`${path}/${id}`, 'GET')
                return data as Sale
            } catch (e: any) {
                this.error = e?.message || 'Failed to load sale'
                throw e
            }
        },

        async create(payload: object, file: null) {
            const formData = new FormData()

            Object.keys(payload)
                .forEach(key => {
                    const item = payload[key] || ''
                    if (typeof item === 'object')
                        formData.append(key, JSON.stringify(item))
                    else
                        formData.append(key, item)
                });

            if (file)
                formData.append('file', file)

            const {data} = await makeAxiosFactory(`${path}`, 'POST', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })

            this.items.push(data)
            return data as Sale
        },

        async update(id: number, payload: object, file: null) {
            const formData = new FormData()
            Object.keys(payload)
                .forEach(key => {
                    const item = payload[key] || ''
                    if (typeof item === 'object')
                        formData.append(key, JSON.stringify(item))
                    else
                        formData.append(key, item)
                });

            if (file)
                formData.append('file', file)

            const {data} = await makeAxiosFactory(`${path}/${id}`, 'POST', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })

            const idx = this.items.findIndex(x => x.id === id)
            if (idx !== -1) this.items[idx] = data
            return data as Sale
        },
        async confirmPayment(sale: object) {
            try {
                const formData = new FormData()
                // @ts-ignore
                formData.append("id", sale.id || null)
                // @ts-ignore
                formData.append("payment_type", sale.payment_type || 0)
                // @ts-ignore
                formData.append("receipt_is_lost", sale.receipt_is_lost || false)
                // @ts-ignore
                if (sale.file)
                    // @ts-ignore
                    formData.append('file', sale.file)

                const {data} = await makeAxiosFactory(`${path}/confirm-payment`, 'POST', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })

                await this.fetchAllByPage(this.pagination?.current_page || 1)
                return data

            } catch (error: any) {
                throw error
            }
        },
        // 🔹 Подтверждение сделки
        async confirmDeal(sale: object) {
            try {

                // @ts-ignore
                const {data} = await makeAxiosFactory(`${path}/confirm-deal`, 'POST', {
                    ...sale,
                    status: 'completed',
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
                const {data} = await makeAxiosFactory(`${path}/${sale.id}`, 'PUT', {
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
