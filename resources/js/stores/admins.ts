import {defineStore} from 'pinia'
import {makeAxiosFactory} from './utillites/makeAxiosFactory'
import { useAlertStore } from './utillites/useAlertStore'

export interface User {
    id: number
    name: string
    fio_from_telegram?: string
    email: string
    telegram_chat_id?: string
    role: number
    percent: number
    is_work: boolean
    email_verified_at?: string
    blocked_at?: string
    blocked_message?: string
}

const path: string = '/bot-api/admins'

export const useAdminsStore = defineStore('admins', {
    state: () => ({
        items: [] as User[],
        loading: false,
        error: null as string | null,
    }),
    getters: {
        byId: (s) => (id: number) => s.items.find(u => u.id === id),
    },
    actions: {
        // @ts-ignore
        async fetchAdminsByPage(page = 1) {
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
        async downloadPersonalAdminReport(criteria: { startDate: string; endDate: string;  user_id: number }) {

            const alertStore = useAlertStore()
            alertStore.show( "Подготавливаем отчет")
            this.loading = true
            this.error = null
            try {
                const {data} = await makeAxiosFactory(`${path}/download-personal-report`, 'POST', criteria)
                alertStore.show( "Отчет отправлен","success")
                return data
            } catch (error: any) {
                this.error = error.response?.data?.message ?? 'Ошибка отправки отчёта'
                alertStore.show( this.error,"error")
                throw error
            } finally {
                this.loading = false
            }

        },
        async downloadReport(criteria: { startDate: string; endDate: string; type: string, object_id: number }) {
            this.loading = true
            this.error = null
            try {
                const {data} = await makeAxiosFactory(`${path}/download-report`, 'POST', criteria)
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
