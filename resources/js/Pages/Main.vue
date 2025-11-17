<script setup>

import { usePage } from '@inertiajs/vue3'

// достаём пропсы, переданные из Laravel
const page = usePage()
const user = page.props.botUser

// сохраняем в window
window.botUser = user
window.botUser.base_role = user.role

import Layout from "@/Layouts/Layout.vue";

</script>
<template>
    <Layout>
        <template #default>
            <!--            <notifications position="top right"
                                       ignoreDuplicates="true"
                                       max="3"
                                       width="100%" speed="10" />-->

            <router-view/>

        </template>
    </Layout>
</template>

<script>


export default {
    computed: {
        tg() {
            return window.Telegram.WebApp;
        },
        user(){
          return window.botUser
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


