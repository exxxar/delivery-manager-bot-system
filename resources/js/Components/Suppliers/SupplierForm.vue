<template>
    <form class="supplier-form" @submit.prevent="submitForm">
        <div class="form-heading">
            <div class="form-heading__icon">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <div>
                <h5 class="form-heading__title">{{ isEdit ? 'Редактирование поставщика' : 'Новый поставщик' }}</h5>
                <p class="form-heading__subtitle">{{ isSimple ? 'Укажите основные контактные данные' : 'Заполните информацию о поставщике' }}</p>
            </div>
        </div>

        <div class="form-section">
            <div class="section-caption">
                <i class="fa-solid fa-address-card"></i>
                <span>Основная информация</span>
            </div>

            <div class="field-group">
                <label for="supplier-name" class="field-label">Имя поставщика <span class="required-mark">*</span></label>
                <div class="field-control">
                    <i class="fa-regular fa-user field-icon"></i>
                    <input
                        id="supplier-name"
                        v-model.trim="form.name"
                        type="text"
                        class="form-control"
                        placeholder="Например, ООО «Поставка»"
                        autocomplete="organization"
                        required
                    >
                </div>
            </div>

            <div class="field-group">
                <label for="supplier-phone" class="field-label">Телефон <span class="required-mark">*</span></label>
                <div class="field-control">
                    <i class="fa-solid fa-phone field-icon"></i>
                    <input
                        id="supplier-phone"
                        v-model="form.phone"
                        v-mask="'+7(###) ###-##-##'"
                        type="tel"
                        class="form-control"
                        placeholder="+7 (___) ___-__-__"
                        autocomplete="tel"
                        required
                    >
                </div>
            </div>
        </div>

        <template v-if="!isSimple">
            <div class="form-section">
                <div class="section-caption">
                    <i class="fa-solid fa-building"></i>
                    <span>Дополнительные сведения</span>
                </div>

                <div class="field-group">
                    <label for="supplier-description" class="field-label">Описание</label>
                    <textarea
                        id="supplier-description"
                        v-model="form.description"
                        class="form-control form-textarea"
                        placeholder="Чем занимается поставщик, какие товары или услуги предлагает"
                        rows="3"
                    ></textarea>
                </div>

                <div class="field-group">
                    <label for="supplier-address" class="field-label">Адрес</label>
                    <div class="field-control">
                        <i class="fa-solid fa-location-dot field-icon"></i>
                        <input
                            id="supplier-address"
                            v-model.trim="form.address"
                            type="text"
                            class="form-control"
                            placeholder="Юридический или фактический адрес"
                            autocomplete="street-address"
                        >
                    </div>
                </div>

                <div class="field-group">
                    <label for="supplier-work-phone" class="field-label">Рабочий телефон</label>
                    <div class="field-control">
                        <i class="fa-solid fa-headset field-icon"></i>
                        <input
                            id="supplier-work-phone"
                            v-model="form.work_phone"
                            v-mask="'+7(###) ###-##-##'"
                            type="tel"
                            class="form-control"
                            placeholder="+7 (___) ___-__-__"
                        >
                    </div>
                </div>

                <div v-if="(user?.role || 0) >= 3" class="field-group">
                    <label for="supplier-percent" class="field-label">Процент, %</label>
                    <div class="field-control">
                        <i class="fa-solid fa-percent field-icon"></i>
                        <input
                            id="supplier-percent"
                            v-model.number="form.percent"
                            type="number"
                            min="0"
                            max="100"
                            step="0.01"
                            class="form-control"
                            placeholder="8"
                        >
                    </div>
                </div>

                <div class="field-grid">
                    <div class="field-group">
                        <label for="supplier-birthday" class="field-label">Дата рождения</label>
                        <div class="field-control">
                            <i class="fa-regular fa-calendar field-icon"></i>
                            <input
                                id="supplier-birthday"
                                v-model="form.birthday"
                                type="date"
                                class="form-control"
                            >
                        </div>
                    </div>
                    <div class="field-group">
                        <label for="supplier-email" class="field-label">Email</label>
                        <div class="field-control">
                            <i class="fa-regular fa-envelope field-icon"></i>
                            <input
                                id="supplier-email"
                                v-model.trim="form.email"
                                type="email"
                                class="form-control"
                                placeholder="name@example.com"
                                autocomplete="email"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary supplier-submit">
                <i :class="isEdit ? 'fa-solid fa-floppy-disk' : 'fa-solid fa-plus'"></i>
                <span>{{ isEdit ? 'Сохранить изменения' : 'Сохранить поставщика' }}</span>
            </button>
            <p class="form-hint"><span class="required-mark">*</span> — обязательные поля</p>
        </div>
    </form>
</template>

<script>
import { useSuppliersStore } from "@/stores/suppliers";
import { useUsersStore } from "@/stores/users";

