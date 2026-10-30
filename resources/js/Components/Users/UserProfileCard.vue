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

                    <div class="password-reset-section">
                        <div class="password-reset-heading">
        <span class="password-reset-icon">
            <i class="fa-solid fa-key"></i>
        </span>

                            <div>
                                <h3>Сброс пароля</h3>
                                <p>Установить пользователю пароль по умолчанию</p>
                            </div>
                        </div>

                        <div class="password-reset-warning" role="alert">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>
            После сброса пароль пользователя станет
            <strong>12345678</strong>.
            Рекомендуется сменить его после входа в систему.
        </span>
                        </div>

                        <div v-if="resetPasswordError"
                             class="password-alert password-alert-error"
                             role="alert">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span>{{ resetPasswordError }}</span>
                        </div>

                        <div v-if="resetPasswordSuccess"
                             class="password-alert password-alert-success"
                             role="status">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>{{ resetPasswordSuccess }}</span>
                        </div>

                        <button
                            type="button"
                            class="password-reset-button"
                            :disabled="resetPasswordSaving || passwordSaving"
                            @click="resetUserPassword"
                        >
                            <i :class="resetPasswordSaving
            ? 'fa-solid fa-spinner fa-spin'
            : 'fa-solid fa-rotate'"></i>

                            {{
                                resetPasswordSaving
                                    ? 'Сбрасываем пароль…'
                                    : 'Сбросить пароль на 12345678'
                            }}
                        </button>
                    </div>

                    <div class="password-divider">
                        <span>Или сменить пароль вручную</span>
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
            resetPasswordSaving: false,
            resetPasswordError: '',
            resetPasswordSuccess: '',
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
        async resetUserPassword() {
            if (this.resetPasswordSaving || this.passwordSaving) return;

            if (!this.user?.id) {
                this.resetPasswordError = 'Не удалось определить пользователя.';
                return;
            }

            const confirmed = window.confirm(
                `Сбросить пароль пользователя ${this.user.name}?\n\n` +
                'Новый пароль: 12345678'
            );

            if (!confirmed) return;

            this.resetPasswordSaving = true;
            this.resetPasswordError = '';
            this.resetPasswordSuccess = '';
            this.passwordError = '';
            this.passwordSuccess = '';

            try {
                const response = await axios.post(
                    `/api/users/${this.user.id}/reset-password`
                );

                this.resetPasswordSuccess =
                    response?.data?.message || 'Пароль успешно сброшен.';
            } catch (error) {
                const data = error?.response?.data;

                this.resetPasswordError =
                    data?.message || 'Не удалось сбросить пароль.';
            } finally {
                this.resetPasswordSaving = false;
            }
        },
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
/* =========================================================
   МОДАЛЬНОЕ ОКНО СМЕНЫ И СБРОСА ПАРОЛЯ
   ========================================================= */

.password-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 20000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto;
    background: rgba(12, 24, 43, 0.68);
    -webkit-backdrop-filter: blur(5px);
    backdrop-filter: blur(5px);
}

.password-modal-backdrop,
.password-modal-backdrop * {
    box-sizing: border-box;
}

.password-modal {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 480px;
    max-height: calc(100dvh - 40px);
    overflow-x: hidden;
    overflow-y: auto;
    color: #172b4d;
    background: #fff;
    border: 1px solid #e6ebf2;
    border-radius: 22px;
    box-shadow: 0 24px 80px rgba(8, 20, 40, 0.28);
    overscroll-behavior: contain;
}

/* Заголовок */

