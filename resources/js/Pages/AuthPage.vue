<template>
    <main class="auth-page">
        <div class="auth-page__decor auth-page__decor--one"></div>
        <div class="auth-page__decor auth-page__decor--two"></div>

        <div class="container auth-page__container">
            <section class="auth-card">
                <div class="auth-card__brand">
                    <div class="auth-card__brand-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="auth-card__brand-name">Мойпроцент</div>
                        <div class="auth-card__brand-caption">Личный кабинет</div>
                    </div>
                </div>

                <div class="auth-card__intro">
                    <div class="auth-card__eyebrow">Рады вас видеть</div>
                    <h1>{{ activeTab === 'login' ? 'С возвращением!' : 'Создайте аккаунт' }}</h1>
                    <p>
                        {{ activeTab === 'login'
                        ? 'Войдите, чтобы продолжить работу с вашими заявками и продажами.'
                        : 'Заполните данные ниже, чтобы начать пользоваться сервисом.' }}
                    </p>
                </div>

                <div class="auth-tabs" role="tablist" aria-label="Вход или регистрация">
                    <button
                        class="auth-tabs__item"
                        :class="{ 'is-active': activeTab === 'login' }"
                        @click="switchTab('login')"
                        type="button"
                        role="tab"
                        :aria-selected="activeTab === 'login'"
                    >
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Вход</span>
                    </button>
                    <button
                        class="auth-tabs__item"
                        :class="{ 'is-active': activeTab === 'register' }"
                        @click="switchTab('register')"
                        type="button"
                        role="tab"
                        :aria-selected="activeTab === 'register'"
                    >
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Регистрация</span>
                    </button>
                </div>

                <div class="auth-card__form-area">
                    <form v-show="activeTab === 'login'" @submit.prevent="submitLogin" class="auth-form">
                        <div class="auth-field">
                            <label for="login">Логин</label>
                            <div class="auth-field__control">
                                <i class="fa-regular fa-user"></i>
                                <input
                                    id="login"
                                    v-model="loginForm.login"
                                    type="text"
                                    autocomplete="username"
                                    placeholder="Введите логин"
                                    required
                                >
                            </div>
                        </div>

                        <div class="auth-field">
                            <label for="login-password">Пароль</label>
                            <div class="auth-field__control">
                                <i class="fa-solid fa-lock"></i>
                                <input
                                    id="login-password"
                                    v-model="loginForm.password"
                                    type="password"
                                    autocomplete="current-password"
                                    placeholder="Введите пароль"
                                    required
                                >
                            </div>
                        </div>

                        <button class="auth-submit auth-submit--primary" type="submit" :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                            <i v-else class="fa-solid fa-arrow-right-to-bracket"></i>
                            <span>{{ loading ? 'Выполняем вход…' : 'Войти в аккаунт' }}</span>
                        </button>

                    </form>

                    <form v-show="activeTab === 'register'" @submit.prevent="submitRegister" class="auth-form">
                        <div class="auth-field">
                            <label for="register-name">Имя <span>*</span></label>
                            <div class="auth-field__control" :class="{ 'has-error': errors.name }">
                                <i class="fa-regular fa-user"></i>
                                <input
                                    id="register-name"
                                    v-model="registerForm.name"
                                    type="text"
                                    autocomplete="name"
                                    :class="{ 'is-invalid': errors.name }"
                                    placeholder="Иван Иванов"
                                    required
                                >
                            </div>
                            <div class="auth-error" v-if="errors.name">{{ errors.name[0] }}</div>
                        </div>

                        <div class="auth-field">
                            <label for="register-email">Email <span>*</span></label>
                            <div class="auth-field__control" :class="{ 'has-error': errors.email }">
                                <i class="fa-regular fa-envelope"></i>
                                <input
                                    id="register-email"
                                    v-model="registerForm.email"
                                    type="email"
                                    autocomplete="email"
                                    :class="{ 'is-invalid': errors.email }"
                                    placeholder="example@mail.com"
                                    required
                                >
                            </div>
                            <div class="auth-error" v-if="errors.email">{{ errors.email[0] }}</div>
                        </div>

                        <div class="auth-field">
                            <label for="register-phone">Телефон <span>*</span></label>
                            <div class="auth-field__control" :class="{ 'has-error': errors.phone }">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                                <input
                                    id="register-phone"
                                    v-model="registerForm.phone"
                                    v-mask="'+7(###) ###-##-##'"
                                    type="text"
                                    autocomplete="tel"
                                    :class="{ 'is-invalid': errors.phone }"
                                    placeholder="+7 (999) 123-45-67"
                                    required
                                >
                            </div>
                            <div class="auth-error" v-if="errors.phone">{{ errors.phone[0] }}</div>
                        </div>

                        <div class="auth-field">
                            <label for="register-role">Тип аккаунта <span>*</span></label>
                            <div class="auth-field__control" :class="{ 'has-error': errors.role }">
                                <i class="fa-solid fa-users-gear"></i>
                                <select
                                    id="register-role"
                                    v-model.number="registerForm.role"
                                    :class="{ 'is-invalid': errors.role }"
                                    required
                                >
                                    <option :value="0">Пользователь</option>
                                    <option :value="1">Администратор</option>
                                    <option :value="2">Поставщик</option>
                                </select>
                            </div>
                            <div class="auth-error" v-if="errors.role">{{ errors.role[0] }}</div>
                            <div class="auth-field__hint">Тип аккаунта определяет доступные разделы системы.</div>
                        </div>

                        <div class="auth-field">
                            <label for="register-password">Пароль <span>*</span></label>
                            <div class="auth-field__control" :class="{ 'has-error': errors.password }">
                                <i class="fa-solid fa-lock"></i>
                                <input
                                    id="register-password"
                                    v-model="registerForm.password"
                                    type="password"
                                    autocomplete="new-password"
                                    :class="{ 'is-invalid': errors.password }"
                                    placeholder="Минимум 6 символов"
                                    required
                                >
                            </div>
                            <div class="auth-error" v-if="errors.password">{{ errors.password[0] }}</div>
                        </div>

                        <div class="auth-field">
                            <label for="register-password-confirmation">Подтверждение пароля <span>*</span></label>
                            <div class="auth-field__control" :class="{ 'has-error': errors.password_confirmation }">
                                <i class="fa-solid fa-shield-halved"></i>
                                <input
                                    id="register-password-confirmation"
                                    v-model="registerForm.password_confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    :class="{ 'is-invalid': errors.password_confirmation }"
                                    placeholder="Повторите пароль"
                                    required
                                >
                            </div>
                            <div class="auth-error" v-if="errors.password_confirmation">
                                {{ errors.password_confirmation[0] }}
                            </div>
                        </div>

                        <button class="auth-submit auth-submit--success" type="submit" :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                            <i v-else class="fa-solid fa-user-check"></i>
                            <span>{{ loading ? 'Создаём аккаунт…' : 'Зарегистрироваться' }}</span>
                        </button>

                        <div v-if="successMessage" class="auth-success">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>{{ successMessage }}</span>
                        </div>
                    </form>
                </div>

                <footer class="auth-card__footer">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Ваши данные защищены и используются для работы с сервисом</span>
                </footer>
            </section>

            <div class="auth-page__copyright">© Мойпроцент · Система управления продажами</div>
        </div>
    </main>
