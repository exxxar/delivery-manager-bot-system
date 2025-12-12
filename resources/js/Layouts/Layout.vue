<script setup>

import {Head} from '@inertiajs/vue3'
import GlobalAlert from "@/Components/GlobalAlert.vue";
import GlobalConfirmModal from "@/Components/GlobalConfirmModal.vue";
import UserProfileCard from "@/Components/Users/UserProfileCard.vue";

import PrimaryForm from "@/Components/Users/Forms/PrimaryForm.vue";
</script>
<template>

    <Head>
        <title>Автоматический учет доставки</title>
        <meta name="description" content="CashMan - система твоего бизнеса внутри"/>
    </Head>

    <header
        class="fixed-top-menu"
        data-bs-theme="dark">
        <div class="navbar shadow shadow-sm">
            <div class="container flex-row-reverse p-2">


                <span
                    data-bs-toggle="modal" data-bs-target="#bot-info-modal"
                    class="text-primary fw-bold cursor-pointer">Система управления доставками</span>

                <button class="btn btn-link rounded-0 border-0 p-1" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <GlobalAlert></GlobalAlert>
    <GlobalConfirmModal></GlobalConfirmModal>
    <slot/>


    <footer class="text-body-secondary" style="padding:0px 0px 90px 0px;">

        <div class="container d-flex justify-content-center flex-column align-items-center">
            <p class="d-flex justify-content-center my-3">
                <a href="javascript:void(0)" @click="scrollTop"><i class="fa-solid fa-arrow-up mr-2"></i>Вернуться
                    наверх</a>
            </p>
        </div>
    </footer>


    <div class="offcanvas offcanvas-start custom-offcanvas"
         style="width: 70%;border-radius: 0px 10px 10px 0px;"
         tabindex="-1" id="sidebar-menu" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <h6 class="offcanvas-title" id="offcanvasExampleLabel">Меню</h6>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>


        <div class="offcanvas-body">
            <UserProfileCard
                v-if="userStore.self"
                :user="userStore.self"></UserProfileCard>
            <ul class="list-group list-group-flush my-3">
                <li class="p-2 list-group-item"><a
                    data-bs-dismiss="offcanvas"
                    v-bind:class="{'fw-bold':$route.name==='MenuPage'}"
                    @click="goTo('MenuPage')"
                    href="javascript:void(0)"
                    class="text-decoration-none fw-normal"
                > Главное меню</a></li>

            </ul>


        </div>
    </div>

    <!-- Модалка редактирования -->
    <div class="modal fade" id="primaryUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Первичная заполнение информации</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <PrimaryForm
                        v-if="userStore.self"
                        v-on:callback="result"
                        :initial-data="userStore.self"></PrimaryForm>
                </div>
            </div>
        </div>
    </div>

</template>
<script>
import {useUsersStore} from "@/stores/users";

export default {
    data() {
        return {
            userStore: useUsersStore(),
            currentTheme: '',
            themes: []
        }
    },
    watch: {},
    created() {
        this.userStore.fetchSelf().then(() => {
            if (!this.userStore.self.registration_at && this.userStore.self.role > 0)
                new bootstrap.Modal(document.getElementById('primaryUserModal')).show()
        })
    },
    computed: {
        tg() {
            return window.Telegram.WebApp;
        },
        self() {
            return window.botUser || null
        },

    },

    mounted() {
        this.tg.expand()

        this.tg.BackButton.hide()
    },
    methods: {
        result() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('primaryUserModal'))
            if (modal)
                modal.hide()
        },
        goTo(name) {
            this.$router.push({name: name})
        },

        scrollTop() {
            window.scrollTo(0, 80);
        },
        openLink(url) {
            this.tg.openLink(url, {
                try_instant_view: true
            })
        },
        closeShop() {
            this.tg.close()
        },

    },


}
</script>

<style>
.fixed-top-menu {
    position: sticky;
    top: 0;
    z-index: 100;
    background: #ffffff;
}
</style>