.password-modal .password-modal-header {
    position: relative;
    flex-shrink: 0;
    padding: 25px 26px 20px;
    background: linear-gradient(135deg, #f0f5ff 0%, #fff 100%);
    border-bottom: 1px solid #edf1f7;
}

.password-modal .password-modal-icon {
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

.password-modal .password-modal-header h2 {
    margin: 0 40px 7px 0;
    color: #172b4d;
    font-size: 1.25rem;
    font-weight: 800;
    line-height: 1.3;
    letter-spacing: -0.02em;
}

.password-modal .password-modal-header p {
    margin: 0;
    color: #718096;
    font-size: 0.88rem;
    font-weight: 400;
    line-height: 1.5;
}

.password-modal .password-modal-close {
    position: absolute;
    top: 18px;
    right: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    padding: 0;
    color: #64748b;
    background: #fff;
    border: 1px solid #e5eaf2;
    border-radius: 11px;
    font-size: 1rem;
    cursor: pointer;
    transition: color 0.18s ease, background 0.18s ease;
}

.password-modal .password-modal-close:hover {
    color: #e5484d;
    background: #fff3f3;
}

.password-modal .password-modal-close:focus-visible,
.password-modal button:focus-visible {
    outline: 3px solid rgba(36, 107, 253, 0.3);
    outline-offset: 2px;
}

/* =========================================================
   БЛОК СБРОСА ПАРОЛЯ
   ========================================================= */

.password-modal .password-reset-section {
    display: flex;
    flex-direction: column;
    gap: 13px;
    padding: 22px 26px 0;
}

.password-modal .password-reset-heading {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.password-modal .password-reset-icon {
    display: inline-flex;
    flex: 0 0 42px;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    color: #b4232d;
    background: #ffebed;
    border: 1px solid #ffd3d7;
    border-radius: 12px;
    font-size: 17px;
}

.password-modal .password-reset-heading > div {
    flex: 1;
    min-width: 0;
}

.password-modal .password-reset-heading h3 {
    margin: 0 0 4px;
    color: #172b4d;
    font-size: 0.95rem;
    font-weight: 800;
    line-height: 1.35;
}

.password-modal .password-reset-heading p {
    margin: 0;
    color: #718096;
    font-size: 0.78rem;
    font-weight: 400;
    line-height: 1.45;
}

.password-modal .password-reset-warning {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    min-width: 0;
    padding: 12px 13px;
    color: #854d0e;
    background: #fff8e6;
    border: 1px solid #f5dda0;
    border-radius: 12px;
    font-size: 0.84rem;
    font-weight: 400;
    line-height: 1.55;
}

.password-modal .password-reset-warning > i {
    flex: 0 0 auto;
    margin-top: 3px;
    color: #d97706;
}

.password-modal .password-reset-warning strong {
    color: #7c2d12;
    font-size: 0.95rem;
    font-weight: 900;
    white-space: nowrap;
}

.password-modal .password-reset-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    width: 100%;
    min-height: 46px;
    margin: 0;
    padding: 11px 14px;
    color: #fff;
    background: #dc3545;
    border: 1px solid #dc3545;
    border-radius: 12px;
    font-family: inherit;
    font-size: 0.86rem;
    font-weight: 750;
    line-height: 1.4;
    text-align: center;
    white-space: normal;
    cursor: pointer;
    transition:
        background 0.18s ease,
        border-color 0.18s ease,
        transform 0.18s ease;
}

.password-modal .password-reset-button:hover:not(:disabled) {
    background: #bb2d3b;
    border-color: #bb2d3b;
    transform: translateY(-1px);
}

.password-modal .password-reset-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Разделитель между сбросом и ручной сменой */

.password-modal .password-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 26px 0;
    color: #8793a5;
    font-size: 0.76rem;
    font-weight: 500;
    line-height: 1.4;
    text-align: center;
}

.password-modal .password-divider::before,
.password-modal .password-divider::after {
    content: "";
    flex: 1 1 auto;
    min-width: 12px;
    height: 1px;
    background: #e5eaf2;
}

/* =========================================================
   ФОРМА РУЧНОЙ СМЕНЫ ПАРОЛЯ
   ========================================================= */

.password-modal .password-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
    min-width: 0;
    margin: 0;
    padding: 18px 26px 26px;
    background: #fff;
}

.password-modal .password-field {
    display: flex;
    flex-direction: column;
    gap: 7px;
    min-width: 0;
}

.password-modal .password-field > label {
    display: block;
    margin: 0;
    color: #34445b;
    font-size: 0.82rem;
    font-weight: 750;
    line-height: 1.4;
}

.password-modal .password-input-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    min-width: 0;
    min-height: 48px;
    padding: 0 12px;
    background: #fff;
    border: 1px solid #dce3ed;
    border-radius: 12px;
    transition:
        border-color 0.18s ease,
        box-shadow 0.18s ease;
}

.password-modal .password-input-wrap:focus-within {
    border-color: #78a4ff;
    box-shadow: 0 0 0 3px rgba(36, 107, 253, 0.11);
}

.password-modal .password-input-wrap > i:first-child {
    flex: 0 0 17px;
    width: 17px;
    color: #7890b2;
    font-size: 0.9rem;
    text-align: center;
}

.password-modal .password-input-wrap input {
    display: block;
    flex: 1 1 auto;
    width: 0;
    min-width: 0;
    min-height: 46px;
    margin: 0;
    padding: 10px 0;
    color: #172b4d;
    background: transparent;
    border: 0;
    border-radius: 0;
    outline: none;
    box-shadow: none;
    font-family: inherit;
    font-size: 0.9rem;
    font-weight: 400;
    line-height: 1.4;
}

.password-modal .password-input-wrap input:focus {
    outline: none;
    border: 0;
    box-shadow: none;
}

.password-modal .password-input-wrap input::placeholder {
    color: #9aa6b6;
    opacity: 1;
}

.password-modal .password-visibility {
    display: inline-flex;
    flex: 0 0 30px;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 34px;
    margin: 0;
    padding: 5px;
    color: #718096;
    background: transparent;
    border: 0;
    border-radius: 8px;
    font-size: 0.9rem;
    cursor: pointer;
}

