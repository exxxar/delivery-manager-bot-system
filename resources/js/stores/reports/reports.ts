import { defineStore } from 'pinia'
import { makeAxiosFactory } from '../utillites/makeAxiosFactory'

const reportPath = '/admins/report-data'

function localDate(): string {
    const now = new Date()
    const year = now.getFullYear()
    const month = String(now.getMonth() + 1).padStart(2, '0')
    const day = String(now.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}

type ReportFilters = {
    startDate: string
    endDate: string
}

export const useReportsStore = defineStore('reports', {
    state: () => ({
        report: null as any,
        loading: false,
        error: null as string | null,
        filters: {
            startDate: localDate(),
            endDate: localDate(),
        } as ReportFilters,
        activeTab: 'summary' as string,
    }),

    getters: {
        hasReport: (state) => !!state.report,
        summary: (state) => state.report?.summary ?? {},
        agentReports: (state) => state.report?.agents ?? [],
        adminReports: (state) => state.report?.summary_sheet?.admins ?? [],
        supplierItems: (state) => state.report?.suppliers ?? [],
        productItems: (state) => state.report?.products ?? [],
    },

    actions: {
        async fetchReport() {
            this.loading = true
            this.error = null

            try {
                const payload = {
                    startDate: this.filters.startDate,
                    endDate: this.filters.endDate,
                }

                const { data } = await makeAxiosFactory(reportPath, 'POST', payload)
                this.report = data
                return data
            } catch (error: any) {
                this.error =
                    error?.response?.data?.message ||
                    error?.message ||
                    'Не удалось загрузить отчёт'
                throw error
            } finally {
                this.loading = false
            }
        },

        resetFilters() {
            const today = localDate()
            this.filters = {
                startDate: today,
                endDate: today,
            }
        },
    },
})
