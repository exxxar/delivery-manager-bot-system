<!-- @/Components/Suppliers/SupplierRegisterForm.vue -->

<template>
    <div class="register-wrapper position-relative min-vh-100 py-5">
        <!-- Декоративные круги -->
        <div class="decoration-circle circle-1"></div>
        <div class="decoration-circle circle-2"></div>
        <div class="decoration-circle circle-3"></div>

        <div class="container position-relative" style="z-index: 10;">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8 col-xl-7 col-xxl-6">

                    <!-- Проверка токена -->
                    <div v-if="validationStatus === 'loading'" class="text-center py-5">
                        <div class="loading-icon mb-4">
                            <div class="pulse-ring"></div>
                            <i class="fa-solid fa-truck fa-3x text-success"></i>
                        </div>
                        <h4 class="fw-bold mb-2">Проверяем ссылку...</h4>
                        <p class="text-muted mb-0">Убеждаемся, что она действительна</p>
                    </div>

                    <!-- Ошибка валидации -->
                    <div
                        v-else-if="validationStatus === 'invalid'"
                        class="card border-0 shadow-lg error-card mx-auto"
                        style="max-width: 550px;"
                    >
                        <div class="card-body text-center p-5">
                            <div class="error-icon mb-4">
                                <i class="fa-solid fa-xmark"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Ссылка недействительна</h4>
                            <div class="alert alert-light border-start border-danger border-4 mb-0">
                                {{ validationReason }}
                            </div>
                            <hr class="my-4">
                            <p class="text-muted small mb-0">
                                <i class="fa-solid fa-circle-info me-1"></i>
                                Обратитесь к администратору для получения новой ссылки приглашения.
                            </p>
                        </div>
                    </div>

                    <!-- Форма регистрации -->
                    <template v-else-if="validationStatus === 'valid'">
                        <!-- Успех -->
                        <div
                            v-if="registered"
                            class="card border-0 shadow-lg success-card mx-auto"
                            style="max-width: 550px;"
                        >
                            <div class="card-body text-center p-5">
                                <div class="success-icon mb-4">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <h4 class="fw-bold mb-2">Регистрация успешна!</h4>
                                <p class="text-muted mb-4">
                                    Ваша учётная запись поставщика создана.<br>
                                    Войдите в систему для начала работы.
                                </p>
                                <a href="/login" class="btn btn-success btn-lg px-5 rounded-3">
                                    <i class="fa-solid fa-arrow-right me-2"></i>
                                    Перейти к входу
                                </a>
                            </div>
                        </div>

                        <!-- Форма -->
                        <form
                            v-else
                            @submit.prevent="submitForm"
                            class="card border-0 shadow-lg register-card mx-auto"
                            style="max-width: 550px;"
                        >
                            <div class="card-body p-4 p-md-5">
                                <!-- Заголовок -->
                                <div class="text-center mb-4">
                                    <div class="brand-icon d-inline-flex align-items-center justify-content-center mb-3">
                                        <i class="fa-solid fa-truck"></i>
                                    </div>
                                    <h3 class="fw-bold mb-1">Регистрация поставщика</h3>
                                    <p class="text-muted small mb-0">
                                        Заполните данные для создания учётной записи
                                    </p>
                                </div>

                                <!-- Наименование -->
                                <div class="form-floating-custom mb-3">
                                    <div class="input-icon">
                                        <i class="fa-solid fa-store"></i>
                                    </div>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="name"
                                        v-model="form.name"

                                        required
                                        :disabled="submitting"
                                    />
                                    <label for="name">Наименование</label>
                                </div>

                                <!-- Телефон -->
                                <div class="form-floating-custom mb-3">
                                    <div class="input-icon">
                                        <i class="fa-solid fa-phone"></i>
                                    </div>
                                    <input
                                        type="tel"
                                        class="form-control"
                                        id="phone"
                                        v-model="form.phone"

                                        required
                                        :disabled="submitting"
                                    />
                                    <label for="phone">Телефон</label>
                                </div>

                                <!-- Email -->
                                <div class="form-floating-custom mb-3">
                                    <div class="input-icon">
                                        <i class="fa-solid fa-envelope"></i>
                                    </div>
                                    <input
                                        type="email"
                                        class="form-control"
                                        id="email"
                                        v-model="form.email"

                                        :disabled="submitting"
                                    />
                                    <label for="email">Email (необязательно)</label>
                                </div>

                                <!-- Адрес -->
                                <div class="form-floating-custom mb-3">
                                    <div class="input-icon">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </div>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="address"
                                        v-model="form.address"

                                        :disabled="submitting"
                                    />
                                    <label for="address">Адрес (необязательно)</label>
                                </div>

                                <!-- Дата рождения -->
                                <div class="form-floating-custom mb-3">
                                    <div class="input-icon">
                                        <i class="fa-solid fa-cake-candles"></i>
                                    </div>
                                    <input
                                        type="date"
                                        class="form-control"
                                        id="birthday"
                                        v-model="form.birthday"
                                        :disabled="submitting"
                                        :max="todayDate"
                                    />
                                    <label for="birthday">Дата рождения (необязательно)</label>
                                </div>

                                <!-- Описание -->
                                <div class="form-floating-custom mb-3">
                                    <div class="input-icon">
                                        <i class="fa-solid fa-comment"></i>
                                    </div>
                                    <textarea
                                        class="form-control textarea-custom"
                                        id="description"
                                        v-model="form.description"

                                        rows="3"
                                        :disabled="submitting"
                                    ></textarea>
                                    <label for="description">Описание (необязательно)</label>
                                </div>

                                <!-- Пароль -->
                                <div class="form-floating-custom mb-3">
                                    <div class="input-icon">
                                        <i class="fa-solid fa-lock"></i>
                                    </div>
                                    <input
                                        :type="showPassword ? 'text' : 'password'"
                                        class="form-control pe-5"
                                        id="password"
                                        v-model="form.password"

                                        required
                                        :disabled="submitting"
                                        @input="checkPasswordStrength"
                                    />
                                    <label for="password">Пароль</label>
                                    <button
                                        type="button"
                                        class="password-toggle"
                                        @click="showPassword = !showPassword"
                                        tabindex="-1"
                                        :disabled="submitting"
                                    >
                                        <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                    </button>
                                </div>

                                <!-- Индикатор силы пароля -->
                                <Transition name="fade">
                                    <div v-if="form.password" class="password-strength mb-3">
                                        <div class="strength-bars d-flex gap-1 mb-1">
                                            <div
                                                v-for="i in 4"
                                                :key="i"
                                                class="strength-bar flex-fill"
                                                :class="{
                                                    'active': passwordStrength >= i,
                                                    'weak': passwordStrength === 1 && i <= 1,
                                                    'fair': passwordStrength === 2 && i <= 2,
                                                    'good': passwordStrength === 3 && i <= 3,
                                                    'strong': passwordStrength === 4 && i <= 4,
                                                }"
                                            ></div>
                                        </div>
                                        <small class="strength-label" :class="strengthClass">
                                            <i :class="strengthIcon" class="me-1"></i>
                                            {{ strengthLabel }}
                                        </small>
                                    </div>
                                </Transition>

                                <!-- Подтверждение пароля -->
                                <div class="form-floating-custom mb-4">
                                    <div class="input-icon">
                                        <i class="fa-solid fa-lock"></i>
                                    </div>
                                    <input
                                        :type="showConfirmPassword ? 'text' : 'password'"
                                        class="form-control pe-5"
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"

                                        required
                                        :disabled="submitting"
                                    />
                                    <label for="password_confirmation">Повторите пароль</label>
                                    <button
                                        type="button"
                                        class="password-toggle"
                                        @click="showConfirmPassword = !showConfirmPassword"
                                        tabindex="-1"
                                        :disabled="submitting"
                                    >
                                        <i :class="showConfirmPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                    </button>
                                    <div v-if="form.password_confirmation" class="password-match-indicator">
                                        <i :class="passwordsMatch ? 'fa-solid fa-circle-check text-success' : 'fa-solid fa-circle-xmark text-danger'"></i>
                                    </div>
                                </div>

                                <!-- Кнопка -->
                                <button
                                    type="submit"
                                    class="btn btn-success btn-lg w-100 rounded-3 submit-btn"
                                    :disabled="submitting"
                                >
                                    <span v-if="submitting" class="d-flex align-items-center justify-content-center">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        <span>Регистрация...</span>
                                    </span>
                                    <span v-else class="d-flex align-items-center justify-content-center">
                                        <i class="fa-solid fa-user-plus me-2"></i>
                                        <span>Зарегистрироваться</span>
                                    </span>
                                </button>

                                <!-- Подвал -->
                                <div class="text-center mt-4">
                                    <small class="text-muted">
                                        <i class="fa-solid fa-lock me-1"></i>
                                        Данные защищены и передаются по защищённому каналу
                                    </small>
                                </div>
                            </div>
                        </form>
                    </template>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { makeAxiosFactory } from '@/stores/utillites/makeAxiosFactory'
