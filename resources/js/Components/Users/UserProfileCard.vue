<template>
    <article class="user-profile-card" :class="{ 'is-expanded': !minimize }">
        <div class="profile-card-accent"></div>

        <div class="profile-card-body">
            <button
                type="button"
                class="profile-heading"
                :aria-expanded="String(!minimize)"
                @click="minimize = !minimize"
            >
                <span class="profile-avatar" aria-hidden="true">
                    <i class="fa-solid fa-user"></i>
                </span>
                <span class="profile-heading-text">
                    <span class="profile-name">{{ user.name }}</span>
                    <span class="profile-role" :class="roleClass(user.role)">{{ roleLabel(user.role) }}</span>
                </span>
                <span class="expand-indicator" aria-hidden="true">
                    <i :class="minimize ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-up'"></i>
                </span>
            </button>

            <transition name="profile-details">
                <div v-if="!minimize" class="profile-details">
                    <div class="detail-row">
                        <span class="detail-icon"><i class="fa-brands fa-telegram"></i></span>
                        <div class="detail-content">
                            <span class="detail-label">Имя в Telegram</span>
                            <span class="detail-value">{{ user.fio_from_telegram || '—' }}</span>
                        </div>
                    </div>

                    <div v-if="user.id" class="detail-row">
                        <span class="detail-icon"><i class="fa-solid fa-hashtag"></i></span>
                        <div class="detail-content">
                            <span class="detail-label">ID в системе</span>
                            <span class="detail-value">{{ user.id }}</span>
                        </div>
                    </div>

                    <div v-if="user.telegram_chat_id" class="detail-row">
                        <span class="detail-icon"><i class="fa-solid fa-comment-dots"></i></span>
                        <div class="detail-content">
                            <span class="detail-label">Telegram Chat ID</span>
                            <span class="detail-value">{{ user.telegram_chat_id }}</span>
                        </div>
                    </div>

                    <template v-if="user.role >= 3">
                        <div class="detail-row">
                            <span class="detail-icon"><i class="fa-solid fa-percent"></i></span>
                            <div class="detail-content">
                                <span class="detail-label">Админский процент</span>
                                <span class="detail-value percent-value">{{ user.percent ?? 0 }}%</span>
                            </div>
                        </div>

                        <div class="work-status-panel" :class="{ 'is-working': user.is_work }">
                            <div class="work-status-copy">
                                <span class="work-status-title">Рабочий статус</span>
                                <span class="work-status-description">
                                    {{ user.is_work ? 'Вы отмечены как работающий' : 'Вы отмечены как неработающий' }}
                                </span>
                            </div>
                            <div class="form-check form-switch work-switch mb-0">
                                <input
                                    @change="changeAdminWorkStatus(user)"
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    v-model="user.is_work"
                                    :id="`is-work-${user.id}`"
                                />
                                <label class="visually-hidden" :for="`is-work-${user.id}`">Рабочий статус</label>
                            </div>
                        </div>
                    </template>

                    <div class="profile-actions">
                        <button
                            v-if="canViewMyIncome"
                            type="button"
                            class="profile-action-button my-income-button"
                            @click.stop="openMyIncome"
                        >
                            <i class="fa-solid fa-wallet"></i>
                            <span>Мой доход</span>
                            <i class="fa-solid fa-chevron-right action-arrow"></i>
                        </button>

                        <button type="button" class="change-password-button" @click.stop="openPasswordModal">
                            <i class="fa-solid fa-key"></i>
                            <span>Сменить пароль</span>
                            <i class="fa-solid fa-chevron-right action-arrow"></i>
                        </button>
                    </div>
                </div>
            </transition>
        </div>


    </article>

    <Teleport to="body">
        <transition name="password-modal">
            <div v-if="showPasswordModal" class="password-modal-backdrop" @click.self="closePasswordModal" @keydown.esc="closePasswordModal">
                <section class="password-modal" role="dialog" aria-modal="true" aria-labelledby="password-modal-title">
                    <div class="password-modal-header">
                        <span class="password-modal-icon"><i class="fa-solid fa-shield-halved"></i></span>
                        <button type="button" class="password-modal-close" aria-label="Закрыть" @click="closePasswordModal">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <h2 id="password-modal-title">Смена пароля</h2>
                        <p>Установите новый пароль для своей учётной записи</p>
                    </div>

                    <form class="password-form" @submit.prevent="submitPasswordChange">
                        <div class="password-field">
                            <label for="current-password">Текущий пароль</label>
                            <div class="password-input-wrap">
                                <i class="fa-solid fa-lock"></i>
                                <input id="current-password" v-model="passwordForm.current_password" :type="showCurrentPassword ? 'text' : 'password'" autocomplete="current-password" required />
                                <button type="button" class="password-visibility" :aria-label="showCurrentPassword ? 'Скрыть пароль' : 'Показать пароль'" @click="showCurrentPassword = !showCurrentPassword">
                                    <i :class="showCurrentPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                </button>
                            </div>
                        </div>

                        <div class="password-field">
                            <label for="new-password">Новый пароль</label>
                            <div class="password-input-wrap">
                                <i class="fa-solid fa-key"></i>
                                <input id="new-password" v-model="passwordForm.password" :type="showNewPassword ? 'text' : 'password'" autocomplete="new-password" minlength="6" required />
                                <button type="button" class="password-visibility" :aria-label="showNewPassword ? 'Скрыть пароль' : 'Показать пароль'" @click="showNewPassword = !showNewPassword">
                                    <i :class="showNewPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                </button>
                            </div>
                            <span class="password-hint">Не менее 6 символов</span>
                        </div>

                        <div class="password-field">
                            <label for="confirm-password">Повторите новый пароль</label>
                            <div class="password-input-wrap">
                                <i class="fa-solid fa-check-double"></i>
                                <input id="confirm-password" v-model="passwordForm.password_confirmation" :type="showNewPassword ? 'text' : 'password'" autocomplete="new-password" minlength="6" required />
                            </div>
                            <span v-if="passwordForm.password_confirmation && passwordForm.password !== passwordForm.password_confirmation" class="password-inline-error">Пароли не совпадают</span>
                        </div>

                        <div v-if="passwordError" class="password-alert password-alert-error" role="alert">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span>{{ passwordError }}</span>
                        </div>
                        <div v-if="passwordSuccess" class="password-alert password-alert-success" role="status">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>{{ passwordSuccess }}</span>
                        </div>

                        <div class="password-modal-actions">
                            <button type="button" class="password-cancel-button" :disabled="passwordSaving" @click="closePasswordModal">Отмена</button>
                            <button type="submit" class="password-submit-button" :disabled="passwordSaving || !canSubmitPassword">
                                <i v-if="passwordSaving" class="fa-solid fa-spinner fa-spin"></i>
                                <i v-else class="fa-solid fa-shield-halved"></i>
                                {{ passwordSaving ? 'Сохранение…' : 'Сменить пароль' }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </transition>
    </Teleport>
</template>

<script>
import { useUsersStore } from "@/stores/users";
import axios from "axios";

export default {
    name: 'UserProfileCard',
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            userStore: useUsersStore(),
            minimize: true,
            showPasswordModal: false,
            passwordSaving: false,
            passwordError: '',
            passwordSuccess: '',
            showCurrentPassword: false,
            showNewPassword: false,
            passwordForm: {
                current_password: '',
                password: '',
                password_confirmation: ''
            }
        };
    },
    computed: {
        canViewMyIncome() {
            return Number(this.user?.role) >= 1;
        },
        canSubmitPassword() {
            return Boolean(
                this.passwordForm.current_password &&
                this.passwordForm.password.length >= 6 &&
                this.passwordForm.password_confirmation &&
                this.passwordForm.password === this.passwordForm.password_confirmation
            );
        }
    },
    methods: {
        openMyIncome() {
            const role = Number(this.user?.role);

            let routeName = null;

            if (role === 2) {
                routeName = 'supplier-my-income';
            } else if (role >= 1) {
                routeName = 'admin-my-income';
            }

            if (!routeName) return;

            // Закрываем боковое меню, если карточка находится внутри него
            const sidebar = document.getElementById('sidebar-menu');

            if (sidebar && window.bootstrap?.Offcanvas) {
                window.bootstrap.Offcanvas
                    .getOrCreateInstance(sidebar)
                    .hide();
            }

            // Переходим на страницу доходов
            this.$router.push({ name: routeName });
        },
        openPasswordModal() {
            this.passwordError = '';
            this.passwordSuccess = '';
            this.showPasswordModal = true;
        },
        closePasswordModal() {
            if (this.passwordSaving) return;
            this.showPasswordModal = false;
            this.passwordForm = {
                current_password: '',
                password: '',
                password_confirmation: ''
            };
            this.passwordError = '';
            this.passwordSuccess = '';
            this.showCurrentPassword = false;
            this.showNewPassword = false;
        },
        async submitPasswordChange() {
            if (!this.canSubmitPassword || this.passwordSaving) return;

            this.passwordSaving = true;
            this.passwordError = '';
            this.passwordSuccess = '';

            try {
                const response = await axios.post('/api/auth/change-password', {
                    current_password: this.passwordForm.current_password,
                    password: this.passwordForm.password,
                    password_confirmation: this.passwordForm.password_confirmation
                });
                this.passwordSuccess = response?.data?.message || 'Пароль успешно изменён.';
                this.passwordForm.current_password = '';
                this.passwordForm.password = '';
                this.passwordForm.password_confirmation = '';
                window.setTimeout(() => {
                    this.showPasswordModal = false;
                    this.passwordSuccess = '';
                }, 1000);
            } catch (error) {
                const data = error?.response?.data;
                const validationErrors = data?.errors;
                if (validationErrors) {
                    const firstError = Object.values(validationErrors).flat()[0];
                    this.passwordError = firstError || data?.message || 'Не удалось изменить пароль.';
                } else {
                    this.passwordError = data?.message || 'Не удалось изменить пароль. Проверьте текущий пароль и повторите попытку.';
                }
            } finally {
                this.passwordSaving = false;
            }
        },
        async changeAdminWorkStatus(user) {
            await this.userStore.updateWorkStatus(user.id, user.is_work);
        },
        roleLabel(role) {
            const roles = {
                0: 'Пользователь',
                1: 'Администратор',
                2: 'Поставщик',
                3: 'Старший администратор',
                4: 'Суперадмин'
            };
            return roles[role] || 'Неизвестно';
        },
        roleClass(role) {
            if (role >= 4) return 'role-superadmin';
            if (role >= 3) return 'role-senior';
            if (role === 2) return 'role-supplier';
            if (role === 1) return 'role-admin';
            return 'role-user';
        }
    }
};
</script>

