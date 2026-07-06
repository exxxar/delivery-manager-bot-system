import { defineStore } from 'pinia'
import { makeAxiosFactory } from './utillites/makeAxiosFactory'

export interface UserLog {
    id: number
    user_id: number
    message: string
    created_at: string
    updated_at: string
}

const path: string = '/logs'

export const useUserLogsStore = defineStore('userLogs', {
    state: () => ({
        items: [] as UserLog[],
        pagination: null as any,
        loading: false,
        error: null as string | null,
    }),
    actions: {
        async fetchAll(page = 1) {
            this.loading = true
            this.error = null
            try {
                const { data } = await makeAxiosFactory(`${path}?page=${page}`, 'GET')
                this.items = data.data
                this.pagination = data
            } catch (e: any) {
                this.error = e?.message || 'Не удалось загрузить логи'
            } finally {
                this.loading = false
            }
        },
        async fetchByUrl(url: string) {
            this.loading = true
            this.error = null
            try {
                const { data } = await makeAxiosFactory(url, 'GET')
                this.items = data.data
                this.pagination = data
            } catch (e: any) {
                this.error = e?.message || 'Не удалось загрузить логи'
            } finally {
                this.loading = false
            }
        },
        formatDate(dateString: string): string {
            if (!dateString) return '-'
            const date = new Date(dateString)
            return date.toLocaleDateString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })
        }
    },
})
