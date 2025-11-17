import {defineStore} from 'pinia'
import {makeAxiosFactory} from "./utillites/makeAxiosFactory"

export interface Agent {
    id: number
    user_id?: number
    name: string
    phone?: string
    email?: string
    region?: string
}

const path: string = "/bot-api/agents"


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
        async fetchOne(id: number) {
            try {
                const {data} = await makeAxiosFactory(`${path}/${id}`, 'GET')
                return data as Agent
            } catch (e: any) {
                this.error = e?.message || 'Failed to load agent'
                throw e
            }
        },

        async create(payload: Omit<Agent, 'id'>) {
            const {data} = await makeAxiosFactory(`${path}`, 'POST', payload)
            this.items.push(data)
            return data as Agent
        },

        async update(id: number, payload: Partial<Agent>) {
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
