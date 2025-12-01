// stores/import.ts
import { defineStore } from 'pinia'
import axios, { AxiosError } from 'axios'

import { useAlertStore } from './utillites/useAlertStore'
interface ImportState {
    uploading: boolean
    message: string | null
    error: string | null
}

const path: string = '/bot-api/imports'

export const useImportStore = defineStore('import', {
    state: (): ImportState => ({
        uploading: false,
        message: null,
        error: null
    }),

    actions: {
        // @ts-ignore
        async uploadProductWithCategoriesExcel(file: File): Promise<void> {
            this.uploading = true
            this.message = null
            this.error = null

            const alertStore = useAlertStore()

            alertStore.show(   "Загрузка файла началась")
            try {
                const formData = new FormData()
                formData.append('excel', file)

                const { data } = await axios.post<{ message: string }>(
                    `${path}/import-products-with-categories`,
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                )

                this.message = data.message || 'Файл успешно загружен'
                alertStore.show(     this.message,"success")
            } catch (err) {
                const error = err as AxiosError<{ message?: string }>
                this.error = error.response?.data?.message || 'Ошибка при загрузке файла'
                alertStore.show( this.error,"error")
            } finally {
                this.uploading = false
            }
        }
    }
})
