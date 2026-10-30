<template>
    <section class="magic-link-manager">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="magic-icon"><i class="fa-solid fa-link"></i></div>
            <div class="flex-grow-1">
                <h6 class="mb-1">Персональная ссылка для входа</h6>
                <p class="text-muted small mb-0">
                    {{ agent?.name || 'Администратор' }} сможет войти без ввода логина и пароля.
                </p>
            </div>
        </div>

        <div v-if="link" class="link-panel mb-3">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <strong class="small">Ссылка создана</strong>
                <span class="status-pill"><i class="fa-solid fa-clock"></i> 15 минут</span>
            </div>
            <div class="input-group">
                <input ref="linkInput" class="form-control" :value="link" readonly @focus="$event.target.select()">
                <button class="btn btn-primary" type="button" :disabled="busy" @click="copyLink">
                    <i class="fa-regular fa-copy me-1"></i> Копировать
                </button>
            </div>
            <p class="small text-muted mt-2 mb-0">
                Ссылка одноразовая. После успешного входа повторно использовать её нельзя.
            </p>
        </div>

        <div v-else class="empty-panel mb-3">
            <i class="fa-solid fa-shield-halved"></i>
            <div class="small mt-2">Ссылка ещё не создана. Создайте её непосредственно перед передачей сотруднику.</div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-primary" type="button" :disabled="busy || !agent?.id" @click="createLink">
                <span v-if="busy" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="fa-solid fa-wand-magic-sparkles me-2"></i>
                {{ link ? 'Создать новую ссылку' : 'Создать ссылку' }}
            </button>
            <button v-if="link" class="btn btn-outline-danger" type="button" :disabled="busy" @click="revokeLink">
                <i class="fa-solid fa-ban me-1"></i> Отозвать
            </button>
        </div>

        <div class="security-note mt-3">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            Любой, у кого есть ссылка, сможет войти в аккаунт этого администратора. Передавайте её только самому сотруднику.
        </div>
    </section>
</template>

<script>
import { makeAxiosFactory } from '@/stores/utillites/makeAxiosFactory'
import { useAlertStore } from '@/stores/utillites/useAlertStore'

export default {
    name: 'AgentMagicLinkManager',
    props: {
        agent: { type: Object, required: true },
    },
    data() {
        return {
            busy: false,
            link: null,
            alertStore: useAlertStore(),
        }
    },
    methods: {
        async createLink() {
            if (!this.agent?.id || this.busy) return
            this.busy = true
            try {
                const { data } = await makeAxiosFactory(`/agents/${this.agent.id}/login-link`, 'POST')
                this.link = data.link
                this.alertStore.show('Ссылка для входа создана', 'success')
            } catch (error) {
                this.alertStore.show(
                    error?.response?.data?.message || 'Не удалось создать ссылку для входа',
                    'danger'
                )
            } finally {
                this.busy = false
            }
        },

        async copyLink() {
            if (!this.link) return
            try {
                await navigator.clipboard.writeText(this.link)
                this.alertStore.show('Ссылка скопирована', 'success')
            } catch {
                const input = this.$refs.linkInput
                if (input) {
                    input.focus()
                    input.select()
                    if (document.execCommand('copy')) {
                        this.alertStore.show('Ссылка скопирована', 'success')
                        return
                    }
                }
                this.alertStore.show('Не удалось скопировать ссылку. Скопируйте её вручную.', 'warning')
            }
        },

        async revokeLink() {
            if (!this.agent?.id || this.busy) return
            if (!window.confirm(`Отозвать активные ссылки для входа пользователя ${this.agent.name || ''}?`)) return

            this.busy = true
            try {
                await makeAxiosFactory(`/agents/${this.agent.id}/login-link`, 'DELETE')
                this.link = null
                this.alertStore.show('Ссылка отозвана', 'success')
            } catch (error) {
                this.alertStore.show(
                    error?.response?.data?.message || 'Не удалось отозвать ссылку',
                    'danger'
                )
            } finally {
                this.busy = false
            }
        },
    },
}
</script>

<style scoped>
.magic-link-manager { color: #172d50; }
.magic-icon { width: 42px; height: 42px; flex: 0 0 42px; display:grid; place-items:center; border-radius:13px; color:#2457a7; background:#e8f0ff; }
.magic-link-manager h6 { font-weight:750; }
.link-panel,.empty-panel { padding:14px; border:1px solid #e2eaf5; border-radius:14px; background:#f8faff; }
.empty-panel { text-align:center; color:#8290a5; }
.empty-panel > i { font-size:24px; color:#6688bb; }
.status-pill { display:inline-flex; align-items:center; gap:5px; padding:5px 9px; border-radius:999px; color:#8b650d; background:#fff2cc; font-size:11px; font-weight:700; white-space:nowrap; }
.security-note { padding:11px 12px; border-radius:11px; background:#fff8e8; color:#80621f; font-size:12px; line-height:1.5; }
</style>
