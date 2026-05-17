<template>


    <div class="container-fluid px-0 py-0">
        <!-- Кнопка открытия -->
        <button
            class="btn btn-outline-primary w-100 my-2 p-3"
            data-bs-toggle="modal"
            data-bs-target="#roleModal"
        >
            <i class="fa-solid fa-user-gear me-2"></i>
            Сменить роль
        </button>
    </div>


    <!-- Модалка -->
    <div class="modal fade"
         id="roleModal"
         tabindex="-1"
         aria-labelledby="roleModalLabel"
         aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">

                <!-- Заголовок -->
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalLabel">
                        <i class="fa-solid fa-users-gear me-2"></i>
                        Выбор роли
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <!-- Тело -->
                <div class="modal-body">

                    <!-- Текущая роль -->
                    <div class="alert alert-primary text-center mb-2">
                        Текущая роль:
                        <strong>{{ roleLabel }}</strong>
                    </div>

                    <!-- Базовая роль -->
                    <div class="alert alert-secondary text-center mb-2"
                         v-if="roleLabel !== baseRoleLabel">

                        Базовая роль:
                        <strong>{{ baseRoleLabel }}</strong>
                    </div>

                    <!-- Кнопки -->
                    <div class="d-grid gap-2 mt-4">

                        <button
                            class="btn btn-outline-secondary p-3"
                            :class="{ 'active': selectedRole === 0 }"
                            @click="selectedRole = 0; changeRole()">

                            <i class="fa-solid fa-user me-2"></i>
                            Пользователь
                        </button>

                        <button
                            class="btn btn-outline-primary p-3"
                            :class="{ 'active': selectedRole === 1 }"
                            @click="selectedRole = 1; changeRole()">

                            <i class="fa-solid fa-user-shield me-2"></i>
                            Администратор
                        </button>

                        <button
                            class="btn btn-outline-warning p-3"
                            :class="{ 'active': selectedRole === 3 }"
                            @click="selectedRole = 3; changeRole()">

                            <i class="fa-solid fa-user-tie me-2"></i>
                            Старший администратор
                        </button>

                        <button
                            class="btn btn-outline-danger p-3"
                            :class="{ 'active': selectedRole === 4 }"
                            @click="selectedRole = 4; changeRole()">

                            <i class="fa-solid fa-crown me-2"></i>
                            Суперадмин
                        </button>

                    </div>
                </div>

            </div>
        </div>
    </div>


</template>

<script>

import {useUsersStore} from "@/stores/users";

export default {
    name: 'RoleSwitcher',
    data() {
        return {
            userStore: useUsersStore(),
            selectedRole: useUsersStore().self.role || 0,
            roles: {
                0: 'Пользователь',
                1: 'Администратор',
                2: 'Поставщик',
                3: 'Старший администратор',
                4: 'Суперадмин'
            },
        }
    },
    computed: {
        baseRoleLabel() {
            return this.roles[this.userStore.self.base_role ?? 0]
        },
        roleLabel() {
            return this.roles[this.userStore.self.role ?? 0]
        }
    },
    methods: {
        changeRole() {
            this.userStore.setRole(this.selectedRole)
            this.$emit('role-changed', this.selectedRole)
        }
    }
}
</script>


