<template>
    <!-- Проверка токена -->
    <div v-if="validationStatus === 'loading'" class="text-center py-5">
        <div class="loading-icon mb-4">
            <div class="pulse-ring"></div>
            <i class="fa-solid fa-shield-halved fa-3x text-primary"></i>
        </div>
        <h4 class="fw-bold mb-2">Проверяем ссылку...</h4>
        <p class="text-muted mb-0">Убеждаемся, что она действительна</p>
    </div>

    <!-- Ошибка валидации токена -->
    <div v-else-if="validationStatus === 'invalid'" class="card border-0 shadow-lg error-card mx-auto">
        <div class="card-body text-center p-5">
            <div class="error-icon mb-4">
                <i class="fa-solid fa-xmark"></i>
            </div>
            <h4 class="fw-bold mb-3">Ссылка недействительна</h4>
            <div class="alert alert-light border-start border-danger border-4 mb-0" role="alert">
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
        <div v-if="registered" class="card border-0 shadow-lg success-card mx-auto">
            <div class="card-body text-center p-5">
                <div class="success-icon mb-4">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h4 class="fw-bold mb-2">Регистрация успешна!</h4>
                <p class="text-muted mb-4">
                    Ваша учётная запись создана.<br>
                    Выполняется вход в систему...
                </p>
                <div class="spinner-border text-primary mb-3" role="status"></div>
            </div>
        </div>

        <!-- Сама форма -->
        <form v-else @submit.prevent="submitForm" class="card border-0 shadow-lg register-card mx-auto">
            <div class="card-body p-4 p-md-5">

                <!-- Имя -->
                <div class="form-floating-custom mb-3">
                    <div class="input-icon"><i class="fa-solid fa-user"></i></div>
                    <input type="text" class="form-control" id="name" v-model="form.name" required :disabled="submitting" />
                    <label for="name">ФИО</label>
                </div>

                <!-- Email -->
                <div class="form-floating-custom mb-3">
                    <div class="input-icon"><i class="fa-solid fa-envelope"></i></div>
                    <input type="email" class="form-control" id="email" v-model="form.email" required :disabled="submitting" />
                    <label for="email">Email</label>
                </div>

                <!-- Телефон -->
                <div class="form-floating-custom mb-3">
                    <div class="input-icon"><i class="fa-solid fa-phone"></i></div>
                    <input type="tel" class="form-control" id="phone" v-model="form.phone" required :disabled="submitting" />
                    <label for="phone">Телефон</label>
                </div>

                <!-- Регион -->
                <div class="form-floating-custom mb-4">
                    <div class="input-icon"><i class="fa-solid fa-location-dot"></i></div>
                    <input type="text" class="form-control" id="region" v-model="form.region" required :disabled="submitting" />
                    <label for="region">Регион</label>
                </div>

                <!-- Пароль -->
                <div class="form-floating-custom mb-3">
                    <div class="input-icon"><i class="fa-solid fa-lock"></i></div>
                    <input :type="showPassword ? 'text' : 'password'" class="form-control pe-5" id="password" v-model="form.password" required :disabled="submitting" @input="checkPasswordStrength" />
                    <label for="password">Пароль (мин. 8 символов)</label>
                    <button type="button" class="password-toggle" @click="showPassword = !showPassword" tabindex="-1" :disabled="submitting">
                        <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                    </button>
                </div>

                <!-- Индикатор силы пароля (только для визуальной подсказки, не блокирует) -->
                <Transition name="fade">
                    <div v-if="form.password" class="password-strength mb-3">
                        <div class="strength-bars d-flex gap-1 mb-1">
                            <div v-for="i in 4" :key="i" class="strength-bar flex-fill" :class="{
                                'active': passwordStrength >= i,
                                'weak': passwordStrength === 1 && i <= 1,
                                'fair': passwordStrength === 2 && i <= 2,
                                'good': passwordStrength === 3 && i <= 3,
                                'strong': passwordStrength === 4 && i <= 4,
                            }"></div>
                        </div>
                        <small class="strength-label" :class="strengthClass">
                            <i :class="strengthIcon" class="me-1"></i>
                            {{ strengthLabel }}
                        </small>
                    </div>
                </Transition>

                <!-- Подтверждение пароля -->
                <div class="form-floating-custom mb-4">
                    <div class="input-icon"><i class="fa-solid fa-lock"></i></div>
                    <input :type="showConfirmPassword ? 'text' : 'password'" class="form-control pe-5" id="password_confirmation" v-model="form.password_confirmation" required :disabled="submitting" />
                    <label for="password_confirmation">Повторите пароль</label>
                    <button type="button" class="password-toggle" @click="showConfirmPassword = !showConfirmPassword" tabindex="-1" :disabled="submitting">
                        <i :class="showConfirmPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                    </button>
                    <div v-if="form.password_confirmation" class="password-match-indicator">
                        <i :class="passwordsMatch ? 'fa-solid fa-circle-check text-success' : 'fa-solid fa-circle-xmark text-danger'"></i>
                    </div>
                </div>

                <!-- 🔹 INLINE СООБЩЕНИЕ ОБ ОШИБКЕ ИЛИ УСПЕХЕ -->
                <div v-if="formMessage.text" :class="`alert alert-${formMessage.type} mb-3 d-flex align-items-center`" role="alert">
                    <i :class="formMessage.type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation'" class="me-2 fs-5"></i>
                    <div>{{ formMessage.text }}</div>
                </div>

                <!-- Кнопка -->
                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 submit-btn" :disabled="submitting">
                    <span v-if="submitting" class="d-flex align-items-center justify-content-center">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
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
</template>

