import { defineStore } from 'pinia'
import { makeAxiosFactory } from './utillites/makeAxiosFactory'

export interface Report {
    id: number
    user_id: number
    title: string
    file_name: string
    file_path: string
    report_type: string | null
    start_date: string | null
    end_date: string | null
    file_size: number
    created_at: string
    updated_at: string
}

const path: string = '/reports'

export const useReportsStore = defineStore('reports', {
    state: () => ({
        items: [] as Report[],
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
                this.error = e?.message || 'Не удалось загрузить отчеты'
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
                this.error = e?.message || 'Не удалось загрузить отчеты'
            } finally {
                this.loading = false
            }
        },
        async download(reportId: number) {
            try {
                const response = await makeAxiosFactory(`${path}/${reportId}/download`, 'GET', null, {
                    responseType: 'blob'
                })

                // Создаем ссылку для скачивания
                const url = window.URL.createObjectURL(new Blob([response.data]))
                const link = document.createElement('a')
                link.href = url
                link.setAttribute('download', this.items.find(r => r.id === reportId)?.file_name || 'report.xlsx')
                document.body.appendChild(link)
                link.click()
                link.remove()
                window.URL.revokeObjectURL(url)

                return true
            } catch (e: any) {
                this.error = e?.message || 'Не удалось скачать отчет'
                throw e
            }
        },
        async remove(id: number) {
            try {
                await makeAxiosFactory(`${path}/${id}`, 'DELETE')
                this.items = this.items.filter(r => r.id !== id)
                return true
            } catch (e: any) {
                this.error = e?.message || 'Не удалось удалить отчет'
                throw e
            }
        },
        formatFileSize(bytes: number): string {
            if (bytes === 0) return '0 Bytes'
            const k = 1024
            const sizes = ['Bytes', 'KB', 'MB', 'GB']
            const i = Math.floor(Math.log(bytes) / Math.log(k))
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
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
        },
        getReportTypeLabel(type: string | null): string {
            const types: Record<string, string> = {
                'sales_period': 'Продажи за период',
                'sales_year': 'Продажи за год',
                'salary_period': 'Зарплаты за период',
                'salary_report': 'Отчет по зарплатам',
                'suppliers_period': 'Поставщики за период',
                'suppliers_year': 'Поставщики за год',
                'suppliers_report': 'Отчет по поставщикам',
                'full_salary_report': 'Полный отчет по зарплатам',
                'full_suppliers_report': 'Полный отчет по поставщикам',
                'individual_sales': 'Индивидуальные продажи',
                'sales_history': 'История продаж',
                'suppliers_list': 'Список поставщиков',
                'customers_list': 'Список клиентов',
                'categories_list': 'Список категорий',
                'products_summary': 'Сводная таблица продуктов',
                'products_by_category': 'Продукты по категориям',
                'products_by_supplier': 'Продукты по поставщикам',
                'users_list': 'Список пользователей',
                'admins_list': 'Список администраторов',
                'agents_list': 'Список агентов',
                'birthdays': 'Дни рождения',
            }
            return type ? (types[type] || type) : 'Отчет'
        }
    },
})
