import { defineStore } from 'pinia'
import axios from 'axios'
import { makeAxiosFactory } from './utillites/makeAxiosFactory'
import { useAlertStore } from './utillites/useAlertStore'
const path: string = '/bot-api/forms'

export const useJobStore = defineStore('job', {
    state: () => ({
        loading: false as boolean,
        error: null as string | null,
        successMessage: null as string | null
    }),
    actions: {
        async submitAdminForm(formData: any) {
            return this.submitForm(
                'admin-job',
                formData,
                'Заявка администратора отправлена',
                'Ошибка отправки заявки администратора'
            )
        },

        async submitAgentForm(formData: any) {
            return this.submitForm(
                'agent-job',
                formData,
                'Заявка младшего администратора отправлена',
                'Ошибка отправки заявки'
            )
        },

        async submitSupplierForm(formData: any) {
            return this.submitForm(
                'supplier-job',
                formData,
                'Заявка поставщика отправлена',
                'Ошибка отправки заявки поставщика'
            )
        },

        async submitClientForm(formData: any) {
            return this.submitForm(
                'client-job',
                formData,
                'Заявка клиента отправлена',
                'Ошибка отправки заявки клиента'
            )
        },
        async submitForm(endpoint: string, formData: any, successMsg: string, errorMsg: string) {
            this.loading = true
            this.error = null
            const alertStore = useAlertStore()
            try {
                const { data } = await makeAxiosFactory(`${path}/${endpoint}`, 'POST', formData)
                this.successMessage = data.message ?? successMsg
                alertStore.show( this.successMessage,"success")
                return data
            } catch (error: any) {
                this.error = error.response?.data?.message ?? errorMsg
                alertStore.show( this.error,"error")
                throw error
            } finally {
                this.loading = false
            }
        }
    }
})
