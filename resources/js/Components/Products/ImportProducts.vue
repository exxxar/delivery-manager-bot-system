<template>
    <p class="alert alert-info">
       При импорте товаров будут созданы также категории и поставщики, которых еще нет в системе.
    </p>
    <div class="card mb-2">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">Импорт Excel-файла с продуктами</h6>
        </div>
        <div class="card-body">
            <form @submit.prevent="handleUpload">
                <div class="mb-3">
                    <input
                        type="file"
                        class="form-control"
                        @change="onFileChange"
                        accept=".xlsx,.xls"
                    />
                </div>
                <button
                    class="btn btn-success w-100 p-3 mb-2"
                    type="submit"
                    :disabled="store.uploading || !file"
                >
                    {{ store.uploading ? 'Загрузка...' : 'Загрузить' }}
                </button>
            </form>

            <div v-if="store.message" class="alert alert-success mb-0">
                {{ store.message }}
            </div>
            <div v-if="store.error" class="alert alert-danger mb-0">
                {{ store.error }}
            </div>
        </div>
    </div>

    <button
        type="button"
        :disabled="jobStore.loading"
        class="btn btn-outline-light text-primary w-100 p-3"
        @click="exportProducts(0)">
        Скачать пример
    </button>
</template>

<script>
import {useImportStore} from '@/stores/import'
import {useBaseExports} from "@/stores/baseExports";

export default {
    name: 'ImportProducts',
    data() {
        return {
            file: null,
            jobStore: useBaseExports(),
            store: useImportStore()
        }
    },
    methods: {
        onFileChange(e) {
            this.file = e.target.files[0]
        },
        exportProducts(type) {
            this.jobStore.exportProducts(type)
        },
        async handleUpload() {
            if (this.file) {
                await this.store.uploadProductWithCategoriesExcel(this.file)
            }
        }
    }
}
</script>
