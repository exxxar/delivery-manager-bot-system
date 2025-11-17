<script setup>
import Layout from "@/Layouts/Layout.vue";

</script>
<template>
    <Layout>
        <template #default>

            <router-view/>

        </template>
    </Layout>
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
    }

}
</script>