</template>

<script>
import { useUsersStore } from '@/stores/users'

export default {
    data() {
        return {
            auth: useUsersStore(),
            activeTab: 'login',
            loginForm: {
                login: '',
                password: ''
            },
            registerForm: {
                name: '',
                email: '',
                phone: '',
                role: 0,
                password: '',
                password_confirmation: ''
            },
            errors: {},
            successMessage: '',
            loading: false
        }
    },

    methods: {
        switchTab(tab) {
            this.activeTab = tab
            this.errors = {}
            this.successMessage = ''
        },

        async submitLogin() {
            this.loading = true
            try {
                await this.auth.login(this.loginForm)
                window.location.href = "/pwa"
            } catch (e) {
                alert('Ошибка авторизации')
            } finally {
                this.loading = false
            }
        },

        async submitRegister() {
            this.errors = {}
            this.successMessage = ''
            this.loading = true

            try {
                await this.auth.register(this.registerForm)
                // The backend logs the user in and regenerates the session.
                // Reload the app so route guards and the Pinia auth state
                // initialize from the authenticated session.
                window.location.href = "/pwa"
            } catch (e) {
                if (e.response?.status === 422) {
                    this.errors = e.response.data.errors || {}
                } else if (e.response?.data?.message) {
                    alert(e.response.data.message)
                } else {
                    alert('Ошибка регистрации')
                }
            } finally {
                this.loading = false
            }
        },

    },

    mounted() {

    }
}
</script>

