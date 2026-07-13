import {defineStore} from 'pinia'
import {makeAxiosFactory} from "./utillites/makeAxiosFactory"

export interface Agent {
    id: number
    user_id?: number
    mentor_id?: number
    name: string
    phone?: string
    email?: string
    region?: string
    in_learning?: boolean
    registration_at?: string | null
    month_sales_count?: number
    month_turnover?: number
}

const path: string = "/agents"

export const useAgentsStore = defineStore('agents', {
    state: () => ({
        items: [] as Agent[],
        loading: false,
        error: null as string | null,
    }),
    getters: {
        byId: (s) => (id: number) => s.items.find(a => a.id === id),
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
                this.error = e?.message || 'Failed to load agents'
            } finally {
                this.loading = false
            }
        },


        // @ts-ignore
        async fetchAllByPage(page = 1, size = 20) {
            const {data} = await makeAxiosFactory(`${path}?page=${page}&size=${size}`, 'GET')
            this.items = data.data
            this.pagination = data
        },
        async fetchByUrl(url: string) {
            this.loading = true
            this.error = null
            try {
                const { data } = await makeAxiosFactory(url, 'GET')
                this.items = data.data
                this._mapPagination(data)
                if (data.stats) this.stats = data.stats
            } catch (e: any) {
                this.error = e?.message || 'Ошибка загрузки'
            } finally {
                this.loading = false
            }
        },

        // Вспомогательный метод для маппинга пагинации Laravel
        _mapPagination(data: any) {
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
        },

        async fetchActive(month: string, page = 1, size = 20, search = '') {
            this.loading = true
            this.error = null
            this.currentMode = 'active'
            this.currentMonth = month

            try {
                const params = new URLSearchParams()
                params.append('month', month)
                params.append('page', String(page))
                params.append('per_page', String(size))
                if (search) params.append('search', search)

                const { data } = await makeAxiosFactory(`${path}/active?${params.toString()}`, 'GET')
                this.items = data.data
                this._mapPagination(data)
                this.stats = data.stats
            } catch (e: any) {
                this.error = e?.message || 'Ошибка загрузки'
            } finally {
                this.loading = false
            }
        },

        async fetchInactive(month: string, page = 1, size = 20, search = '') {
            this.loading = true
            this.error = null
            this.currentMode = 'inactive'
            this.currentMonth = month

            try {
                const params = new URLSearchParams()
                params.append('month', month)
                params.append('page', String(page))
                params.append('per_page', String(size))
                if (search) params.append('search', search)

                const { data } = await makeAxiosFactory(`${path}/inactive?${params.toString()}`, 'GET')
                this.items = data.data
                this._mapPagination(data)
                this.stats = data.stats
            } catch (e: any) {
                this.error = e?.message || 'Ошибка загрузки'
            } finally {
                this.loading = false
            }
        },

        async fetchOne(id: number) {
            try {
                const {data} = await makeAxiosFactory(`${path}/${id}`, 'GET')
                return data as Agent
            } catch (e: any) {
                this.error = e?.message || 'Failed to load agent'
                throw e
            }
        },
        async removePercentage(payload: object) {
            const {data} = await makeAxiosFactory(`${path}/remove-percentage`, 'POST', payload)
            return data
        },
        async fetchPercentage(payload: object) {
            const {data} = await makeAxiosFactory(`${path}/percentage`, 'POST', payload)
            return data
        },
        async create(payload: object) {
            const {data} = await makeAxiosFactory(`${path}`, 'POST', payload)
            this.items.push(data)
            return data as Agent
        },

        async storePercentage(payload: object) {
            const {data} = await makeAxiosFactory(`${path}/store-percentage`, 'POST', payload)
            const idx = this.items.findIndex(a => a.id === payload.id)
            if (idx !== -1) this.items[idx] = data
            return data as Agent
        },

        async update(id: number, payload: object) {
            const {data} = await makeAxiosFactory(`${path}/${id}`, 'PUT', payload)
            const idx = this.items.findIndex(a => a.id === id)
            if (idx !== -1) this.items[idx] = data
            return data as Agent
        },
        // @ts-ignore
        async remove(id: number) {
            await makeAxiosFactory(`${path}/${id}`, 'DELETE')
            this.items = this.items.filter(x => x.id !== id)
        },
        // @ts-ignore
        async downloadSelReport(criteria: { startDate: string; endDate: string; type: string }) {
            this.loading = true
            this.error = null
            try {
                const {data} = await makeAxiosFactory(`${path}/download-self-report`, 'POST', criteria)
                return data
            } catch (error: any) {
                this.error = error.response?.data?.message ?? 'Ошибка отправки отчёта'
                throw error
            } finally {
                this.loading = false
            }

        },
    },
})
