<script setup>
import MenuAdmin from "@/Components/MenuAdmin.vue";
import MenuUser from "@/Components/MenuUser.vue";
import MenuAgent from "@/Components/MenuAgent.vue";
import MenuSuperAdmin from "@/Components/MenuSuperAdmin.vue";
import RoleSwitcher from "@/Components/Users/RoleSwitcher.vue";
</script>

<template>


    <div class="container-fluid p-3" v-if="user">
        <template v-if="user.role === 0">
            <MenuUser></MenuUser>
        </template>

        <MenuAdmin v-if="user.role === 3"></MenuAdmin>
        <MenuAgent v-if="user.role === 1"></MenuAgent>

        <template v-if="user.role === 4">
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