export default {
    name: 'SupplierForm',
    props: {
        isSimple: {
            type: Boolean,
            default: false
        },
        initialData: {
            type: Object,
            default: null
        }
    },
    emits: ['saved'],
    computed: {
        user() {
            return this.userStore.self || null
        },
    },
    data() {
        return {
            userStore: useUsersStore(),
            suppliersStore: useSuppliersStore(),
            isEdit: false,
            form: {
                name: '',
                description: '',
                address: '',
                phone: '',
                work_phone: '',
                percent: 8,
                birthday: '',
                email: ''
            }
        }
    },
    created() {
        if (this.initialData) {
            this.form = { ...this.form, ...this.initialData }
            this.isEdit = true
        }
    },
    methods: {
        submitForm() {
            const supplier = this.isEdit
                ? this.suppliersStore.update(this.form.id, this.form)
                : this.suppliersStore.create(this.form)

            supplier.then((resp) => {
                this.$emit("saved", resp)
            })
        }
    }
}
</script>

<style scoped>
.supplier-form {
    --supplier-blue: #2457d6;
    --supplier-blue-dark: #183fa8;
    --supplier-red: #e5484d;
    --supplier-ink: #17233c;
    --supplier-muted: #738099;
    --supplier-border: #e2e8f2;
    color: var(--supplier-ink);
}

.form-heading {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 24px;
}

.form-heading__icon {
    display: grid;
    place-items: center;
    flex: 0 0 48px;
    width: 48px;
    height: 48px;
    border-radius: 15px;
    color: var(--supplier-blue);
    background: #edf3ff;
    font-size: 1.2rem;
}

.form-heading__title {
    margin: 0 0 4px;
    color: var(--supplier-ink);
    font-size: 1.08rem;
    font-weight: 750;
}

.form-heading__subtitle {
    margin: 0;
    color: var(--supplier-muted);
    font-size: .88rem;
    line-height: 1.45;
}

.form-section {
    padding: 20px;
    margin-bottom: 16px;
    border: 1px solid var(--supplier-border);
    border-radius: 18px;
    background: #fff;
}

.section-caption {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 18px;
    color: var(--supplier-ink);
    font-size: .94rem;
    font-weight: 750;
}

.section-caption > i {
    color: var(--supplier-blue);
}

.field-group {
    min-width: 0;
    margin-bottom: 17px;
}

.field-group:last-child {
    margin-bottom: 0;
}

.field-label {
    display: block;
    margin-bottom: 7px;
    color: #43516a;
    font-size: .84rem;
    font-weight: 650;
}

.required-mark {
    color: var(--supplier-red);
}

.field-control {
    position: relative;
}

.field-icon {
    position: absolute;
    z-index: 2;
    top: 50%;
    left: 14px;
    width: 17px;
    color: #8290a8;
    text-align: center;
    transform: translateY(-50%);
    pointer-events: none;
}

.field-control .form-control {
    min-height: 48px;
    padding: 12px 14px 12px 42px;
    border: 1px solid var(--supplier-border);
    border-radius: 11px;
    color: var(--supplier-ink);
    background-color: #fbfcff;
    box-shadow: none;
    transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
}

.field-control .form-control:focus,
.form-textarea:focus {
    border-color: var(--supplier-blue);
    background-color: #fff;
    box-shadow: 0 0 0 3px rgb(36 87 214 / 11%);
}

.field-control .form-control::placeholder,
.form-textarea::placeholder {
    color: #a0aabc;
}

.form-textarea {
    min-height: 100px;
    padding: 13px 14px;
    border: 1px solid var(--supplier-border);
    border-radius: 11px;
    background: #fbfcff;
    resize: vertical;
    box-shadow: none;
}

.field-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.form-actions {
    margin-top: 20px;
}

.supplier-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    min-height: 52px;
    padding: 13px 18px;
    border: 0;
    border-radius: 13px;
    background: linear-gradient(110deg, var(--supplier-blue), #3677ed);
    box-shadow: 0 7px 18px rgb(36 87 214 / 20%);
    font-weight: 700;
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}

.supplier-submit:hover {
    background: linear-gradient(110deg, var(--supplier-blue-dark), var(--supplier-blue));
    box-shadow: 0 9px 22px rgb(36 87 214 / 27%);
    transform: translateY(-1px);
}

.supplier-submit:active {
    transform: translateY(0);
}

.form-hint {
    margin: 10px 0 0;
    color: var(--supplier-muted);
    font-size: .78rem;
    text-align: center;
}

@media (max-width: 575.98px) {
    .form-heading {
        align-items: flex-start;
        margin-bottom: 18px;
    }

    .form-heading__icon {
        flex-basis: 42px;
        width: 42px;
        height: 42px;
        border-radius: 13px;
    }

    .form-section {
        padding: 16px;
        border-radius: 15px;
    }

    .field-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 0;
    }
}
</style>
