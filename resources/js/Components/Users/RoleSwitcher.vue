<template>

    <div class="container">
        <div class="form-floating w-100">

            <select
                id="roleSelect"
                class="form-select"
                v-model="selectedRole"
                @change="changeRole"
            >
                <option :value="0">Пользователь</option>
                <option :value="1">Агент</option>
                <option :value="3">Администратор</option>
                <option :value="4">Суперадмин</option>
            </select>

            <label for="roleSelect" class="form-label">Сменить роль</label>
        </div>
        <p class="mt-2">
            Текущая роль: <strong>{{ roleLabel }}</strong>
        </p>
        <p class="mt-2" v-if="roleLabel!==baseRoleLabel">
            Базовая роль роль: <strong>{{ baseRoleLabel }}</strong>
        </p>
    </div>

</template>

<script>
export default {
    name: 'RoleSwitcher',
    data() {
        return {
            selectedRole: window.botUser?.role ?? 0,
            roles: {
                0: 'Пользователь',
                1: 'Агент',
                2: 'Поставщик',
                3: 'Администратор',
                4: 'Суперадмин'
            },
        }
    },
    computed: {
        baseRoleLabel() {
            return this.roles[window.botUser?.base_role ?? 0]
        },
        roleLabel() {
            return this.roles[window.botUser?.role ?? 0]
        }
    },
    methods: {
        changeRole() {
            window.botUser.role = this.selectedRole
            this.$emit('role-changed', this.selectedRole)
        }
    }
}
</script>


