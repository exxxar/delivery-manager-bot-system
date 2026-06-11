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

    <div class="modal fade" id="installPwaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Установить приложение</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>Вы можете установить приложение к себе в один клик и запускать его прямо с рабочего стола.</p>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Позже</button>
                    <button class="btn btn-primary" @click="installPWA">Установить</button>
                </div>

            </div>
        </div>
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

        this.initPush()
    },
    methods: {
        installPWA() {
            window.installPWA()
        },
        async initPush() {

            /*  const oldRegistration = await navigator.serviceWorker.ready
              const oldSubscription =  await oldRegistration.pushManager.getSubscription()
              if (oldSubscription) {
                  oldSubscription.unsubscribe()
                  console.log('Старая подписка удалена')
              }
  */
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                console.warn('Push notifications not supported')
                return
            }

            const registration = await navigator.serviceWorker.register('/sw.js')

            const permission = await Notification.requestPermission()
            if (permission !== 'granted') {
                console.warn('User denied notifications')
                return
            }

            /*      const subscription = await registration.pushManager.subscribe({
                      userVisibleOnly: true,
                      applicationServerKey: this.vapidPublicKey,

                  })
      */
            /*  await axios.post('/api/push/subscribe', {
                  subscription,
                  board_uuid: this.board.uuid
              })*/
        },
        open(url) {
            this.tg.openLink(url)
        },
        goTo(name) {
            this.$router.push({name: name})
        },
    }

}
</script>


