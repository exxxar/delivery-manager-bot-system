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


    <template v-if="user">
        <template v-if="user.role === 0">
            <MenuUser></MenuUser>
        </template>

        <template v-if="user.role>0">
            <!-- Заполнить данные -->
           <div class="row g-2 mb-2">
               <div class="col-12 col-md-6 col-xl-3">
                   <button
                       @click="openPrimaryRegistration"
                       class="btn btn-success w-100">Редактировать свой профиль</button>
               </div>
           </div>


        </template>

        <MenuAdmin v-if="user.role === 3"></MenuAdmin>
        <MenuAgent v-if="user.role === 1"></MenuAgent>

        <template v-if="user.role === 4">
            <MenuSuperAdmin></MenuSuperAdmin>
        </template>

        <template v-if="user.base_role===4">
            <hr>
            <RoleSwitcher ></RoleSwitcher>
        </template>

    </template>

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
    mounted() {
        if (this.userStore.self?.blocked_at != null)
            this.$router.push({name: 'BlockedPage'})

    },
    methods: {
        openPrimaryRegistration() {
            new bootstrap.Modal(document.getElementById('primaryUserModal')).show()
        },
    }

}
</script>
