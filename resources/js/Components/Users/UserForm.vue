<template>
    <div class="user-form">
        <section v-if="initialData?.agent?.user_info" class="info-card mb-4">
            <div class="section-heading">
                <span class="section-icon"><i class="fa-solid fa-circle-info"></i></span>
                <div>
                    <h6 class="section-title">Информация о пользователе</h6>
                    <p class="section-caption">Дополнительные сведения из профиля</p>
                </div>
            </div>
            <div class="user-info-content" v-html="initialData.agent.user_info"></div>
        </section>

        <form @submit.prevent="submitForm">
            <section class="form-section mb-4">
                <div class="section-heading">
                    <span class="section-icon"><i class="fa-solid fa-user"></i></span>
                    <div>
                        <h6 class="section-title">Основные данные</h6>
                        <p class="section-caption">Контактная информация и дата рождения</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-floating">
                            <input
                                v-model="form.name"
                                type="text"
                                class="form-control"
                                id="user-form-name"
                                placeholder="Имя"
                                autocomplete="name"
                                required
                            >
                            <label for="user-form-name"><i class="fa-solid fa-user me-2"></i>Имя</label>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-floating">
                            <input
                                v-model="form.phone"
                                v-mask="'+7(###) ###-##-##'"
                                type="text"
                                class="form-control"
                                id="user-form-phone"
                                placeholder="Телефон"
                                autocomplete="tel"
                                required
                            >
                            <label for="user-form-phone"><i class="fa-solid fa-phone me-2"></i>Телефон</label>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-floating">
                            <input
                                v-model="form.email"
                                type="email"
                                class="form-control"
                                id="user-form-email"
                                placeholder="Email"
                                autocomplete="email"
                                required
                            >
                            <label for="user-form-email"><i class="fa-solid fa-envelope me-2"></i>Email</label>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-floating">
                            <input
                                v-model="form.birthday"
                                type="date"
                                class="form-control"
                                id="user-form-birthday"
                                required
                            >
                            <label for="user-form-birthday"><i class="fa-solid fa-cake-candles me-2"></i>Дата рождения</label>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-floating">
                            <input
                                v-model="form.region"
                                type="text"
                                class="form-control"
                                id="user-form-region"
                                placeholder="Регион"
                                autocomplete="address-level1"
                            >
                            <label for="user-form-region"><i class="fa-solid fa-location-dot me-2"></i>Регион</label>
                        </div>
                    </div>
                </div>
            </section>

            <section class="form-section mb-4">
                <div class="section-heading">
                    <span class="section-icon section-icon--blue"><i class="fa-solid fa-shield-halved"></i></span>
                    <div>
                        <h6 class="section-title">Права доступа</h6>
                        <p class="section-caption">Выберите роль пользователя в системе</p>
                    </div>
                </div>

                <div class="form-floating">
                    <select v-model="form.role" class="form-select" id="user-form-role" required>
                        <option :value="0">Пользователь</option>
                        <option :value="1">Администратор</option>
                        <option :value="2">Поставщик</option>
                        <option :value="3">Старший администратор</option>
                        <option :value="4">Суперадмин</option>
                    </select>
                    <label for="user-form-role"><i class="fa-solid fa-user-tag me-2"></i>Роль</label>
                </div>
            </section>

            <button type="submit" class="btn btn-save w-100" :disabled="userStore.loading">
                <span v-if="userStore.loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                <i v-else class="fa-solid fa-floppy-disk me-2"></i>
                {{ isEdit ? 'Сохранить изменения' : 'Создать пользователя' }}
            </button>
        </form>
    </div>
</template>

<script>
import { useUsersStore } from "@/stores/users";

export default {
    name: 'UserForm',
    props: {
        initialData: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            userStore: useUsersStore(),
            form: {
                name: "",
                email: "",
                birthday: "",
                password: "",
                phone: "",
                region: "",
                role: 0,
            },
            isEdit: false
        }
    },
    created() {
        if (this.initialData) {
            this.form = { ...this.initialData }
            this.form.phone = this.initialData.agent?.phone || ''
            this.form.email = this.initialData.agent?.email || ''
            this.form.region = this.initialData.agent?.region || ''
            this.form.birthday = this.form.birthday
                ? new Date(this.form.birthday).toISOString().slice(0, 10)
                : ''
            this.isEdit = true
        }
    },
    methods: {
        async submitForm() {
            await this.userStore.update(this.form.id, this.form)
            this.$emit("saved")
        }
    }
}
</script>

<style scoped>
.user-form {
    --user-form-navy: #172b4d;
    --user-form-blue: #2563eb;
    --user-form-red: #dc3545;
    color: var(--user-form-navy);
}

.info-card,
.form-section {
    padding: 1.15rem;
    border: 1px solid #e6ebf2;
    border-radius: 1rem;
    background: #fff;
    box-shadow: 0 8px 24px rgba(23, 43, 77, 0.055);
}

.info-card {
    border-top: 3px solid var(--user-form-blue);
}

.section-heading {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-bottom: 1.15rem;
}

.section-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 2.65rem;
    width: 2.65rem;
    height: 2.65rem;
    border-radius: 0.85rem;
    color: var(--user-form-red);
    background: #fff0f1;
    font-size: 1.1rem;
}

.section-icon--blue {
    color: var(--user-form-blue);
    background: #edf4ff;
}

.section-title {
    margin: 0;
    color: var(--user-form-navy);
    font-size: 1rem;
    font-weight: 750;
}

.section-caption {
    margin: 0.2rem 0 0;
    color: #718096;
    font-size: 0.82rem;
}

.user-form :deep(.form-control),
.user-form :deep(.form-select) {
    min-height: 3.55rem;
    border-color: #dce4ef;
    border-radius: 0.8rem;
    color: var(--user-form-navy);
    background-color: #fff;
    box-shadow: none;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.user-form :deep(.form-control:focus),
.user-form :deep(.form-select:focus) {
    border-color: var(--user-form-blue);
    box-shadow: 0 0 0 0.22rem rgba(37, 99, 235, 0.12);
}

.user-form :deep(.form-floating > label) {
    color: #718096;
}

.user-form :deep(.form-floating > label i) {
    color: var(--user-form-blue);
}

.user-info-content {
    color: #4b5b72;
    line-height: 1.65;
    overflow-wrap: anywhere;
}

.user-info-content :deep(p:last-child) {
    margin-bottom: 0;
}

.btn-save {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 3.5rem;
    padding: 0.9rem 1.25rem;
    border: 0;
    border-radius: 0.85rem;
    color: #fff;
    background: linear-gradient(115deg, #2563eb, #1746b8);
    box-shadow: 0 8px 18px rgba(37, 99, 235, 0.2);
    font-weight: 700;
    transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
}

.btn-save:hover:not(:disabled) {
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 11px 22px rgba(37, 99, 235, 0.27);
}

.btn-save:disabled {
    cursor: wait;
    opacity: 0.7;
}

@media (max-width: 575.98px) {
    .info-card,
    .form-section {
        padding: 0.9rem;
        border-radius: 0.85rem;
    }

    .section-heading {
        gap: 0.65rem;
    }
}
</style>