.password-modal .password-visibility:hover {
    color: #246bfd;
    background: #f0f5ff;
}

.password-modal .password-hint {
    color: #8793a5;
    font-size: 0.75rem;
    line-height: 1.4;
}

.password-modal .password-inline-error {
    color: #c62835;
    font-size: 0.76rem;
    line-height: 1.4;
}

/* Сообщения об успехе и ошибках */

.password-modal .password-alert {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    min-width: 0;
    padding: 11px 12px;
    border-radius: 11px;
    font-size: 0.82rem;
    font-weight: 400;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.password-modal .password-alert > i {
    flex: 0 0 auto;
    margin-top: 2px;
}

.password-modal .password-alert-error {
    color: #a8202a;
    background: #fff0f1;
    border: 1px solid #ffd9dc;
}

.password-modal .password-alert-success {
    color: #087443;
    background: #eafaf1;
    border: 1px solid #c9f0d8;
}

/* Кнопки внизу формы */

.password-modal .password-modal-actions {
    display: flex;
    gap: 10px;
    padding-top: 3px;
}

.password-modal .password-cancel-button,
.password-modal .password-submit-button {
    display: inline-flex;
    flex: 1 1 0;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-width: 0;
    min-height: 46px;
    margin: 0;
    padding: 10px 14px;
    border-radius: 12px;
    font-family: inherit;
    font-size: 0.86rem;
    font-weight: 750;
    line-height: 1.4;
    text-align: center;
    cursor: pointer;
    transition:
        background 0.18s ease,
        border-color 0.18s ease,
        transform 0.18s ease;
}

.password-modal .password-cancel-button {
    color: #475569;
    background: #f7f9fc;
    border: 1px solid #e2e8f0;
}

.password-modal .password-cancel-button:hover:not(:disabled) {
    background: #edf1f7;
}

.password-modal .password-submit-button {
    color: #fff;
    background: #246bfd;
    border: 1px solid #246bfd;
    box-shadow: 0 5px 12px rgba(36, 107, 253, 0.18);
}

.password-modal .password-submit-button:hover:not(:disabled) {
    background: #1557e8;
    border-color: #1557e8;
    transform: translateY(-1px);
}

.password-modal .password-submit-button:disabled,
.password-modal .password-cancel-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* =========================================================
   АНИМАЦИЯ МОДАЛЬНОГО ОКНА
   ========================================================= */

.password-modal-enter-active,
.password-modal-leave-active {
    transition: opacity 0.2s ease;
}

.password-modal-enter-active .password-modal,
.password-modal-leave-active .password-modal {
    transition:
        transform 0.2s ease,
        opacity 0.2s ease;
}

.password-modal-enter-from,
.password-modal-leave-to {
    opacity: 0;
}

.password-modal-enter-from .password-modal,
.password-modal-leave-to .password-modal {
    opacity: 0;
    transform: translateY(10px) scale(0.985);
}

/* =========================================================
   МОБИЛЬНАЯ ВЕРСИЯ
   ========================================================= */

@media (max-width: 520px) {
    .password-modal-backdrop {
        align-items: flex-end;
        padding: 0;
    }

    .password-modal {
        width: 100%;
        max-width: none;
        max-height: 92dvh;
        border-right: 0;
        border-bottom: 0;
        border-left: 0;
        border-radius: 22px 22px 0 0;
        padding-bottom: env(safe-area-inset-bottom, 0px);
    }

    .password-modal .password-modal-header {
        padding: 22px 20px 17px;
    }

    .password-modal .password-reset-section {
        gap: 12px;
        padding: 18px 20px 0;
    }

    .password-modal .password-divider {
        gap: 9px;
        padding: 18px 20px 0;
        font-size: 0.72rem;
    }

    .password-modal .password-form {
        gap: 14px;
        padding: 16px 20px 22px;
    }

    .password-modal .password-modal-actions {
        flex-direction: column-reverse;
    }

    .password-modal .password-cancel-button,
    .password-modal .password-submit-button {
        flex: 0 0 auto;
        width: 100%;
    }
}

@media (max-width: 360px) {
    .password-modal .password-reset-heading {
        align-items: flex-start;
    }

    .password-modal .password-reset-warning {
        padding: 10px;
        font-size: 0.8rem;
    }

    .password-modal .password-reset-button {
        font-size: 0.81rem;
    }

    .password-modal .password-modal-header h2 {
        font-size: 1.15rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .password-modal-enter-active,
    .password-modal-leave-active,
    .password-modal-enter-active .password-modal,
    .password-modal-leave-active .password-modal,
    .password-modal .password-reset-button,
    .password-modal .password-submit-button,
    .password-modal .password-cancel-button {
        transition: none;
    }
}
</style>