<style scoped>
.auth-page {
    --auth-blue: #2457d6;
    --auth-blue-dark: #173b91;
    --auth-red: #df3548;
    --auth-text: #17233b;
    --auth-muted: #78859a;
    --auth-border: #e4eaf3;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 42px 18px 24px;
    overflow: hidden;
    color: var(--auth-text);
    background:
        radial-gradient(circle at 10% 15%, rgba(37, 99, 235, .10), transparent 27rem),
        radial-gradient(circle at 90% 85%, rgba(223, 53, 72, .08), transparent 24rem),
        #f5f7fc;
}

.auth-page__decor {
    position: absolute;
    pointer-events: none;
    border: 1px solid rgba(36, 87, 214, .08);
    border-radius: 50%;
}
.auth-page__decor--one { width: 420px; height: 420px; top: -220px; right: -110px; }
.auth-page__decor--two { width: 320px; height: 320px; bottom: -190px; left: -90px; }

.auth-page__container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 520px;
    padding: 0;
}

.auth-card {
    padding: clamp(24px, 5vw, 42px);
    background: rgba(255, 255, 255, .97);
    border: 1px solid rgba(226, 232, 240, .9);
    border-radius: 26px;
    box-shadow: 0 24px 70px rgba(22, 42, 84, .12);
}

.auth-card__brand {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 32px;
}
.auth-card__brand-icon {
    display: grid;
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    place-items: center;
    color: #fff;
    background: linear-gradient(135deg, #3474f5, #1d4ed8);
    border-radius: 15px;
    box-shadow: 0 7px 16px rgba(37, 99, 235, .23);
    font-size: 20px;
}
.auth-card__brand-name { color: var(--auth-text); font-size: 17px; font-weight: 850; letter-spacing: -.025em; }
.auth-card__brand-caption { margin-top: 2px; color: var(--auth-muted); font-size: 12px; }

.auth-card__intro { margin-bottom: 24px; }
.auth-card__eyebrow {
    margin-bottom: 8px;
    color: var(--auth-blue);
    font-size: 11px;
    font-weight: 850;
    letter-spacing: .12em;
    text-transform: uppercase;
}
.auth-card__intro h1 {
    margin: 0;
    color: var(--auth-text);
    font-size: clamp(26px, 5vw, 32px);
    font-weight: 850;
    letter-spacing: -.045em;
    line-height: 1.15;
}
.auth-card__intro p { margin: 10px 0 0; color: var(--auth-muted); font-size: 13px; line-height: 1.65; }

.auth-tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 5px;
    padding: 5px;
    margin-bottom: 26px;
    background: #f0f3f9;
    border-radius: 14px;
}
.auth-tabs__item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 46px;
    padding: 10px;
    color: #748097;
    background: transparent;
    border: 0;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 750;
    transition: color .18s ease, background .18s ease, box-shadow .18s ease;
}
.auth-tabs__item.is-active {
    color: var(--auth-blue);
    background: #fff;
    box-shadow: 0 3px 10px rgba(27, 44, 78, .08);
}
.auth-tabs__item:focus-visible { outline: 3px solid rgba(36, 87, 214, .25); outline-offset: 2px; }

