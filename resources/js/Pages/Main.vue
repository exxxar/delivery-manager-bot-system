<script setup>
import Layout from "@/Layouts/Layout.vue";

</script>
<template>
    <Layout v-if="!userStore.self?.blocked_at">
        <template #default>

            <router-view/>

        </template>
    </Layout>

    <div class="container py-3" v-else>
        <p class="alert alert-light text-center" >
            Доступ ограничен
        </p>
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
    created() {
        this.userStore.fetchSelf()
    },
    computed: {
        tg() {
            return window.Telegram.WebApp;
        },

        tgUser() {
            const urlParams = new URLSearchParams(this.tg.initData);
            return JSON.parse(urlParams.get('user'));
        },
    },
    mounted() {


    },
    methods: {
        open(url) {
            this.tg.openLink(url)
        },
        goTo(name) {
            this.$router.push({name: name})
        },
    }

}
</script>


