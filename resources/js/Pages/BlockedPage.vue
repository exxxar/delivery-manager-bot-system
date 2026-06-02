<template>
    <p class="alert alert-light">
        {{user.blocked_message  || 'Вам ограничен доступ'}}
    </p>

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


    },
    methods: {

    }

}
</script>