.auth-form { display: flex; flex-direction: column; gap: 17px; }
.auth-field label { display: block; margin-bottom: 7px; color: #34415a; font-size: 12px; font-weight: 750; }
.auth-field label span { color: var(--auth-red); }
.auth-field__control {
    display: flex;
    align-items: center;
    gap: 11px;
    min-height: 52px;
    padding: 0 14px;
    background: #fbfcff;
    border: 1px solid #dfe6f1;
    border-radius: 13px;
    transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
}
.auth-field__control > i { width: 17px; flex: 0 0 17px; color: #8a97ad; text-align: center; font-size: 14px; }
.auth-field__control:focus-within {
    background: #fff;
    border-color: #86a7ff;
    box-shadow: 0 0 0 4px rgba(36, 87, 214, .09);
}
.auth-field__control.has-error { border-color: #e58b96; background: #fffafb; }
.auth-field__control input,
.auth-field__control select {
    width: 100%;
    min-width: 0;
    padding: 14px 0;
    color: var(--auth-text);
    background: transparent;
    border: 0;
    outline: 0;
    box-shadow: none !important;
    font: inherit;
    font-size: 13px;
}
.auth-field__control input::placeholder { color: #a0aabc; }
.auth-field__control select { cursor: pointer; appearance: auto; }
.auth-field__hint { margin-top: 6px; color: var(--auth-muted); font-size: 11px; line-height: 1.45; }
.auth-field__control input.is-invalid { background-image: none; }
.auth-error { margin-top: 6px; color: #c52d40; font-size: 11px; font-weight: 650; }

.auth-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    min-height: 52px;
    margin-top: 4px;
    padding: 13px 18px;
    color: #fff;
    border: 0;
    border-radius: 13px;
    font-size: 13px;
    font-weight: 800;
    transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
}
.auth-submit--primary {
    background: linear-gradient(135deg, #3474f5, #1d4ed8);
    box-shadow: 0 8px 18px rgba(37, 99, 235, .2);
}
.auth-submit--success {
    background: linear-gradient(135deg, #2675d8, #1d4ed8);
    box-shadow: 0 8px 18px rgba(37, 99, 235, .2);
}
.auth-submit:hover:not(:disabled) { color: #fff; transform: translateY(-1px); filter: brightness(1.04); }
.auth-submit:active:not(:disabled) { transform: translateY(0); }
.auth-submit:disabled { opacity: .7; cursor: wait; box-shadow: none; }

.auth-divider { display: flex; align-items: center; gap: 12px; color: #98a2b3; font-size: 11px; text-align: center; }
.auth-divider::before, .auth-divider::after { height: 1px; flex: 1; content: ""; background: #e9edf4; }

.auth-success {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    padding: 12px 14px;
    color: #176747;
    background: #edfbf4;
    border: 1px solid #c7f0dc;
    border-radius: 12px;
    font-size: 12px;
    line-height: 1.5;
}
.auth-success i { margin-top: 2px; }

.auth-card__footer {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 8px;
    margin-top: 28px;
    padding-top: 18px;
    color: #929db0;
    border-top: 1px solid #edf0f5;
    font-size: 10px;
    line-height: 1.5;
    text-align: center;
}
.auth-card__footer i { margin-top: 1px; color: #6d8bd3; }

.auth-page__copyright {
    margin-top: 20px;
    color: #9aa4b5;
    font-size: 10px;
    text-align: center;
}

@media (max-width: 480px) {
    .auth-page { align-items: flex-start; padding: 18px 12px 22px; }
    .auth-card { padding: 23px 18px; border-radius: 21px; }
    .auth-card__brand { margin-bottom: 26px; }
    .auth-card__intro { margin-bottom: 20px; }
    .auth-form { gap: 15px; }
    .auth-field__control { min-height: 50px; }
    .auth-card__footer { margin-top: 22px; }
}

@media (prefers-reduced-motion: reduce) {
    .auth-page *, .auth-page *::before, .auth-page *::after {
        transition-duration: .01ms !important;
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
    }
}
</style>
