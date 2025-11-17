<script setup>

import {Head} from '@inertiajs/vue3'
import GlobalAlert from "@/Components/GlobalAlert.vue";
</script>
<template>

    <Head>
        <title>Автоматический учет доставки</title>
        <meta name="description" content="CashMan - система твоего бизнеса внутри"/>
    </Head>

    <header
        data-bs-theme="dark">
        <div class="navbar shadow shadow-sm">
            <div class="container flex-row-reverse p-2">


                <span
                    data-bs-toggle="modal" data-bs-target="#bot-info-modal"
                    class="text-primary fw-bold cursor-pointer">Система управления доставками</span>

                <button class="btn btn-link rounded-0 border-0 p-1" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </header>

    <GlobalAlert></GlobalAlert>
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
            <h6 class="offcanvas-title" id="offcanvasExampleLabel">{{  'Магазин' }}</h6>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>


        <div class="offcanvas-body">

            <ul class="list-unstyled">
                <li class="p-1"><a
                    data-bs-dismiss="offcanvas"
                    v-bind:class="{'fw-bold':$route.name==='MenuPage'}"
                    @click="goTo('MenuPage')"
                    href="javascript:void(0)"
                    class="text-decoration-none fw-normal"
                > Главное меню</a></li>

            </ul>


        </div>
    </div>



</template>
<script>

export default {
    data() {
        return {

            currentTheme: '',
            themes: []
        }
    },
    watch: {

    },
    computed: {
        tg() {
            return window.Telegram.WebApp;
        },
        self(){
          return window.botUser || null
        },

    },

    mounted() {
        this.tg.expand()

        this.tg.BackButton.hide()
    },
    methods: {
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

<style lang="scss">

</style>
