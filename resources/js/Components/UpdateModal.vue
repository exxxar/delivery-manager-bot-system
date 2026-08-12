<template>
    <div class="modal fade" id="updateModal" tabindex="-1"
         data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-arrow-up-from-bracket me-2"></i>
                        Доступно обновление
                    </h5>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fa-solid fa-cloud-arrow-down fs-1 text-primary mb-3"></i>

                    <p class="lead">{{ message }}</p>

                    <div v-if="forceUpdate" class="alert alert-warning">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <strong>Обновление обязательно!</strong>
                        <p class="mb-0 small">Работа в старой версии невозможна.</p>
                    </div>

                    <div class="alert alert-info mb-0">
                        <small>
                            <strong>Текущая версия:</strong> {{ localVersion }}<br>
                            <strong>Новая версия:</strong> {{ newVersion }}
                        </small>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button
                        type="button"
                        class="btn btn-primary btn-lg w-100"
                        @click="handleUpdate"
                        :disabled="updating"
                    >
                        <template v-if="updating">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Обновление...
                        </template>
                        <template v-else>
                            <i class="fa-solid fa-rotate me-2"></i>
                            Обновить сейчас
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { forceUpdate, setLocalVersion } from '@/utilites/versionCheck.js'

export default {
    name: 'UpdateModal',
    props: {
        newVersion: { type: String, required: true },
        localVersion: { type: String, required: true },
        message: {
            type: String,
            default: 'Доступна новая версия приложения'
        },
        forceUpdate: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            updating: false
        }
    },
    methods: {
        async handleUpdate() {
            this.updating = true
            // Сохраняем новую версию ДО перезагрузки
            setLocalVersion(this.newVersion)
            await forceUpdate()
        }
    }
}
</script>
