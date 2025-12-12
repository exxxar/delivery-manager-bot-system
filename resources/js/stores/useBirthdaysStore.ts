import { defineStore } from 'pinia'
import { makeAxiosFactory } from './utillites/makeAxiosFactory'

const path: string = '/bot-api/birthdays'

export interface BirthdayItem {
    name: string
    date: string
    weekday: string
    type: 'пользователь' | 'поставщик'
}

export const useBirthdaysStore = defineStore('birthdays', {
    state: () => ({
        items: [] as BirthdayItem[],
        loading: false,
        error: null as string | null
    }),

    getters: {
        byType: (s) => (type: string) => s.items.filter(i => i.type === type),
        today: (s) => s.items.filter(i => i.date === new Date().toISOString().split('T')[0])
    },

    actions: {
        // @ts-ignore
        async fetchNextWeek() {
            this.loading = true
            this.error = null

            try {
                const { data } = await makeAxiosFactory(path, 'POST')
                this.items = data
            } catch (e: any) {
                this.error = e?.message || 'Failed to load birthdays'
            } finally {
                this.loading = false
            }
        }
    }
})
