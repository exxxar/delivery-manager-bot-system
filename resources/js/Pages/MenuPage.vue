<script setup>
import MenuAdmin from "@/Components/MenuAdmin.vue";
import MenuUser from "@/Components/MenuUser.vue";
import MenuAgent from "@/Components/MenuAgent.vue";
import MenuSuperAdmin from "@/Components/MenuSuperAdmin.vue";
import RoleSwitcher from "@/Components/Users/RoleSwitcher.vue";

import { useI18n } from "vue-i18n";
const { t } = useI18n();
</script>

<template>


    <div class="container-fluid p-3" v-if="user">
        <template v-if="user.role === 0">
            <MenuUser></MenuUser>
        </template>

        <MenuAdmin v-if="user.role === 3"></MenuAdmin>
        <MenuAgent v-if="user.role === 1"></MenuAgent>

        <template v-if="user.role === 4">
<!--            <h1>{{ t("customer.title") }}</h1>
            <p>{{ t("customer.name") }}: Иван</p>
            <button>{{ t("common.save") }}</button>

            <p>{{ t("common.hello", { name: "Алексей" }) }}</p>

            &lt;!&ndash; Подстановка числа &ndash;&gt;
            <p>{{ t("common.items", { count: 5 }) }}</p>

            &lt;!&ndash; Несколько переменных &ndash;&gt;
            <p>{{ t("common.order", { id: 123, date: "10.12.2025" }) }}</p>-->
            <MenuSuperAdmin></MenuSuperAdmin>
        </template>

        <RoleSwitcher v-if="user.base_role===4"></RoleSwitcher>
    </div>

</template>
<script>
import {useUsersStore} from "@/stores/users";

export default {
    data() {
        return {
            userStore: useUsersStore()
        }
    },
    computed: {
        tg() {
            return window.Telegram.WebApp;
        },
        user() {
            return this.userStore.self || null
        },
    },
    methods: {}

}
</script>