import { useAlertStore } from '@/stores/utillites/useAlertStore'
import axios from "axios";

export default {
    name: 'SupplierRegisterForm',
    props: {
        token: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            alertStore: useAlertStore(),
            validationStatus: 'loading',
            validationReason: '',
            submitting: false,
            registered: false,
            showPassword: false,
            showConfirmPassword: false,
            passwordStrength: 0,
            form: {
                name: '',
                phone: '',
                email: '',
                address: '',
                description: '',
                birthday: '',
                password: '',
                password_confirmation: '',
            },
        }
    },
    computed: {
        passwordsMatch() {
            if (!this.form.password || !this.form.password_confirmation) return false
            return this.form.password === this.form.password_confirmation
        },
        strengthLabel() {
            const labels = ['', 'Очень слабый', 'Слабый', 'Хороший', 'Надёжный']
            return labels[this.passwordStrength] || ''
        },
        strengthClass() {
            const classes = ['', 'text-danger', 'text-warning', 'text-info', 'text-success']
            return classes[this.passwordStrength] || ''
        },
        strengthIcon() {
            const icons = [
                '',
                'fa-solid fa-triangle-exclamation',
                'fa-solid fa-triangle-exclamation',
                'fa-solid fa-shield',
                'fa-solid fa-shield-halved'
            ]
            return icons[this.passwordStrength] || ''
        },
        todayDate() {
            const now = new Date()
            return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`
        },
    },
    mounted() {
        this.validateToken()
    },
    methods: {
        async validateToken() {
            this.validationStatus = 'loading'
            try {
                const { data } = await axios.get(`/api/supplier-invite/validate/${this.token}`)
                if (data.valid) {
                    this.validationStatus = 'valid'
                } else {
                    this.validationStatus = 'invalid'
                    this.validationReason = data.reason
                }
            } catch (e) {
                this.validationStatus = 'invalid'
                this.validationReason = 'Не удалось проверить ссылку. Попробуйте позже.'
            }
        },

        checkPasswordStrength() {
            const password = this.form.password
            if (!password) {
                this.passwordStrength = 0
                return
            }

            let score = 0
            if (password.length >= 8) score++
            if (password.length >= 12) score++
            if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score++
            if (/\d/.test(password)) score++
            if (/[^A-Za-z0-9]/.test(password)) score++

            this.passwordStrength = Math.min(4, score)
        },

        async submitForm() {
            // Обязательные поля
            if (!this.form.name.trim()) {
                this.alertStore.show('Введите наименование', 'warning')
                return
            }
            if (!this.form.phone.trim()) {
                this.alertStore.show('Введите телефон', 'warning')
                return
            }

            // Опциональные поля (если заполнены — валидируем)
            if (this.form.email.trim() && !/\S+@\S+\.\S+/.test(this.form.email)) {
                this.alertStore.show('Введите корректный email', 'warning')
                return
            }

            // Пароль
            if (!this.form.password) {
                this.alertStore.show('Введите пароль', 'warning')
                return
            }
            if (this.form.password.length < 8) {
                this.alertStore.show('Пароль должен содержать минимум 8 символов', 'warning')
                return
            }
            if (this.passwordStrength < 2) {
                this.alertStore.show('Пароль слишком простой. Добавьте цифры и заглавные буквы.', 'warning')
                return
            }
            if (!this.passwordsMatch) {
                this.alertStore.show('Пароли не совпадают', 'warning')
                return
            }

            this.submitting = true
            try {
                const { data } = await makeAxiosFactory(
                    `/supplier-invite/register/${this.token}`,
                    'POST',
                    this.form
                )
                this.registered = true
                this.alertStore.show(data.message, 'success')
            } catch (e) {
                if (e?.response?.status === 422) {
                    await this.validateToken()
                }
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>

<style scoped>
/* 🎨 Декоративные круги */
.register-wrapper {
    overflow: hidden;
}

.decoration-circle {
    position: fixed;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.15;
    z-index: 0;
    pointer-events: none;
}

.circle-1 {
    width: 400px;
    height: 400px;
    background: #198754;
    top: -100px;
    left: -100px;
    animation: float1 20s ease-in-out infinite;
}

.circle-2 {
    width: 300px;
    height: 300px;
    background: #20c997;
    bottom: -50px;
    right: -50px;
    animation: float2 25s ease-in-out infinite;
}

.circle-3 {
    width: 200px;
    height: 200px;
    background: #0dcaf0;
    top: 50%;
    right: 20%;
    animation: float3 18s ease-in-out infinite;
}

@keyframes float1 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(50px, 50px) scale(1.1); }
}

@keyframes float2 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(-40px, -40px) scale(0.9); }
}

@keyframes float3 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(30px, -30px) scale(1.15); }
}

/* 📋 Карточки */
.register-card,
.success-card,
.error-card {
    width: 100%;
    max-width: 550px;
    border-radius: 1rem;
    backdrop-filter: blur(10px);
    animation: cardAppear 0.5s ease;
}

@media (min-width: 1400px) {
    .register-card,
    .success-card,
    .error-card {
        max-width: 600px;
    }
}

@media (max-width: 576px) {
    .register-card,
    .success-card,
    .error-card {
        max-width: 100%;
        border-radius: 0.75rem;
    }
}

@keyframes cardAppear {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* 🎯 Бренд-иконка */
.brand-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #198754 0%, #20c997 100%);
    border-radius: 1rem;
    color: white;
    font-size: 2rem;
    box-shadow: 0 10px 30px rgba(25, 135, 84, 0.3);
    position: relative;
    overflow: hidden;
}

.brand-icon::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

/* 📝 Поля ввода с иконкой */
.form-floating-custom {
    position: relative;
}

.form-floating-custom .input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #d1e7dd 0%, #e8f5e9 100%);
    border-radius: 0.5rem;
    color: #198754;
    font-size: 15px;
    z-index: 5;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
}

.form-floating-custom .textarea-custom {
    min-height: calc(3.5rem + 2px);
    padding: 1rem 0.75rem 1rem 3.5rem;
    height: auto;
}

.form-floating-custom .form-control {
    height: calc(3.5rem + 2px);
    padding: 1rem 0.75rem 1rem 3.5rem;
    border: 2px solid #dee2e6;
    border-radius: 0.75rem;
    background-color: #f8f9fa;
    font-size: 0.95rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-floating-custom label {
    position: absolute;
    left: 3.5rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 0.95rem;
    pointer-events: none;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 0 0.25rem;
    background: transparent;
    z-index: 4;
}

.form-floating-custom .textarea-custom ~ label {
    top: 1.5rem;
}

.form-floating-custom .form-control:focus {
    background-color: #fff;
    border-color: #198754;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.12);
    outline: none;
}

.form-floating-custom .form-control:focus ~ label,
.form-floating-custom .form-control:not(:placeholder-shown) ~ label {
    top: 0;
    transform: translateY(-50%);
    font-size: 0.75rem;
    font-weight: 600;
    color: #198754;
    background-color: #fff;
    padding: 0 0.5rem;
    left: 3rem;
}

.form-floating-custom:focus-within .input-icon {
    background: linear-gradient(135deg, #198754 0%, #20c997 100%);
    color: #fff;
    transform: translateY(-50%) scale(1.05);
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
}

.form-floating-custom .form-control:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
    opacity: 0.7;
}

.form-floating-custom .form-control:disabled ~ .input-icon {
    opacity: 0.5;
}

.form-floating-custom:hover .form-control:not(:disabled):not(:focus) {
    border-color: #adb5bd;
    background-color: #fff;
}

/* 👁️ Кнопка показа/скрытия пароля */
.form-floating-custom .password-toggle {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    border-radius: 0.5rem;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 5;
}

.form-floating-custom .password-toggle:hover {
    color: #198754;
    background: rgba(25, 135, 84, 0.08);
}

.form-floating-custom .password-toggle:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ✅ Индикатор совпадения паролей */
.form-floating-custom .password-match-indicator {
    position: absolute;
    right: 3.25rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1rem;
    z-index: 5;
    pointer-events: none;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-50%) scale(0.8); }
    to { opacity: 1; transform: translateY(-50%) scale(1); }
}

/* 🔒 Индикатор силы пароля */
.password-strength {
    padding: 0 0.5rem;
}

.strength-bars {
    height: 6px;
}

.strength-bar {
    height: 100%;
    background: #e9ecef;
    border-radius: 3px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.strength-bar.weak {
    background: linear-gradient(90deg, #dc3545, #f87171);
}

.strength-bar.fair {
    background: linear-gradient(90deg, #fd7e14, #fbbf24);
}

.strength-bar.good {
    background: linear-gradient(90deg, #0dcaf0, #06b6d4);
}

.strength-bar.strong {
    background: linear-gradient(90deg, #198754, #22c55e);
}

.strength-label {
    font-size: 0.75rem;
    font-weight: 600;
    transition: color 0.3s ease;
}

/* 🌊 Плавное появление/исчезновение */
.fade-enter-active,
.fade-leave-active {
    transition: all 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}

.form-floating-custom .form-control.pe-5 {
    padding-right: 3.5rem !important;
}

/* 🎨 Кнопка (зелёная для поставщика) */
.submit-btn {
    background: linear-gradient(135deg, #198754 0%, #20c997 100%);
    border: none;
    height: 3.5rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    box-shadow: 0 10px 25px rgba(25, 135, 84, 0.3);
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.submit-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s;
}

.submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(25, 135, 84, 0.4);
    background: linear-gradient(135deg, #157347 0%, #1ba383 100%);
}

.submit-btn:hover:not(:disabled)::before {
    left: 100%;
}

.submit-btn:active:not(:disabled) {
    transform: translateY(0);
}

/* 🔄 Состояние загрузки */
.loading-icon {
    position: relative;
    display: inline-block;
}

.pulse-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 3px solid #198754;
    animation: pulseRing 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulseRing {
    0% {
        transform: scale(0.8);
        opacity: 1;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

/* ✅ Состояние успеха */
.success-card {
    border: 2px solid rgba(25, 135, 84, 0.2);
}

.success-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #198754 0%, #20c997 100%);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2.5rem;
    box-shadow: 0 10px 30px rgba(25, 135, 84, 0.4);
    animation: successBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes successBounce {
    0% { transform: scale(0); }
    60% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* ❌ Состояние ошибки */
.error-card {
    border: 2px solid rgba(220, 53, 69, 0.2);
}

.error-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2.5rem;
    box-shadow: 0 10px 30px rgba(220, 53, 69, 0.4);
    animation: errorShake 0.5s ease;
}

@keyframes errorShake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}
</style>
