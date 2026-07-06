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

    <div class="container-lg py-3">
        <slot/>
    </div>

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

            <template v-if="self">
                <div class="alert alert-light border-primary border my-3">
                    <p
                        @click="copy(self.email)"
                        class="small mb-0">Ваш логин <span class="fw-bold text-primary"> <i class="fa-solid fa-copy me-1"></i>{{ self.email }}</span></p>
                    <p
                        @click="copy(self.telegram_chat_id)"
                        class="small mb-0">Ваш пароль <span class="fw-bold text-primary"> <i class="fa-solid fa-copy me-1"></i>{{ self.telegram_chat_id }}</span></p>
                </div>

            </template>
            <ul class="list-group list-group-flush my-3">
                <li class="p-2 list-group-item">
                    <a
                        data-bs-dismiss="offcanvas"
                        v-bind:class="{'fw-bold': $route.name === 'MenuPage'}"
                        @click="goTo('MenuPage')"
                        href="javascript:void(0)"
                        class="text-decoration-none fw-normal"
                    >
                        <i class="fa-solid fa-house me-2"></i>
                        Главное меню
                    </a>
                </li>

                <li class="p-2 list-group-item">
                    <a
                        data-bs-dismiss="offcanvas"
                        @click="confirmLogout"
                        href="javascript:void(0)"
                        class="text-decoration-none text-danger fw-normal"
                    >
                        <i class="fa-solid fa-right-from-bracket me-2"></i>
                        Выйти из системы
                    </a>
                </li>
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
import {useModalStore} from "@/stores/utillites/useConfitmModalStore";
export default {
    data() {
        return {
            userStore: useUsersStore(),
            modalStore: useModalStore(),
            currentTheme: '',
            themes: []
        }
    },
    watch: {},
    created() {
        /*  this.userStore.fetchSelf().then(() => {

              if (this.userStore.self.blocked_at != null)
                  this.$router.push({name: 'BlockedPage'})

              if (!this.userStore.self.registration_at && this.userStore.self.role > 0)
                  new bootstrap.Modal(document.getElementById('primaryUserModal')).show()
          })*/
    },
    computed: {
        canUseTG() {
            return window.apiPrefix === "bot-api"
        },
        tg() {
            return window.Telegram.WebApp || null;
        },
        self() {
            return this.userStore.self
        },

    },

    mounted() {

        if (this.canUseTG) {
            this.tg.expand()

            this.tg.BackButton.hide()
        }

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
            if (this.canUseTG)
                this.tg.openLink(url, {
                    try_instant_view: true
                })
            else
                window.location.href = url
        },

        async copy(item) {

            try {

                await navigator.clipboard.writeText(
                    item
                );

                alert("Данные скопированы");

            } catch (e) {

                console.error(e);

            }

        },

        confirmLogout() {
            // Используем ваш модальный стор для подтверждения
            this.modalStore.open(
                'Вы уверены, что хотите выйти из системы?',
                async () => {
                    await this.performLogout();
                    this.modalStore.close();
                },
                () => this.modalStore.close()
            );
        },

        async performLogout() {
            try {
                const userStore = useUsersStore();
                await userStore.logout();

                // Очищаем кеш Service Worker
                if (navigator.serviceWorker?.controller) {
                    navigator.serviceWorker.controller.postMessage({ type: 'CLEAR_CACHE' });
                }

                // Логируем выход
                // (уже логируется в AuthController::logout на бэкенде)

                // Редирект на страницу авторизации
                window.location.href = '/login';
            } catch (e) {
                console.error('Ошибка при выходе:', e);
                alert('Произошла ошибка при выходе');
            }
        }
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
