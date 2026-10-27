
<template>
    <div class="role-switcher">
        <button
            type="button"
            class="role-switcher__trigger"
            data-bs-toggle="modal"
            data-bs-target="#roleModal"
        >
            <span class="role-switcher__trigger-icon">
                <i class="fa-solid fa-users-gear"></i>
            </span>

            <span class="role-switcher__trigger-content">
                <span class="role-switcher__trigger-title">
                    Сменить роль
                </span>
                <span class="role-switcher__trigger-description">
                    Текущая роль: {{ roleLabel }}
                </span>
            </span>

            <span class="role-switcher__trigger-arrow">
                <i class="fa-solid fa-chevron-right"></i>
            </span>
        </button>

        <!-- Модальное окно -->
        <div
            class="modal fade"
            id="roleModal"
            tabindex="-1"
            aria-labelledby="roleModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content role-modal">
                    <div class="modal-header role-modal__header">
                        <div class="role-modal__heading">
                            <div class="role-modal__icon">
                                <i class="fa-solid fa-user-gear"></i>
                            </div>

                            <div>
                                <h2
                                    id="roleModalLabel"
                                    class="modal-title"
                                >
                                    Выбор роли
                                </h2>
                                <p class="role-modal__subtitle mb-0">
                                    Выберите режим работы в системе
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть"
                        ></button>
                    </div>

                    <div class="modal-body role-modal__body">
                        <!-- Текущая роль -->
                        <div class="current-role">
                            <span class="current-role__label">
                                <i class="fa-solid fa-circle-check"></i>
                                Текущая роль
                            </span>

                            <strong class="current-role__value">
                                {{ roleLabel }}
                            </strong>
                        </div>

                        <!-- Базовая роль -->
                        <div
                            v-if="roleLabel !== baseRoleLabel"
                            class="base-role"
                        >
                            <i class="fa-solid fa-shield-halved"></i>

                            <span>
                                Базовая роль:
                                <strong>{{ baseRoleLabel }}</strong>
                            </span>
                        </div>

                        <div class="role-list">
                            <button
                                v-for="role in roleOptions"
                                :key="role.id"
                                type="button"
                                class="role-option"
                                :class="{
                                    'role-option--selected':
                                        selectedRole === role.id,
                                    'role-option--current':
                                        currentRole === role.id,
                                }"
                                :disabled="changingRole"
                                :aria-pressed="selectedRole === role.id"
                                @click="selectedRole = role.id"
                            >
                                <span
                                    class="role-option__icon"
                                    :class="`role-option__icon--${role.color}`"
                                >
                                    <i
                                        class="fa-solid"
                                        :class="role.icon"
                                    ></i>
                                </span>

                                <span class="role-option__content">
                                    <span class="role-option__title">
                                        {{ role.label }}
                                    </span>

                                    <span class="role-option__description">
                                        {{ role.description }}
                                    </span>
                                </span>

                                <span class="role-option__status">
                                    <span
                                        v-if="currentRole === role.id"
                                        class="role-option__current"
                                    >
                                        Текущая
                                    </span>

                                    <i
                                        v-else-if="selectedRole === role.id"
                                        class="fa-solid fa-circle-check"
                                    ></i>

                                    <i
                                        v-else
                                        class="fa-regular fa-circle"
                                    ></i>
                                </span>
                            </button>
                        </div>

                        <p
                            v-if="errorMessage"
                            class="role-error"
                            role="alert"
                        >
                            <i class="fa-solid fa-circle-exclamation"></i>
                            {{ errorMessage }}
                        </p>

                        <div class="role-modal__actions">
                            <button
                                type="button"
                                class="role-cancel"
                                data-bs-dismiss="modal"
                                :disabled="changingRole"
                            >
                                Отмена
                            </button>

                            <button
                                type="button"
                                class="role-confirm"
                                :disabled="
                                    changingRole ||
                                    selectedRole === currentRole
                                "
                                @click="changeRole"
                            >
                                <span
                                    v-if="changingRole"
                                    class="spinner-border spinner-border-sm"
                                    aria-hidden="true"
                                ></span>

                                <i
                                    v-else
                                    class="fa-solid fa-check"
                                ></i>

                                {{ changingRole ? 'Переключение...' : 'Применить роль' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { useUsersStore } from "@/stores/users";

export default {
    name: "RoleSwitcher",

    emits: ["role-changed"],

    data() {
        return {
            userStore: useUsersStore(),
            selectedRole: 0,
            changingRole: false,
            errorMessage: "",

            roles: {
                0: "Пользователь",
                1: "Администратор",
                2: "Поставщик",
                3: "Старший администратор",
                4: "Суперадмин",
            },

            roleOptions: [
                {
                    id: 0,
                    label: "Пользователь",
                    description: "Стандартный доступ к системе",
                    icon: "fa-user",
                    color: "gray",
                },
                {
                    id: 1,
                    label: "Администратор",
                    description: "Работа с доставками и операциями",
                    icon: "fa-user-shield",
                    color: "blue",
                },
                {
                    id: 2,
                    label: "Поставщик",
                    description: "Работа с поставками и товарами",
                    icon: "fa-truck-fast",
                    color: "red",
                },
                {
                    id: 3,
                    label: "Старший администратор",
                    description: "Расширенные административные функции",
                    icon: "fa-user-tie",
                    color: "blue",
                },
                {
                    id: 4,
                    label: "Суперадмин",
                    description: "Управление системой и ролями",
                    icon: "fa-crown",
                    color: "red",
                },
            ],
        };
    },

    computed: {
        currentRole() {
            return Number(this.userStore.self?.role ?? 0);
        },

        roleLabel() {
            return this.roles[this.currentRole] ?? "Неизвестная роль";
        },

        baseRoleLabel() {
            const role = Number(this.userStore.self?.base_role ?? 0);

            return this.roles[role] ?? "Неизвестная роль";
        },
    },

    watch: {
        currentRole: {
            immediate: true,
            handler(role) {
                this.selectedRole = role;
            },
        },
    },

    methods: {
        async changeRole() {
            if (
                this.changingRole ||
                this.selectedRole === this.currentRole
            ) {
                return;
            }

            this.changingRole = true;
            this.errorMessage = "";

            try {
                await this.userStore.setRole(this.selectedRole);

                this.$emit("role-changed", this.selectedRole);

                const element = document.getElementById("roleModal");

                if (element && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getInstance(element)?.hide();
                }
            } catch (error) {
                console.error("Ошибка переключения роли:", error);

                this.errorMessage =
                    "Не удалось сменить роль. Попробуйте ещё раз.";
            } finally {
                this.changingRole = false;
            }
        },
    },
};
</script>

<style scoped>
.role-switcher {
    --role-blue: #2457d6;
    --role-blue-dark: #173b91;
    --role-red: #df3548;
    --role-text: #17233b;
    --role-muted: #778399;
    --role-border: #e5eaf2;

    width: 100%;
}

/* Кнопка открытия */

.role-switcher__trigger {
    display: flex;
    align-items: center;
    gap: 14px;
    width: 100%;
    padding: 17px;
    border: 1px solid var(--role-border);
    border-radius: 16px;
    background: #fff;
    color: var(--role-text);
    text-align: left;
    transition: border-color .2s, box-shadow .2s, transform .2s;
}

.role-switcher__trigger:hover {
    transform: translateY(-1px);
    border-color: #b8cafa;
    box-shadow: 0 8px 24px rgb(23 35 59 / 6%);
}

.role-switcher__trigger-icon {
    display: grid;
    place-items: center;
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    border-radius: 14px;
    background: #edf3ff;
    color: var(--role-blue);
    font-size: 21px;
}

.role-switcher__trigger-content {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.role-switcher__trigger-title {
    font-size: 15px;
    font-weight: 750;
}

.role-switcher__trigger-description {
    color: var(--role-muted);
    font-size: 13px;
    overflow-wrap: anywhere;
}

.role-switcher__trigger-arrow {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    border-radius: 10px;
    background: #f5f7fb;
    color: var(--role-muted);
}

/* Модальное окно */

.role-modal {
    overflow: hidden;
    border: 0;
    border-radius: 20px;
    box-shadow: 0 24px 70px rgb(23 35 59 / 16%);
}

.role-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 23px;
    border-bottom: 1px solid var(--role-border);
}

.role-modal__heading {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
}

.role-modal__icon {
    display: grid;
    place-items: center;
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    border-radius: 14px;
    background: #edf3ff;
    color: var(--role-blue);
    font-size: 20px;
}

.role-modal .modal-title {
    color: var(--role-text);
    font-size: 20px;
    font-weight: 750;
}

.role-modal__subtitle {
    margin-top: 4px;
    color: var(--role-muted);
    font-size: 12px;
    line-height: 1.5;
}

.role-modal__body {
    padding: 22px;
}

/* Текущая и базовая роль */

.current-role {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 16px;
    border: 1px solid #d9e5ff;
    border-radius: 14px;
    background: #f3f7ff;
}

.current-role__label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #61759b;
    font-size: 12px;
    font-weight: 650;
}

.current-role__label i {
    color: var(--role-blue);
}

.current-role__value {
    color: var(--role-blue-dark);
    font-size: 17px;
    overflow-wrap: anywhere;
}

.base-role {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    margin-top: 10px;
    padding: 12px 14px;
    border: 1px solid var(--role-border);
    border-radius: 12px;
    color: #647187;
    background: #fafbfd;
    font-size: 13px;
    line-height: 1.5;
}

.base-role i {
    margin-top: 3px;
    color: var(--role-blue);
}

.base-role strong {
    color: var(--role-text);
}

/* Список ролей */

.role-list {
    display: flex;
    flex-direction: column;
    gap: 9px;
    margin-top: 20px;
}

.role-option {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    min-width: 0;
    padding: 12px;
    border: 1px solid var(--role-border);
    border-radius: 14px;
    background: #fff;
    color: var(--role-text);
    text-align: left;
    transition: border-color .18s, background .18s;
}

.role-option:hover:not(:disabled) {
    border-color: #b8cafa;
    background: #f9fbff;
}

.role-option--selected {
    border-color: var(--role-blue);
    background: #f3f7ff;
}

.role-option--current {
    border-color: #b9cafa;
}

.role-option:disabled {
    cursor: wait;
    opacity: .65;
}

.role-option__icon {
    display: grid;
    place-items: center;
    width: 43px;
    height: 43px;
    flex-shrink: 0;
    border-radius: 12px;
    font-size: 19px;
}

.role-option__icon--gray {
    color: #657186;
    background: #f0f2f6;
}

.role-option__icon--blue {
    color: var(--role-blue);
    background: #edf3ff;
}

.role-option__icon--red {
    color: var(--role-red);
    background: #fff0f1;
}

.role-option__content {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.role-option__title {
    font-size: 14px;
    font-weight: 750;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

.role-option__description {
    color: var(--role-muted);
    font-size: 11px;
    line-height: 1.5;
}

.role-option__status {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
    flex-shrink: 0;
    color: #a0aabc;
    font-size: 15px;
}

.role-option--selected .role-option__status {
    color: var(--role-blue);
}

.role-option__current {
    padding: 5px 7px;
    border-radius: 7px;
    background: #e5edff;
    color: var(--role-blue-dark);
    font-size: 10px;
    font-weight: 750;
}

/* Ошибки и кнопки */

.role-error {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    margin: 16px 0 0;
    padding: 12px;
    border: 1px solid #ffd1d6;
    border-radius: 11px;
    background: #fff3f4;
    color: #b42335;
    font-size: 13px;
    line-height: 1.5;
}

.role-modal__actions {
    display: flex;
    gap: 10px;
    margin-top: 22px;
}

.role-cancel,
.role-confirm {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 45px;
    padding: 11px 16px;
    border-radius: 11px;
    font-size: 13px;
    font-weight: 700;
    transition: background .2s, border-color .2s;
}

.role-cancel {
    flex: 1;
    border: 1px solid var(--role-border);
    background: #fff;
    color: var(--role-text);
}

.role-cancel:hover:not(:disabled) {
    background: #f5f7fb;
}

.role-confirm {
    flex: 1.5;
    border: 1px solid var(--role-blue);
    background: var(--role-blue);
    color: #fff;
}

.role-confirm:hover:not(:disabled) {
    border-color: var(--role-blue-dark);
    background: var(--role-blue-dark);
}

.role-cancel:disabled,
.role-confirm:disabled {
    cursor: not-allowed;
    opacity: .55;
}

@media (max-width: 575.98px) {
    .role-modal {
        border-radius: 16px;
    }

    .role-modal__header {
        padding: 17px;
    }

    .role-modal__body {
        padding: 16px;
    }

    .role-modal .modal-title {
        font-size: 18px;
    }

    .role-option {
        gap: 10px;
        padding: 10px;
    }

    .role-option__icon {
        width: 38px;
        height: 38px;
        font-size: 17px;
    }

    .role-option__title {
        font-size: 13px;
    }

    .role-option__description {
        font-size: 10px;
    }

    .role-option__current {
        font-size: 9px;
    }

    .role-modal__actions {
        flex-direction: column-reverse;
    }

    .role-cancel,
    .role-confirm {
        width: 100%;
        flex: auto;
    }
}
</style>
