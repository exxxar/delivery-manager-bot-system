import { defineStore } from 'pinia'
import { makeAxiosFactory } from './utillites/makeAxiosFactory'
import { useAlertStore } from './utillites/useAlertStore'

const path: string = '/bot-api/exports'

export const useBaseExports = defineStore('exports', {
    state: () => ({
        loading: false as boolean,
        error: null as string | null,
        successMessage: null as string | null,
        exportData: null as any
    }),
    actions: {
        async exportBirthdaysList(){
            return this._exportHelper(`${path}/birthdays`, 'Дни рождения выгружены')
        },
        async exportAgents() {
            return this._exportHelper(`${path}/agents`, 'Младшие администраторы выгружены')
        },
        async exportAdmins() {
            return this._exportHelper(`${path}/admins`, 'Администраторы выгружены')
        },
        async exportUsers() {
            return this._exportHelper(`${path}/users`, 'Пользователи выгружены')
        },
        async exportProducts(type = 0) {
            return this._exportHelper(`${path}/products?type=${type}`, 'Продукты выгружены')
        },
        async exportFull(payload) {
            const alertStore = useAlertStore()
            let url = `${path}/full`
            alertStore.show( "Процесс генерации отчета запущен")
            this.loading = true
            this.error = null
            try {
                const { data } = await makeAxiosFactory(url, 'POST', payload)
                this.successMessage = 'Отчет сформирован'
                this.exportData = data
                alertStore.show( this.successMessage,"success")
                return data
            } catch (error: any) {
                this.error = error.response?.data?.message ?? 'Ошибка выгрузки'
                alertStore.show( this.error,"error")
                throw error
            } finally {
                this.loading = false
            }
        },
        async exportCategories() {
            return this._exportHelper(`${path}/categories`, 'Категории продуктов выгружены')
        },
        async exportClients() {
            return this._exportHelper(`${path}/clients`, 'Покупатели выгружены')
        },
        async exportSuppliers() {
            return this._exportHelper(`${path}/suppliers`, 'Поставщики выгружены')
        },
        async exportSalesHistory() {
            return this._exportHelper(`${path}/sales-history`, 'История продаж выгружена')
        },

        // 🔹 универсальный хелпер
        async _exportHelper(url: string, successMsg: string) {

            const alertStore = useAlertStore()
            alertStore.show( "Процесс генерации отчета запущен")
            this.loading = true
            this.error = null
            try {
                const { data } = await makeAxiosFactory(url, 'GET')
                this.successMessage = successMsg
                this.exportData = data
                alertStore.show( this.successMessage,"success")
                return data
            } catch (error: any) {
                this.error = error.response?.data?.message ?? 'Ошибка выгрузки'
                alertStore.show( this.error,"error")
                throw error
            } finally {
                this.loading = false
            }
        }
    }
})
