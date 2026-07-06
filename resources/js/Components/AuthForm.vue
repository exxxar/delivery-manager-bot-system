<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">

                        <!-- Табы -->
                        <ul class="nav nav-tabs nav-fill mb-4" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link"
                                    :class="{ active: activeTab === 'login' }"
                                    @click="activeTab = 'login'"
                                    type="button"
                                    role="tab"
                                >
                                    <i class="fa-solid fa-right-to-bracket me-1"></i>
                                    Вход
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link"
                                    :class="{ active: activeTab === 'register' }"
                                    @click="activeTab = 'register'"
                                    type="button"
                                    role="tab"
                                >
                                    <i class="fa-solid fa-user-plus me-1"></i>
                                    Регистрация
                                </button>
                            </li>
                        </ul>

                        <!-- Содержимое табов -->
                        <div class="tab-content">

                            <!-- ===== ТАБ: ВХОД ===== -->
                            <div v-show="activeTab === 'login'">
                                <h3 class="mb-4 text-center">Вход</h3>

                                <form @submit.prevent="submitLogin">
                                    <div class="mb-3">
                                        <label class="form-label">Логин</label>
                                        <input
                                            v-model="loginForm.login"
                                            class="form-control"
                                            type="text"
                                            required
                                        >
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Пароль</label>
                                        <input
                                            v-model="loginForm.password"
                                            class="form-control"
                                            type="password"
                                            required
                                        >
                                    </div>

                                    <button
                                        class="btn btn-primary w-100"
                                        :disabled="loading"
                                    >
                                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                        Войти
                                    </button>
                                </form>

                                <hr>

                                <div id="telegram-login"></div>
                            </div>

                            <!-- ===== ТАБ: РЕГИСТРАЦИЯ ===== -->
                            <div v-show="activeTab === 'register'">
                                <h3 class="mb-4 text-center">Регистрация</h3>

                                <form @submit.prevent="submitRegister">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Имя <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            v-model="registerForm.name"
                                            class="form-control"
                                            type="text"
                                            :class="{ 'is-invalid': errors.name }"
                                            placeholder="Иван Иванов"
                                            required
                                        >
                                        <div class="invalid-feedback" v-if="errors.name">
                                            {{ errors.name[0] }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            Email <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            v-model="registerForm.email"
                                            class="form-control"
                                            type="email"
                                            :class="{ 'is-invalid': errors.email }"
                                            placeholder="example@mail.com"
                                            required
                                        >
                                        <div class="invalid-feedback" v-if="errors.email">
                                            {{ errors.email[0] }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            Телефон <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            v-model="registerForm.phone"
                                            class="form-control"
                                            v-mask="'+7(###) ###-##-##'"
                                            type="text"
                                            :class="{ 'is-invalid': errors.phone }"
                                            placeholder="+7 (999) 123-45-67"
                                            required
                                        >
                                        <div class="invalid-feedback" v-if="errors.phone">
                                            {{ errors.phone[0] }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            Пароль <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            v-model="registerForm.password"
                                            class="form-control"
                                            type="password"
                                            :class="{ 'is-invalid': errors.password }"
                                            placeholder="Минимум 6 символов"
                                            required
                                        >
                                        <div class="invalid-feedback" v-if="errors.password">
                                            {{ errors.password[0] }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            Подтверждение пароля <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            v-model="registerForm.password_confirmation"
                                            class="form-control"
                                            type="password"
                                            :class="{ 'is-invalid': errors.password_confirmation }"
                                            placeholder="Повторите пароль"
                                            required
                                        >
                                        <div class="invalid-feedback" v-if="errors.password_confirmation">
                                            {{ errors.password_confirmation[0] }}
                                        </div>
                                    </div>

                                    <button
                                        class="btn btn-success w-100"
                                        :disabled="loading"
                                    >
                                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                        Зарегистрироваться
                                    </button>
                                </form>

                                <div v-if="successMessage" class="alert alert-success mt-3 mb-0">
                                    {{ successMessage }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                password: '',
                password_confirmation: ''
            },

            errors: {},
            successMessage: '',
            loading: false
        }
    },

    computed: {
        loading() {
            return this.auth.loading
        }
    },

    methods: {
        // ===== ВХОД =====
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

        // ===== РЕГИСТРАЦИЯ =====
        async submitRegister() {
            this.errors = {}
            this.successMessage = ''
            this.loading = true

            try {
                await this.auth.register(this.registerForm)

                this.successMessage = 'Регистрация прошла успешно! Теперь вы можете войти.'

                // Очищаем форму регистрации
                this.registerForm = {
                    name: '',
                    email: '',
                    phone: '',
                    password: '',
                    password_confirmation: ''
                }

                // Переключаемся на таб входа через 1.5 секунды
                setTimeout(() => {
                    this.activeTab = 'login'
                    this.successMessage = ''
                }, 1500)

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

        // ===== TELEGRAM =====
        telegramCallback(user) {
            this.auth.loginTelegram(user)
        },

        loadTelegram() {
            window.onTelegramAuth = this.telegramCallback

            const script = document.createElement('script')
            script.src = 'https://telegram.org/js/telegram-widget.js?2'
            script.async = true
            script.setAttribute('data-telegram-login', 'delivery_town_bot')
            script.setAttribute('data-auth-url', '/callback/telegram')
            script.setAttribute('data-request-access', 'write')

            document.getElementById('telegram-login').appendChild(script)
        }
    },

    mounted() {
        this.loadTelegram()
    }
}
</script>