<style scoped>
.user-profile-card {
    --profile-navy: #172b4d;
    --profile-blue: #246bfd;
    --profile-red: #e5484d;
    position: relative;
    overflow: hidden;
    width: 100%;
    background: #fff;
    border: 1px solid #e6ebf2;
    border-radius: 18px;
    box-shadow: 0 5px 20px rgba(23, 43, 77, 0.06);
    transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
}

.user-profile-card:hover {
    border-color: #d5e2fb;
    box-shadow: 0 10px 28px rgba(23, 43, 77, 0.1);
    transform: translateY(-2px);
}

.profile-card-accent {
    height: 4px;
    background: linear-gradient(90deg, var(--profile-blue) 0%, #69a0ff 68%, var(--profile-red) 100%);
}

.profile-card-body {
    padding: 16px;
}

.profile-heading {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 0;
    color: inherit;
    text-align: left;
    background: transparent;
    border: 0;
    cursor: pointer;
}

.profile-avatar {
    display: inline-flex;
    flex: 0 0 46px;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    color: var(--profile-blue);
    font-size: 19px;
    background: #edf3ff;
    border: 1px solid #dce8ff;
    border-radius: 14px;
}

.profile-heading-text {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-width: 0;
    gap: 5px;
}

.profile-name {
    overflow-wrap: anywhere;
    color: var(--profile-navy);
    font-size: 1rem;
    font-weight: 750;
    line-height: 1.3;
}

.profile-role {
    display: inline-flex;
    align-self: flex-start;
    align-items: center;
    padding: 4px 9px;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 700;
    line-height: 1.2;
}

.role-user { color: #475569; background: #f1f5f9; }
.role-admin { color: #1d4ed8; background: #eaf1ff; }
.role-supplier { color: #087e8b; background: #e5f8fa; }
.role-senior { color: #a45308; background: #fff3df; }
.role-superadmin { color: #b4232d; background: #ffebed; }

.expand-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 32px;
    width: 32px;
    height: 32px;
    color: #64748b;
    background: #f5f7fb;
    border-radius: 10px;
    transition: background .2s ease, color .2s ease;
}

.profile-heading:hover .expand-indicator {
    color: var(--profile-blue);
    background: #edf3ff;
}

.profile-details {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid #edf0f5;
}

.detail-row {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    min-width: 0;
}

.detail-icon {
    display: inline-flex;
    flex: 0 0 34px;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    color: #4c6fae;
    background: #f0f5ff;
    border-radius: 10px;
}

.detail-content {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-width: 0;
    gap: 3px;
}

.detail-label {
    color: #7b8798;
    font-size: .75rem;
    font-weight: 600;
}

.detail-value {
    overflow-wrap: anywhere;
    color: #26364b;
    font-size: .9rem;
    font-weight: 600;
}

.percent-value { color: var(--profile-blue); }

.work-status-panel {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 13px 14px;
    background: #fff0f0;
    border: 1px solid #ffdddd;
    border-radius: 13px;
}

.work-status-panel.is-working {
    background: #ecfdf3;
    border-color: #c9f0d8;
}

.work-status-copy {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.work-status-title {
    color: var(--profile-navy);
    font-size: .86rem;
    font-weight: 750;
}

.work-status-description {
    color: #69778a;
    font-size: .76rem;
}

.work-switch { padding-left: 2.5em; }
.work-switch .form-check-input {
    width: 2.5em;
    height: 1.35em;
    margin-left: -2.5em;
    cursor: pointer;
}
.work-switch .form-check-input:checked {
    background-color: #16a05d;
    border-color: #16a05d;
}

.profile-details-enter-active,
.profile-details-leave-active {
    transition: opacity .18s ease, transform .18s ease;
}
.profile-details-enter-from,
.profile-details-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}

@media (max-width: 480px) {
    .profile-card-body { padding: 13px; }
    .profile-avatar { flex-basis: 40px; width: 40px; height: 40px; border-radius: 12px; }
    .profile-name { font-size: .94rem; }
    .work-status-panel { align-items: flex-start; }
}

@media (prefers-reduced-motion: reduce) {
    .user-profile-card,
    .expand-indicator,
    .profile-details-enter-active,
    .profile-details-leave-active {
        transition: none;
    }
}


/* Быстрая смена пароля */
.profile-actions {
    display: flex;
    flex-direction: column;
    gap: 9px;
    padding-top: 4px;
}
.profile-action-button,
.change-password-button {
    display: flex;
    align-items: center;
    gap: 11px;
    width: 100%;
    padding: 13px 14px;
    color: #1d4ed8;
    text-align: left;
    background: #f3f7ff;
    border: 1px solid #dce8ff;
    border-radius: 13px;
    font: inherit;
    font-size: .88rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .18s ease, border-color .18s ease, transform .18s ease;
}
.change-password-button:hover {
    background: #eaf1ff;
    border-color: #b9d0ff;
    transform: translateY(-1px);
}
.my-income-button {
    color: #087443;
    background: #effcf5;
    border: 1px solid #ccefdc;
}
.my-income-button:hover {
    color: #066238;
    background: #e3f8ec;
    border-color: #a8e2c1;
    transform: translateY(-1px);
}
.my-income-button > i:first-child {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    color: #0a8f52;
    background: #fff;
    border-radius: 10px;
}

.change-password-button > i:first-child {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    color: #246bfd;
    background: #fff;
    border-radius: 10px;
}
.profile-action-button span, .change-password-button span { flex: 1; }
.change-password-button .action-arrow { color: #7892bd; font-size: .78rem; }

.password-modal-backdrop {
    position: fixed;
    z-index: 20000;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto;
    background: rgba(12, 24, 43, .64);
    -webkit-backdrop-filter: blur(5px);
    backdrop-filter: blur(5px);
}
.password-modal {
    width: min(100%, 460px);
    max-height: calc(100dvh - 40px);
    overflow-y: auto;
    color: #172b4d;
    background: #fff;
    border: 1px solid rgba(230, 235, 242, .9);
    border-radius: 22px;
    box-shadow: 0 24px 80px rgba(8, 20, 40, .28);
}
.password-modal-header {
    position: relative;
    padding: 25px 26px 20px;
    background: linear-gradient(135deg, #f0f5ff 0%, #fff 100%);
    border-bottom: 1px solid #edf1f7;
}
.password-modal-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    margin-bottom: 14px;
    color: #246bfd;
    background: #e1ebff;
    border: 1px solid #d1e0ff;
    border-radius: 15px;
    font-size: 20px;
}
.password-modal-header h2 {
    margin: 0 40px 7px 0;
    color: #172b4d;
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -.02em;
}
.password-modal-header p {
    margin: 0;
    color: #718096;
    font-size: .88rem;
    line-height: 1.5;
}
.password-modal-close {
    position: absolute;
    top: 18px;
    right: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    color: #64748b;
    background: #fff;
    border: 1px solid #e5eaf2;
    border-radius: 11px;
    cursor: pointer;
}
.password-modal-close:hover { color: #e5484d; background: #fff3f3; }
.password-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 22px 26px 26px;
}
.password-field { display: flex; flex-direction: column; gap: 7px; }
.password-field > label {
    color: #34445b;
    font-size: .82rem;
    font-weight: 750;
}
.password-input-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 48px;
    padding: 0 12px;
    background: #fff;
    border: 1px solid #dce3ed;
    border-radius: 12px;
    transition: border-color .18s ease, box-shadow .18s ease;
}
.password-input-wrap:focus-within {
    border-color: #78a4ff;
    box-shadow: 0 0 0 3px rgba(36, 107, 253, .11);
}
.password-input-wrap > i:first-child { color: #7890b2; width: 17px; text-align: center; }
.password-input-wrap input {
    flex: 1;
    width: 0;
    min-width: 0;
    padding: 12px 0;
    color: #172b4d;
    background: transparent;
    border: 0;
    outline: 0;
    font: inherit;
    font-size: .9rem;
}
.password-input-wrap input:focus { outline: 0; }
.password-visibility {
    padding: 6px;
    color: #718096;
    background: transparent;
    border: 0;
    cursor: pointer;
}
.password-hint { color: #8793a5; font-size: .75rem; }
.password-inline-error { color: #c62835; font-size: .76rem; }
.password-alert {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    padding: 11px 12px;
    border-radius: 11px;
    font-size: .82rem;
    line-height: 1.45;
}
.password-alert-error { color: #a8202a; background: #fff0f1; border: 1px solid #ffd9dc; }
.password-alert-success { color: #087443; background: #eafaf1; border: 1px solid #c9f0d8; }
.password-modal-actions { display: flex; gap: 10px; padding-top: 3px; }
.password-cancel-button, .password-submit-button {
    display: inline-flex;
    flex: 1;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 46px;
    padding: 10px 14px;
    border-radius: 12px;
    font: inherit;
    font-size: .86rem;
    font-weight: 750;
    cursor: pointer;
    transition: background .18s ease, transform .18s ease;
}
.password-cancel-button { color: #475569; background: #f7f9fc; border: 1px solid #e2e8f0; }
.password-cancel-button:hover { background: #edf1f7; }
.password-submit-button { color: #fff; background: #246bfd; border: 1px solid #246bfd; box-shadow: 0 5px 12px rgba(36, 107, 253, .18); }
.password-submit-button:hover:not(:disabled) { background: #1557e8; transform: translateY(-1px); }
.password-submit-button:disabled, .password-cancel-button:disabled { opacity: .6; cursor: not-allowed; }
.password-modal-enter-active, .password-modal-leave-active { transition: opacity .2s ease; }
.password-modal-enter-active .password-modal, .password-modal-leave-active .password-modal { transition: transform .2s ease, opacity .2s ease; }
.password-modal-enter-from, .password-modal-leave-to { opacity: 0; }
.password-modal-enter-from .password-modal, .password-modal-leave-to .password-modal { opacity: 0; transform: translateY(10px) scale(.985); }

@media (max-width: 520px) {
    .password-modal-backdrop { align-items: flex-end; padding: 0; }
    .password-modal {
        width: 100%;
        max-height: 92dvh;
        border-radius: 22px 22px 0 0;
        padding-bottom: env(safe-area-inset-bottom, 0px);
    }
    .password-modal-header { padding: 22px 20px 17px; }
    .password-form { padding: 20px; gap: 14px; }
    .password-modal-actions { flex-direction: column-reverse; }
    .password-cancel-button, .password-submit-button { flex: auto; width: 100%; }
}

</style>
