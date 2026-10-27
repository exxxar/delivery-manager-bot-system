<template>
    <section class="invite-manager card border-0">
        <div class="card-body p-3 p-md-4">
            <div class="invite-heading d-flex align-items-start gap-3 mb-4">
                <div class="invite-heading-icon flex-shrink-0" aria-hidden="true">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="invite-eyebrow">УПРАВЛЕНИЕ ДОСТУПОМ</div>
                    <h5 class="invite-title mb-1">Регистрация поставщиков</h5>
                    <p class="invite-subtitle mb-0">Создавайте и управляйте ссылкой-приглашением для новых поставщиков.</p>
                </div>
                <span v-if="!loading && currentLink" class="invite-status status-active">
                    <span class="status-dot"></span> Активна
                </span>
            </div>

            <div v-if="loading" class="invite-loading py-5 text-center" role="status" aria-live="polite">
                <span class="spinner-border text-primary mb-3" aria-hidden="true"></span>
                <div class="loading-title">Обновляем данные</div>
                <div class="loading-caption">Пожалуйста, подождите…</div>
            </div>

            <template v-else>
                <div v-if="currentLink" class="invite-content">
                    <div class="invite-expiry d-flex align-items-start gap-3 mb-3">
                        <div class="expiry-icon flex-shrink-0"><i class="fa-regular fa-clock"></i></div>
                        <div class="flex-grow-1">
                            <div class="expiry-title">Ссылка действует</div>
                            <div class="expiry-description">Срок действия ограничен — проверьте дату окончания перед отправкой.</div>
                            <div class="expiry-date mt-2">
                                <i class="fa-regular fa-calendar me-2"></i>{{ formatDate(currentLink.expires_at) }}
                            </div>
                        </div>
                    </div>

                    <label class="field-label" for="supplier-invite-link">Ссылка для приглашения</label>
                    <div class="invite-link-field input-group mb-4">
                        <span class="input-group-text" aria-hidden="true"><i class="fa-solid fa-link"></i></span>
                        <input
                            id="supplier-invite-link"
                            type="text"
                            class="form-control font-monospace"
                            :value="currentLink.link"
                            readonly
                            ref="linkInput"
                            aria-label="Ссылка для регистрации поставщика"
                            @focus="$event.target.select()"
                        />
                        <button class="btn btn-copy" type="button" @click="copyLink">
                            <i class="fa-regular fa-copy me-sm-2"></i><span>Копировать</span>
                        </button>
                    </div>

                    <div class="invite-actions d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-action btn-regenerate" @click="confirmRegenerate">
                            <i class="fa-solid fa-rotate me-2"></i>Перегенерировать
                        </button>
                        <button type="button" class="btn btn-action btn-revoke" @click="confirmRevoke">
                            <i class="fa-solid fa-ban me-2"></i>Отозвать ссылку
                        </button>
                    </div>
                    <p class="invite-footnote mt-3 mb-0">
                        <i class="fa-solid fa-shield-halved me-2"></i>
                        При перегенерации или отзыве старая ссылка перестанет работать.
                    </p>
                </div>

                <div v-else class="invite-empty text-center">
                    <div class="empty-icon mx-auto mb-3"><i class="fa-solid fa-link-slash"></i></div>
                    <h6 class="empty-title">Активной ссылки пока нет</h6>
                    <p class="empty-description mx-auto mb-4">Сгенерируйте ссылку и отправьте её поставщику для регистрации в системе.</p>
                    <button type="button" class="btn btn-generate" @click="generateLink">
                        <i class="fa-solid fa-plus me-2"></i>Сгенерировать ссылку
                    </button>
                </div>
            </template>
        </div>
    </section>
</template>

<script>
import { makeAxiosFactory } from '@/stores/utillites/makeAxiosFactory'
import { useAlertStore } from '@/stores/utillites/useAlertStore'
import { useModalStore } from '@/stores/utillites/useConfitmModalStore'

