<script setup>
import Layout from "@/Layouts/Layout.vue";

defineProps({
    api_type: String,
});
</script>
<template>
    <Layout v-if="!userStore.self?.blocked_at">
        <template #default>
            <router-view />
        </template>
    </Layout>

    <div class="access-denied" v-else>
        <div class="access-denied-card">
            <div class="access-denied-icon">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h2>Доступ ограничен</h2>
            <p>Обратитесь к администратору для уточнения информации.</p>
        </div>
    </div>

    <!-- Установка PWA -->
    <div
        class="modal fade install-pwa-modal"
        id="installPwaModal"
        tabindex="-1"
        aria-labelledby="installPwaModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="install-pwa-hero">
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Закрыть"
                    ></button>

                    <div class="install-pwa-logo">
                        <i class="fa-solid fa-percent"></i>
                    </div>

                    <div class="install-pwa-badge">
                        <i class="fa-solid fa-mobile-screen-button me-1"></i>
                        Приложение для вашего устройства
                    </div>

                    <h2 id="installPwaModalLabel">
                        Мойпроцент<br />
                        всегда под рукой
                    </h2>

                    <p>
                        Установите приложение и запускайте его с рабочего
                        стола — без необходимости каждый раз открывать браузер.
                    </p>
                </div>

                <div class="modal-body install-pwa-body">
                    <div class="install-pwa-benefit">
                        <div class="benefit-icon">
                            <i class="fa-solid fa-rocket"></i>
                        </div>
                        <div>
                            <strong>Быстрый запуск</strong>
                            <span>Открывайте приложение одним нажатием</span>
                        </div>
                    </div>

                    <div class="install-pwa-benefit">
                        <div class="benefit-icon">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <strong>Отдельное окно</strong>
                            <span>Удобный интерфейс без лишних элементов браузера</span>
                        </div>
                    </div>

                    <div class="install-pwa-benefit">
                        <div class="benefit-icon">
                            <i class="fa-solid fa-cloud-arrow-down"></i>
                        </div>
                        <div>
                            <strong>Удобство в работе</strong>
                            <span>Быстрый доступ к системе и её функциям</span>
                        </div>
                    </div>
                </div>

                <div class="install-pwa-footer">
                    <button
                        type="button"
                        class="install-pwa-later"
                        data-bs-dismiss="modal"
                    >
                        Позже
                    </button>

                    <button
                        type="button"
                        class="install-pwa-submit"
                        @click="installPWA"
                    >
                        <i class="fa-solid fa-download me-2"></i>
                        Установить приложение
                    </button>
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

<style scoped>
.access-denied {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 65vh;
    padding: 24px;
    background: #f5f7fc;
}

.access-denied-card {
    width: 100%;
    max-width: 420px;
    padding: 36px 28px;
    text-align: center;
    background: #fff;
    border: 1px solid #e5eaf2;
    border-radius: 24px;
    box-shadow: 0 16px 45px rgba(23, 35, 59, 0.07);
}

.access-denied-icon {
    display: grid;
    place-items: center;
    width: 64px;
    height: 64px;
    margin: 0 auto 20px;
    color: #dc3545;
    background: #fff0f1;
    border-radius: 20px;
    font-size: 25px;
}

.access-denied-card h2 {
    color: #17233b;
    font-size: 22px;
    font-weight: 800;
}

.access-denied-card p {
    margin: 10px 0 0;
    color: #778399;
    line-height: 1.6;
}

/* Модальное окно установки */

.install-pwa-modal .modal-content {
    overflow: hidden;
    border: 1px solid #e5eaf2;
    border-radius: 24px;
    box-shadow: 0 24px 70px rgba(23, 35, 59, 0.2);
}

.install-pwa-hero {
    position: relative;
    padding: 32px 28px 28px;
    color: #fff;
    background:
        radial-gradient(circle at 90% 5%, rgba(255,255,255,.15), transparent 32%),
        linear-gradient(145deg, #2457d6 0%, #173b91 100%);
}

.install-pwa-hero .btn-close {
    position: absolute;
    top: 18px;
    right: 18px;
    filter: invert(1);
    opacity: .8;
}

.install-pwa-logo {
    display: grid;
    place-items: center;
    width: 68px;
    height: 68px;
    margin-bottom: 22px;
    color: #2457d6;
    background: #fff;
    border-radius: 21px;
    box-shadow: 0 10px 28px rgba(0, 0, 0, .15);
    font-size: 32px;
}

.install-pwa-badge {
    display: inline-flex;
    align-items: center;
    padding: 7px 10px;
    margin-bottom: 14px;
    color: #fff;
    background: rgba(255, 255, 255, .13);
    border: 1px solid rgba(255, 255, 255, .2);
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}

.install-pwa-hero h2 {
    margin: 0 0 12px;
    font-size: clamp(26px, 5vw, 32px);
    font-weight: 850;
    line-height: 1.15;
    letter-spacing: -.7px;
}

.install-pwa-hero p {
    max-width: 390px;
    margin: 0;
    color: rgba(255, 255, 255, .82);
    font-size: 14px;
    line-height: 1.65;
}

.install-pwa-body {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 26px 28px;
    background: #fff;
}

.install-pwa-benefit {
    display: flex;
    align-items: center;
    gap: 14px;
}

.benefit-icon {
    display: grid;
    flex: 0 0 46px;
    place-items: center;
    width: 46px;
    height: 46px;
    color: #2457d6;
    background: #edf3ff;
    border-radius: 14px;
    font-size: 18px;
}

.install-pwa-benefit > div:last-child {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.install-pwa-benefit strong {
    color: #17233b;
    font-size: 14px;
    font-weight: 750;
}

.install-pwa-benefit span {
    color: #778399;
    font-size: 12px;
    line-height: 1.5;
}

.install-pwa-footer {
    display: flex;
    gap: 12px;
    padding: 18px 28px 26px;
    background: #fff;
}

.install-pwa-later {
    padding: 12px 16px;
    color: #53627a;
    background: #f4f6fa;
    border: 1px solid #e5eaf2;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
}

.install-pwa-submit {
    flex: 1;
    padding: 12px 16px;
    color: #fff;
    background: #2457d6;
    border: 1px solid #2457d6;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 750;
    transition: background .2s ease, transform .2s ease;
}

.install-pwa-submit:hover {
    background: #173b91;
    transform: translateY(-1px);
}

@media (max-width: 575.98px) {
    .install-pwa-hero {
        padding: 28px 22px 24px;
    }

    .install-pwa-body {
        padding: 24px 22px;
    }

    .install-pwa-footer {
        flex-direction: column-reverse;
        padding: 12px 22px 22px;
    }

    .install-pwa-later,
    .install-pwa-submit {
        width: 100%;
        min-height: 46px;
    }
}
</style>