<script setup>
import { useAlertStore } from '@/stores/utillites/useAlertStore'
import { makeAxiosFactory } from '@/stores/utillites/makeAxiosFactory'
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    token: { type: String, required: true }
})

const alertStore = useAlertStore()
const validationStatus = ref('loading')
const validationReason = ref('')
const submitting = ref(false)
const registered = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const passwordStrength = ref(0)

// 🔹 Новое состояние для сообщений прямо в форме
const formMessage = ref({ type: '', text: '' })

const form = ref({
    name: '',
    email: '',
    phone: '',
    region: '',
    password: '',
    password_confirmation: '',
})

const passwordsMatch = computed(() => {
    if (!form.value.password || !form.value.password_confirmation) return false
    return form.value.password === form.value.password_confirmation
})

const strengthLabel = computed(() => {
    const labels = ['', 'Слабый', 'Средний', 'Хороший', 'Отличный']
    return labels[passwordStrength.value] || ''
})

const strengthClass = computed(() => {
    const classes = ['', 'text-danger', 'text-warning', 'text-info', 'text-success']
    return classes[passwordStrength.value] || ''
})

const strengthIcon = computed(() => {
    const icons = ['', 'fa-solid fa-triangle-exclamation', 'fa-solid fa-shield', 'fa-solid fa-shield', 'fa-solid fa-shield-halved']
    return icons[passwordStrength.value] || ''
})

const checkPasswordStrength = () => {
    const password = form.value.password
    if (!password) {
        passwordStrength.value = 0
        return
    }
    let score = 0
    if (password.length >= 8) score++
    if (password.length >= 12) score++
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score++
    if (/\d/.test(password)) score++
    if (/[^A-Za-z0-9]/.test(password)) score++
    passwordStrength.value = Math.min(4, score)
}

const validateToken = async () => {
    validationStatus.value = 'loading'
    try {
        const { data } = await makeAxiosFactory(`/admin-invite/validate/${props.token}`, 'GET')
        if (data.valid) {
            validationStatus.value = 'valid'
        } else {
            validationStatus.value = 'invalid'
            validationReason.value = data.reason
        }
    } catch (e) {
        validationStatus.value = 'invalid'
        validationReason.value = 'Не удалось проверить ссылку. Попробуйте позже.'
    }
}

const submitForm = async () => {
    // 🔹 Сбрасываем сообщение при новой попытке
    formMessage.value = { type: '', text: '' }

    // Простая валидация (без требований к спецсимволам и регистру)
    if (!form.value.name.trim()) {
        formMessage.value = { type: 'danger', text: 'Введите ФИО' }
        return
    }
    if (!form.value.email.trim() || !/\S+@\S+\.\S+/.test(form.value.email)) {
        formMessage.value = { type: 'danger', text: 'Введите корректный email' }
        return
    }
    if (!form.value.phone.trim()) {
        formMessage.value = { type: 'danger', text: 'Введите телефон' }
        return
    }
    if (!form.value.region.trim()) {
        formMessage.value = { type: 'danger', text: 'Введите регион' }
        return
    }
    if (form.value.password.length < 8) {
        formMessage.value = { type: 'danger', text: 'Пароль должен содержать минимум 8 символов' }
        return
    }
    if (!passwordsMatch.value) {
        formMessage.value = { type: 'danger', text: 'Пароли не совпадают' }
        return
    }

    submitting.value = true
    try {
        const { data } = await makeAxiosFactory(`/admin-invite/register/${props.token}`, 'POST', form.value)

        registered.value = true
        formMessage.value = { type: 'success', text: data.message || 'Регистрация прошла успешно!' }

        // Если бэкенд возвращает флаг автологина, можно сделать редирект
        if (data.auto_login) {
            setTimeout(() => {
                window.location.href = '/'
            }, 1500)
        }
    } catch (e) {
        // Показываем конкретную ошибку от сервера над кнопкой
        if (e?.response?.status === 422) {
            const errors = e.response.data.errors
            const firstError = Object.values(errors)[0]?.[0] || 'Ошибка валидации данных'
            formMessage.value = { type: 'danger', text: firstError }

            // Если токен стал невалидным из-за ошибок, обновим статус
            await validateToken()
        } else {
            formMessage.value = {
                type: 'danger',
                text: e?.response?.data?.message || 'Произошла ошибка при регистрации. Попробуйте позже.'
            }
        }
    } finally {
        submitting.value = false
    }
}

onMounted(() => {
    validateToken()
})
</script>

