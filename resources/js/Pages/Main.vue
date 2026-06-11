<script setup>
import Layout from "@/Layouts/Layout.vue";

defineProps({
    api_type: String,
});
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
import {useConfigStore} from "@/stores/config.js";

export default {
    data() {
        return {
            userStore: useUsersStore()
        }
    },
    created() {
        const configStore = useConfigStore();
        configStore.apiPrefix = this.api_type


        this.userStore.fetchSelf().then(() => {

            if (this.userStore.self?.blocked_at != null)
                this.$router.push({name: 'BlockedPage'})

            if (!this.userStore.self?.registration_at && this.userStore.self?.role > 0)
                new bootstrap.Modal(document.getElementById('primaryUserModal')).show()
        })
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
       // this.tg.requestFullscreen()
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


