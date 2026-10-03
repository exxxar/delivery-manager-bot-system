<template>
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fa-solid fa-link text-primary me-2"></i>
                Ссылка для регистрации администраторов
            </h6>
        </div>
        <div class="card-body">
            <div v-if="loading" class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary"></div>
            </div>

            <template v-else>
                <template v-if="currentLink">
                    <div class="alert alert-warning mb-3">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <strong>Активная ссылка приглашения.</strong>
                        <small class="d-block mt-1">
                            Истекает: {{ formatDate(currentLink.expires_at) }}
                        </small>
                    </div>

                    <div class="input-group mb-3">
                        <input
                            type="text"
                            class="form-control font-monospace small"
                            :value="currentLink.link"
                            readonly
                            ref="linkInput"
                        />
                        <button
                            class="btn btn-primary"
                            type="button"
                            @click="copyLink"
                        >
                            <i class="fa-solid fa-copy me-1"></i>
                            Копировать
                        </button>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button
                            type="button"
                            class="btn btn-outline-warning"
                            @click="confirmRegenerate"
                        >
                            <i class="fa-solid fa-rotate me-1"></i>
                            Перегенерировать
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-danger"
                            @click="confirmRevoke"
                        >
                            <i class="fa-solid fa-ban me-1"></i>
                            Отозвать
                        </button>
                    </div>

                    <small class="text-muted d-block mt-2">
                        <i class="fa-solid fa-info-circle me-1"></i>
                        При перегенерации старая ссылка перестанет работать.
                    </small>
                </template>

                <template v-else>
                    <div class="alert alert-info mb-3">
                        <i class="fa-solid fa-circle-info me-2"></i>
                        Нет активной ссылки для регистрации. Сгенерируйте новую.
                    </div>

                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="generateLink"
                    >
                        <i class="fa-solid fa-plus me-1"></i>
                        Сгенерировать ссылку
                    </button>
                </template>
            </template>
        </div>
    </div>
</template>

<script>
import { makeAxiosFactory } from '@/stores/utillites/makeAxiosFactory'
import { useAlertStore } from '@/stores/utillites/useAlertStore'
import { useModalStore } from '@/stores/utillites/useConfitmModalStore'

export default {
    name: 'AdminInviteLinkManager',
    data() {
        return {
            loading: false,
            currentLink: null,
            alertStore: useAlertStore(),
            modalStore: useModalStore(),
        }
    },
    created() {
        this.loadCurrent()
    },
    methods: {
        async loadCurrent() {
            this.loading = true
            try {
                const { data } = await makeAxiosFactory('/admin-invite/current', 'GET')
                this.currentLink = data.has_link ? data : null
            } catch (e) {
                console.error('Ошибка загрузки ссылки:', e)
            } finally {
                this.loading = false
            }
        },

        async generateLink() {
            this.loading = true
            try {
                const { data } = await makeAxiosFactory('/admin-invite/generate', 'POST')
                this.currentLink = data
            } catch (e) {
                console.error('Ошибка генерации ссылки:', e)
            } finally {
                this.loading = false
            }
        },

        confirmRegenerate() {
            this.modalStore.open(
                'Перегенерировать ссылку? Текущая ссылка перестанет работать, будет создана новая.',
                async () => {
                    await this.generateLink()
                    this.modalStore.close()
                },
                () => this.modalStore.close()
            )
        },

        confirmRevoke() {
            this.modalStore.open(
                'Отозвать ссылку? Больше никто не сможет по ней зарегистрироваться.',
                async () => {
                    this.loading = true
                    try {
                        await makeAxiosFactory('/admin-invite/revoke', 'POST')
                        this.currentLink = null
                    } catch (e) {
                        console.error('Ошибка отзыва ссылки:', e)
                    } finally {
                        this.loading = false
                    }
                    this.modalStore.close()
                },
                () => this.modalStore.close()
            )
        },

        async copyLink() {
            try {
                await navigator.clipboard.writeText(this.currentLink.link)
                this.alertStore.show('Ссылка скопирована', 'success')
            } catch (e) {
                this.$refs.linkInput.select()
                document.execCommand('copy')
                this.alertStore.show('Ссылка скопирована', 'success')
            }
        },

        formatDate(dateString) {
            if (!dateString) return '—'
            try {
                return new Date(dateString).toLocaleString('ru-RU', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                })
            } catch {
                return dateString
            }
        },
    },
}
</script>