<style scoped>
/* 🔹 Карточки */
.register-card, .success-card, .error-card {
    width: 100%;
    max-width: 600px;
    border-radius: 1.25rem;
    backdrop-filter: blur(10px);
    animation: cardAppear 0.5s ease;
}

@keyframes cardAppear {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* 🔹 Поля ввода */
.form-floating-custom { position: relative; }

.form-floating-custom .input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #e7f1ff 0%, #f0f7ff 100%);
    border-radius: 0.6rem;
    color: #0d6efd;
    font-size: 15px;
    z-index: 5;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
}

.form-floating-custom .form-control {
    height: calc(3.5rem + 2px);
    padding: 1rem 0.75rem 1rem 3.6rem;
    border: 2px solid #dee2e6;
    border-radius: 0.75rem;
    background-color: #f8f9fa;
    font-size: 0.95rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-floating-custom label {
    position: absolute;
    left: 3.6rem;
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

.form-floating-custom .form-control:focus {
    background-color: #fff;
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.12);
    outline: none;
}

.form-floating-custom .form-control:focus ~ label,
.form-floating-custom .form-control:not(:placeholder-shown) ~ label {
    top: 0;
    transform: translateY(-50%);
    font-size: 0.75rem;
    font-weight: 600;
    color: #0d6efd;
    background-color: #fff;
    padding: 0 0.5rem;
    left: 3.1rem;
}

.form-floating-custom:focus-within .input-icon {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    color: #fff;
    transform: translateY(-50%) scale(1.05);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.form-floating-custom .form-control:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
    opacity: 0.7;
}

.form-floating-custom .form-control:disabled ~ .input-icon { opacity: 0.5; }

.form-floating-custom:hover .form-control:not(:disabled):not(:focus) {
    border-color: #adb5bd;
    background-color: #fff;
}

/* 🔹 Кнопка показа пароля */
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
    color: #0d6efd;
    background: rgba(13, 110, 253, 0.08);
}

.form-floating-custom .password-toggle:disabled { opacity: 0.5; cursor: not-allowed; }
.form-floating-custom .form-control.pe-5 { padding-right: 3.5rem !important; }

/* 🔹 Индикатор совпадения */
.form-floating-custom .password-match-indicator {
    position: absolute;
    right: 3.4rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.1rem;
    z-index: 5;
    pointer-events: none;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-50%) scale(0.8); }
    to { opacity: 1; transform: translateY(-50%) scale(1); }
}

/* 🔹 Сила пароля (визуальная подсказка) */
.password-strength { padding: 0 0.5rem; }
.strength-bars { height: 6px; }
.strength-bar {
    height: 100%;
    background: #e9ecef;
    border-radius: 3px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.strength-bar.weak { background: linear-gradient(90deg, #dc3545, #f87171); }
.strength-bar.fair { background: linear-gradient(90deg, #fd7e14, #fbbf24); }
.strength-bar.good { background: linear-gradient(90deg, #0dcaf0, #06b6d4); }
.strength-bar.strong { background: linear-gradient(90deg, #198754, #22c55e); }
.strength-label { font-size: 0.75rem; font-weight: 600; transition: color 0.3s ease; }

/* 🔹 Кнопка отправки */
.submit-btn {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border: none;
    height: 3.5rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    box-shadow: 0 10px 25px rgba(13, 110, 253, 0.3);
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
    box-shadow: 0 15px 35px rgba(13, 110, 253, 0.4);
    background: linear-gradient(135deg, #0b5ed7 0%, #5a0fc8 100%);
}

.submit-btn:hover:not(:disabled)::before { left: 100%; }
.submit-btn:active:not(:disabled) { transform: translateY(0); }

/* 🔹 Состояния загрузки, успеха и ошибки */
.loading-icon { position: relative; display: inline-block; }
.pulse-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 3px solid #0d6efd;
    animation: pulseRing 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulseRing {
    0% { transform: scale(0.8); opacity: 1; }
    100% { transform: scale(1.5); opacity: 0; }
}

.success-card { border: 2px solid rgba(25, 135, 84, 0.2); }
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

.error-card { border: 2px solid rgba(220, 53, 69, 0.2); }
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

/* 🔹 Анимации Vue */
.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-5px); }

/* 🔹 Адаптив */
@media (max-width: 576px) {
    .register-card, .success-card, .error-card { max-width: 100%; border-radius: 1rem; }
    .card-body { padding: 1.5rem !important; }
    .form-floating-custom .form-control { height: calc(3.2rem + 2px); padding: 0.9rem 0.75rem 0.9rem 3.4rem; font-size: 0.9rem; }
    .form-floating-custom .input-icon { width: 34px; height: 34px; left: 0.8rem; font-size: 14px; }
    .form-floating-custom label { left: 3.4rem; font-size: 0.9rem; }
    .form-floating-custom .form-control:focus ~ label,
    .form-floating-custom .form-control:not(:placeholder-shown) ~ label { left: 3rem; }
    .form-floating-custom .password-toggle { right: 0.5rem; width: 34px; height: 34px; }
    .form-floating-custom .password-match-indicator { right: 3.2rem; }
}
</style>
