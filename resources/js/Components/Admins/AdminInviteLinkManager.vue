<template>
    <section class="invite-manager card border-0">
        <div class="card-body p-3 p-md-4">
            <div class="invite-heading d-flex align-items-start justify-content-between gap-3 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="heading-icon"><i class="fa-solid fa-link"></i></div>
                    <div>
                        <h5 class="mb-1">Ссылка для регистрации администраторов</h5>
                        <p class="text-muted small mb-0">Управление приглашениями и доступом к регистрации</p>
                    </div>
                </div>
                <span v-if="!loading && currentLink" class="status-pill status-active">
                    <span class="status-dot"></span> Активна
                </span>
            </div>

            <div v-if="loading" class="loading-state py-5 text-center" role="status" aria-live="polite">
                <span class="spinner-border text-primary mb-3" aria-hidden="true"></span>
                <div class="small text-muted">Загружаем данные приглашения…</div>
            </div>

            <template v-else>
                <template v-if="currentLink">
                    <div class="invite-alert d-flex align-items-start gap-3 mb-4">
                        <div class="alert-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">Ссылка приглашения активна</div>
                            <div class="small text-muted mt-1">Передавайте её только тем, кому разрешена регистрация администратора.</div>
                            <div class="expiry-line mt-2">
                                <i class="fa-regular fa-clock me-1"></i>
                                Истекает: <strong>{{ formatDate(currentLink.expires_at) }}</strong>
                            </div>
                        </div>
                    </div>

                    <label class="form-label field-label" for="admin-invite-link">Ссылка-приглашение</label>
                    <div class="invite-link-box mb-3">
                        <div class="link-icon"><i class="fa-solid fa-globe"></i></div>
                        <input
                            id="admin-invite-link"
                            ref="linkInput"
                            type="text"
                            class="form-control invite-link-input"
                            :value="currentLink.link"
                            readonly
                            aria-label="Ссылка для регистрации администратора"
                            @focus="$event.target.select()"
                        />
                        <button class="btn btn-primary copy-button" type="button" @click="copyLink">
                            <i class="fa-solid fa-copy me-2"></i><span>Копировать</span>
                        </button>
                    </div>

                    <div class="action-row d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary" @click="confirmRegenerate">
                            <i class="fa-solid fa-rotate me-2"></i>Перегенерировать
                        </button>
                        <button type="button" class="btn btn-outline-danger" @click="confirmRevoke">
                            <i class="fa-solid fa-ban me-2"></i>Отозвать ссылку
                        </button>
                    </div>
                    <div class="security-note mt-3">
                        <i class="fa-solid fa-circle-info me-2"></i>
                        При перегенерации прежняя ссылка сразу перестанет работать.
                    </div>
                </template>

                <template v-else>
                    <div class="empty-state text-center py-4 px-3">
                        <div class="empty-icon mb-3"><i class="fa-solid fa-link-slash"></i></div>
                        <h6 class="mb-2">Активной ссылки пока нет</h6>
                        <p class="text-muted small mb-4">Создайте приглашение, чтобы разрешить регистрацию нового администратора.</p>
                        <button type="button" class="btn btn-primary px-4" @click="generateLink">
                            <i class="fa-solid fa-plus me-2"></i>Сгенерировать ссылку
                        </button>
                    </div>
                </template>
            </template>
        </div>
    </section>
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
                const input = this.$refs.linkInput
                if (input) {
                    input.focus()
                    input.select()
                    const copied = document.execCommand('copy')
                    if (copied) {
                        this.alertStore.show('Ссылка скопирована', 'success')
                        return
                    }
                }
                this.alertStore.show('Не удалось скопировать ссылку. Выделите её и скопируйте вручную.', 'warning')
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
    box-shadow: 0 8px 28px rgba(23, 43, 77, 0.07);
}
.invite-heading h5 { color: #172b4d; font-size: 1.02rem; font-weight: 750; letter-spacing: -0.02em; }
.invite-heading p { line-height: 1.5; }
.heading-icon, .empty-icon {
    display: grid;
    flex: 0 0 auto;
    place-items: center;
    color: #fff;
    background: linear-gradient(135deg, #1769d2, #438cf4);
    box-shadow: 0 6px 16px rgba(23, 105, 210, .2);
}
.heading-icon { width: 44px; height: 44px; border-radius: 14px; font-size: 1.05rem; }
.status-pill { display: inline-flex; align-items: center; gap: 7px; padding: 7px 11px; border-radius: 999px; font-size: .75rem; font-weight: 700; white-space: nowrap; }
.status-active { color: #137547; background: #e8f7ef; }
.status-dot { width: 7px; height: 7px; border-radius: 50%; background: #1fa66a; box-shadow: 0 0 0 3px rgba(31, 166, 106, .12); }
.invite-alert { padding: 16px; border: 1px solid #f2dfaa; border-radius: 14px; background: #fff9e9; }
.alert-icon { display: grid; flex: 0 0 auto; place-items: center; width: 38px; height: 38px; color: #9b6a00; background: #ffefbf; border-radius: 11px; }
.expiry-line { color: #735d2a; font-size: .82rem; }
.field-label { color: #344563; font-size: .82rem; font-weight: 700; }
.invite-link-box { display: flex; align-items: center; gap: 10px; padding: 7px; border: 1px solid #dce5f0; border-radius: 13px; background: #f8faff; }
.link-icon { display: grid; flex: 0 0 auto; place-items: center; width: 36px; height: 36px; color: #1769d2; background: #e8f1ff; border-radius: 10px; }
.invite-link-input { min-width: 0; padding: 9px 5px; color: #40536e; background: transparent; border: 0; box-shadow: none !important; font-family: var(--bs-font-monospace); font-size: .85rem; }
.invite-link-input:focus { background: transparent; }
.btn { border-radius: 10px; font-size: .84rem; font-weight: 650; padding: 9px 13px; transition: transform .18s ease, box-shadow .18s ease, background .18s ease; }
.btn-primary { background: linear-gradient(135deg, #1769d2, #347fe8); border-color: #1769d2; box-shadow: 0 4px 10px rgba(23, 105, 210, .15); }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 7px 15px rgba(23, 105, 210, .22); }
.copy-button { flex: 0 0 auto; white-space: nowrap; }
.btn-outline-primary, .btn-outline-danger { background: #fff; }
.security-note { display: flex; align-items: flex-start; color: #687b95; font-size: .8rem; line-height: 1.5; }
.security-note i { margin-top: 3px; color: #347fe8; }
.empty-state { border: 1px dashed #d8e2ef; border-radius: 15px; background: #f9fbff; }
.empty-icon { width: 58px; height: 58px; margin-inline: auto; border-radius: 18px; font-size: 1.25rem; background: #e8f1ff; color: #1769d2; box-shadow: none; }
.empty-state h6 { font-weight: 750; color: #172b4d; }
.empty-state p { max-width: 390px; margin-inline: auto; line-height: 1.6; }
.loading-state .spinner-border { width: 2rem; height: 2rem; }
@media (max-width: 575.98px) {
    .invite-heading { flex-wrap: wrap; }
    .invite-heading > .status-pill { margin-left: 57px; }
    .invite-link-box { flex-wrap: wrap; }
    .invite-link-input { flex: 1 1 calc(100% - 50px); }
    .copy-button { width: 100%; }
    .action-row .btn { flex: 1 1 100%; }
}
</style>