export default {
    name: 'SupplierInviteLinkManager',
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
                const { data } = await makeAxiosFactory('/supplier-invite/current', 'GET')
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
                const { data } = await makeAxiosFactory('/supplier-invite/generate', 'POST')
                this.currentLink = data
            } catch (e) {
                console.error('Ошибка генерации ссылки:', e)
            } finally {
                this.loading = false
            }
        },

        confirmRegenerate() {
            this.modalStore.open(
                'Перегенерировать ссылку? Текущая ссылка перестанет работать.',
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
                        await makeAxiosFactory('/supplier-invite/revoke', 'POST')
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

<style scoped>
.invite-manager {
    overflow: hidden;
    color: #172b4d;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 28px rgba(22, 43, 77, 0.07);
}

.invite-heading-icon {
    display: grid;
    place-items: center;
    width: 48px;
    height: 48px;
    color: #fff;
    font-size: 1.2rem;
    background: linear-gradient(145deg, #1769e0, #0b3d91);
    border-radius: 15px;
    box-shadow: 0 6px 14px rgba(23, 105, 224, 0.2);
}

.invite-eyebrow {
    margin-bottom: 5px;
    color: #1769e0;
    font-size: 0.67rem;
    font-weight: 800;
    letter-spacing: 0.12em;
}

.invite-title {
    color: #172b4d;
    font-size: 1.08rem;
    font-weight: 750;
    letter-spacing: -0.02em;
}

.invite-subtitle,
.empty-description {
    color: #718096;
    font-size: 0.88rem;
    line-height: 1.55;
}

.invite-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 10px;
    color: #147d4a;
    font-size: 0.75rem;
    font-weight: 700;
    white-space: nowrap;
    background: #eaf8f0;
    border-radius: 999px;
}

.status-dot {
    width: 7px;
    height: 7px;
    background: #20a464;
    border-radius: 50%;
    box-shadow: 0 0 0 3px rgba(32, 164, 100, 0.12);
}

.invite-expiry {
    padding: 16px;
    background: #fff8eb;
    border: 1px solid #ffe4b4;
    border-radius: 14px;
}

.expiry-icon {
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    color: #b66a00;
    background: #ffedca;
    border-radius: 11px;
}

.expiry-title {
    color: #7a4b08;
    font-weight: 750;
}

.expiry-description {
    margin-top: 3px;
    color: #8b6a3c;
    font-size: 0.82rem;
    line-height: 1.45;
}

.expiry-date {
    color: #754b13;
    font-size: 0.88rem;
    font-weight: 700;
}

.field-label {
    display: block;
    margin-bottom: 8px;
    color: #344563;
    font-size: 0.82rem;
    font-weight: 700;
}

.invite-link-field .input-group-text,
.invite-link-field .form-control {
    min-height: 46px;
    color: #344563;
    background: #f8faff;
    border-color: #dfe7f2;
}

.invite-link-field .input-group-text {
    color: #1769e0;
    border-radius: 11px 0 0 11px;
}

.invite-link-field .form-control {
    min-width: 0;
    font-size: 0.78rem;
    box-shadow: none;
}

.invite-link-field .form-control:focus {
    border-color: #7eaff7;
    box-shadow: 0 0 0 3px rgba(23, 105, 224, 0.1);
}

.btn-copy,
.btn-generate {
    color: #fff;
    font-weight: 700;
    background: #1769e0;
    border-color: #1769e0;
    transition: background 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
}

.btn-copy {
    border-radius: 0 11px 11px 0;
}

.btn-copy:hover,
.btn-generate:hover {
    color: #fff;
    background: #0d52b8;
    border-color: #0d52b8;
    box-shadow: 0 5px 14px rgba(23, 105, 224, 0.2);
    transform: translateY(-1px);
}

.btn-action {
    min-height: 42px;
    padding: 10px 14px;
    font-size: 0.85rem;
    font-weight: 700;
    border-radius: 10px;
    transition: all 0.18s ease;
}

.btn-regenerate {
    color: #1769e0;
    background: #eff6ff;
    border: 1px solid #d7e7ff;
}

.btn-regenerate:hover {
    color: #fff;
    background: #1769e0;
    border-color: #1769e0;
}

.btn-revoke {
    color: #c63745;
    background: #fff1f2;
    border: 1px solid #ffd9dd;
}

.btn-revoke:hover {
    color: #fff;
    background: #c63745;
    border-color: #c63745;
}

.invite-footnote {
    color: #8290a5;
    font-size: 0.77rem;
    line-height: 1.5;
}

.invite-footnote i {
    color: #1769e0;
}

.invite-empty {
    padding: 22px 10px 18px;
}

.empty-icon {
    display: grid;
    place-items: center;
    width: 68px;
    height: 68px;
    color: #1769e0;
    font-size: 1.6rem;
    background: #edf5ff;
    border: 1px solid #dceaff;
    border-radius: 22px;
}

.empty-title {
    color: #172b4d;
    font-size: 1rem;
    font-weight: 750;
}

.empty-description {
    max-width: 390px;
}

.btn-generate {
    min-height: 45px;
    padding: 11px 19px;
    border-radius: 11px;
}

.invite-loading .spinner-border {
    width: 2rem;
    height: 2rem;
}

.loading-title {
    color: #344563;
    font-weight: 700;
}

.loading-caption {
    margin-top: 4px;
    color: #8290a5;
    font-size: 0.83rem;
}

@media (max-width: 575.98px) {
    .invite-heading {
        flex-wrap: wrap;
    }

    .invite-status {
        margin-left: 60px;
    }

    .invite-link-field {
        flex-wrap: wrap;
    }

    .invite-link-field .input-group-text {
        border-radius: 10px 0 0 10px;
    }

    .invite-link-field .form-control {
        width: calc(100% - 42px);
        border-radius: 0 10px 10px 0;
    }

    .invite-link-field .btn-copy {
        width: 100%;
        margin-top: 8px;
        border-radius: 10px;
    }

    .invite-actions .btn {
        flex: 1 1 100%;
    }
}
</style>
