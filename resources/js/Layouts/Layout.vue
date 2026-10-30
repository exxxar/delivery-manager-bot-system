<template>
    <Head>
        <title>Мойпроцент — управление доставками</title>
        <meta name="description" content="Мойпроцент — система управления доставками, заявками и поставками"/>
    </Head>

    <!-- Индикатор отсутствия сети -->
    <Transition name="offline-banner">
        <div v-if="isOffline" class="offline-banner">
            <i class="fa-solid fa-wifi"></i>
            <span>Нет подключения к интернету</span>
        </div>
    </Transition>

    <header class="fixed-top-menu">
        <div class="app-navbar">
            <div class="app-navbar-inner">
                <a href="/pwa#/" class="brand-lockup" style="text-decoration:none;">
                    <div class="brand-mark"><i class="fa-solid fa-percent"></i></div>
                    <div class="brand-copy">
                        <span class="brand-name">Мойпроцент</span>
                        <span class="brand-caption">Управление доставками</span>
                    </div>
                </a>

                <div class="navbar-actions">
                    <!-- Версия приложения -->
                    <span
                        v-if="displayVersion"
                        class="version-badge"
                        :title="`Версия приложения: ${serverVersion}`"
                    >v{{ serverVersion }}</span>

                    <!-- Счётчик офлайн-заявок -->
                    <button
                        v-if="offlineQueueCount > 0"
                        type="button"
                        class="queue-badge"
                        data-bs-toggle="modal"
                        data-bs-target="#offlineQueueModal"
                        @click="refreshQueueList"
                        title="Просмотреть очередь офлайн-заявок"
                    >
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>{{ offlineQueueCount }}</span>
                    </button>

                    <button
                        class="menu-toggle"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#sidebar-menu"
                        aria-controls="sidebar-menu"
                        aria-label="Открыть меню"
                    >
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <GlobalAlert/>
    <GlobalConfirmModal/>

    <main class="app-main container-lg">
        <slot/>
    </main>


    <footer class="app-footer">
        <div class="container-lg app-footer-inner">
            <span class="footer-brand"><i class="fa-solid fa-percent me-2"></i>Мойпроцент</span>
            <button
                v-if="canInstallPwa"
                type="button"
                class="install-pwa-btn"
                @click="installPwa"
            >
                <i class="fa-solid fa-mobile-screen-button"></i>
                <span>Установить приложение</span>
            </button>
            <button type="button" class="back-to-top" @click="scrollTop">
                <i class="fa-solid fa-arrow-up"></i>
                <span>Вернуться наверх</span>
            </button>
        </div>
    </footer>

    <!-- Боковое меню -->
    <div
        class="offcanvas offcanvas-start custom-offcanvas"
        tabindex="-1"
        id="sidebar-menu"
        aria-labelledby="offcanvasExampleLabel"
    >
        <div class="offcanvas-header sidebar-heading">
            <div class="sidebar-brand">
                <div class="brand-mark"><i class="fa-solid fa-percent"></i></div>
                <div>
                    <h6 class="offcanvas-title" id="offcanvasExampleLabel">Мойпроцент</h6>
                    <span>Навигация по системе</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Закрыть меню"></button>
        </div>

        <div class="offcanvas-body">
            <UserProfileCard
                v-if="userStore.self"
                :user="userStore.self"
            />

            <template v-if="self">
                <div class="alert alert-light border-primary border my-3">
                    <p @click="copy(self.email)" class="small mb-0">
                        Ваш логин
                        <span class="fw-bold text-primary">
                            <i class="fa-solid fa-copy me-1"></i>{{ self.email }}
                        </span>
                    </p>
                </div>
            </template>


            <ul class="list-group list-group-flush my-3">
                <li class="p-2 list-group-item">
                    <a
                        data-bs-dismiss="offcanvas"
                        :class="{'fw-bold': $route.name === 'MenuPage'}"
                        @click="goTo('MenuPage')"
                        href="javascript:void(0)"
                        class="text-decoration-none fw-normal"
                    >
                        <i class="fa-solid fa-house me-2"></i>
                        Главное меню
                    </a>
                </li>

                <template v-if="self && self.role >= 4">
                    <li class="p-2 list-group-item">
                        <a
                            data-bs-dismiss="offcanvas"
                            data-bs-toggle="modal"
                            data-bs-target="#inviteManagerModal"
                            href="javascript:void(0)"
                            class="text-decoration-none fw-normal"
                        >
                            <i class="fa-solid fa-user-plus me-2 text-warning"></i>
                            Ссылка для администратора
                        </a>
                    </li>

                    <li class="p-2 list-group-item">
                        <a
                            data-bs-dismiss="offcanvas"
                            data-bs-toggle="modal"
                            data-bs-target="#supplierInviteModal"
                            href="javascript:void(0)"
                            class="text-decoration-none fw-normal"
                        >
                            <i class="fa-solid fa-truck me-2 text-success"></i>
                            Ссылка для поставщика
                        </a>
                    </li>
                </template>

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

            <div class="sidebar-version" v-if="displayVersion">
                <i class="fa-solid fa-code-branch"></i>
                <span class="sidebar-version-label">Версия системы</span>
                <span
                    class="version-badge"
                    :title="`Версия приложения: ${displayVersion}`"
                >v{{ displayVersion }}</span>
            </div>
        </div>
    </div>

    <!-- Модалка первичного заполнения -->
    <div class="modal fade" id="primaryUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Первичное заполнение информации</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <PrimaryForm
                        v-if="userStore.self"
                        @callback="onPrimaryFormSuccess"
                        :initial-data="userStore.self"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- 🔹 МОДАЛКА ОФЛАЙН-ОЧЕРЕДИ -->
    <div class="modal fade" id="offlineQueueModal" tabindex="-1" aria-labelledby="offlineQueueModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center" id="offlineQueueModalLabel">
                        <i class="fa-solid fa-cloud-arrow-up text-warning me-2"></i>
                        Очередь офлайн-заявок
                        <span class="badge bg-secondary ms-2">{{ offlineQueueCount }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div v-if="isOffline" class="alert alert-danger d-flex align-items-start mb-3">
                        <i class="fa-solid fa-triangle-exclamation fs-4 me-2 mt-1"></i>
                        <div>
                            <strong>Нет подключения к интернету</strong>
                            <p class="mb-0 small">
                                Все заявки сохранены локально и будут отправлены автоматически,
                                как только соединение восстановится.
                            </p>
                        </div>
                    </div>

                    <div v-else class="alert alert-success d-flex align-items-center mb-3">
                        <i class="fa-solid fa-circle-check fs-4 me-2"></i>
                        <div class="flex-grow-1">
                            <strong>Подключение восстановлено</strong>
                            <p class="mb-0 small">Можно отправить все заявки прямо сейчас.</p>
                        </div>
                    </div>

                    <div v-if="queueItems.length > 0">
                        <div
                            v-for="(item, index) in queueItems"
                            :key="item.id"
                            class="queue-item card mb-2"
                        >
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1 me-2">
                                        <div class="fw-bold mb-1">
                                            <i class="fa-solid fa-file-lines text-primary me-1"></i>
                                            {{ item.formData.title || 'Без названия' }}
                                            <span class="badge ms-2"
                                                  :class="item.isEdit ? 'bg-info' : 'bg-success'">
                                                {{ item.isEdit ? 'Редактирование' : 'Создание' }}
                                            </span>
                                        </div>

                                        <p v-if="item.formData.description" class="small text-muted mb-2 text-truncate">
                                            {{ item.formData.description }}
                                        </p>

                                        <div class="small d-flex flex-wrap gap-3 text-muted">
                                            <span v-if="item.formData.total_price">
                                                <i class="fa-solid fa-ruble-sign me-1"></i>
                                                {{ formatMoney(item.formData.total_price) }}
                                            </span>
                                            <span>
                                                <i class="fa-regular fa-clock me-1"></i>
                                                {{ formatDate(item.createdAt) }}
                                            </span>
                                            <span v-if="item.fileName">
                                                <i class="fa-solid fa-paperclip me-1"></i>
                                                Чек прикреплён
                                            </span>
                                            <span v-if="item.attempts > 0" class="text-warning">
                                                <i class="fa-solid fa-rotate me-1"></i>
                                                Попыток: {{ item.attempts }}
                                            </span>
                                        </div>

                                        <div v-if="item.error" class="small text-danger mt-1">
                                            <i class="fa-solid fa-circle-xmark me-1"></i>
                                            {{ item.error }}
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        @click="confirmRemoveFromQueue(item)"
                                        :disabled="syncingIds.includes(item.id)"
                                        title="Удалить из очереди"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>

                                <div v-if="syncingIds.includes(item.id)" class="mt-2">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                             style="width: 100%"></div>
                                    </div>
                                    <div class="small text-primary mt-1">
                                        <i class="fa-solid fa-spinner fa-spin me-1"></i>
                                        Отправка...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-5 text-muted">
                        <i class="fa-solid fa-inbox fs-1 mb-3 d-block"></i>
                        <p class="mb-0">Очередь пуста</p>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Закрыть
                    </button>

                    <div class="d-flex gap-2">
                        <button
                            v-if="queueItems.length > 0"
                            type="button"
                            class="btn btn-outline-danger"
                            @click="confirmClearQueue"
                            :disabled="syncingIds.length > 0"
                        >
                            <i class="fa-solid fa-broom me-1"></i>
                            Очистить всё
                        </button>

                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="syncAllQueue"
                            :disabled="isOffline || queueItems.length === 0 || syncingIds.length > 0"
                        >
                            <template v-if="syncingIds.length > 0">
                                <i class="fa-solid fa-spinner fa-spin me-1"></i>
                                Отправка...
                            </template>
                            <template v-else>
                                <i class="fa-solid fa-paper-plane me-1"></i>
                                Отправить все
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔹 МОДАЛКА ОБНОВЛЕНИЯ -->
    <UpdateModal
        v-if="updateAvailable"
        :new-version="updateInfo.version"
        :local-version="updateInfo.localVersion"
        :message="updateInfo.message"
        :force-update="updateInfo.forceUpdate"
    />

    <div class="modal fade" id="inviteManagerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-user-shield text-primary me-2"></i>
                        Управление ссылкой
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <AdminInviteLinkManager v-if="self && self.role >= 4"/>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка управления ссылкой поставщика -->
    <div class="modal fade" id="supplierInviteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-truck text-success me-2"></i>
                        Управление ссылкой поставщика
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <SupplierInviteLinkManager v-if="self && self.role >= 4"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {Head, usePage} from '@inertiajs/vue3'
import GlobalAlert from "@/Components/GlobalAlert.vue";
import GlobalConfirmModal from "@/Components/GlobalConfirmModal.vue";
import UserProfileCard from "@/Components/Users/UserProfileCard.vue";
import PrimaryForm from "@/Components/Users/Forms/PrimaryForm.vue";
import UpdateModal from "@/Components/UpdateModal.vue";
import AdminInviteLinkManager from "@/Components/Admins/AdminInviteLinkManager.vue";
import SupplierInviteLinkManager from "@/Components/Suppliers/SupplierInviteLinkManager.vue";
import {
    getQueue,
    removeFromQueue,
    incrementAttempts,
    base64ToFile,
} from "@/utilites/offlineQueue.js";
import {
    getLocalVersion,
    setLocalVersion,
    forceUpdate
} from "@/utilites/versionCheck.js";

import {
    startMonitoring,
    stopMonitoring,
    onStatusChange,
} from "@/utilites/networkStatus.js";

import {useUsersStore} from "@/stores/users";
import {useSalesStore} from "@/stores/sales";
import {useModalStore} from "@/stores/utillites/useConfitmModalStore";
import {useAlertStore} from "@/stores/utillites/useAlertStore";

export default {
    components: {
        Head,
        GlobalAlert,
        GlobalConfirmModal,
        UserProfileCard,
        PrimaryForm,
        UpdateModal,
        SupplierInviteLinkManager,
        AdminInviteLinkManager
    },

    data() {
        return {
            unsubscribeNetwork: null,
            // 🔹 Состояние сети и очереди
            offlineQueueCount: 0,
            queueItems: [],
            syncingIds: [],
            isOffline: !navigator.onLine,

            // 🔹 Состояние обновления
            updateAvailable: false,

            updateInfo: {
                version: '',
                localVersion: '',
                message: '',
                forceUpdate: false,
            },

            canInstallPwa: false,
            deferredInstallPrompt: null,
            // 🔹 Stores
            userStore: useUsersStore(),
            salesStore: useSalesStore(),
            modalStore: useModalStore(),
            alertStore: useAlertStore(),

            // 🔹 Inertia page для доступа к shared props
            page: usePage(),
        };
    },

    computed: {
        canUseTG() {
            return window.apiPrefix === "bot-api";
        },
        tg() {
            return window.Telegram?.WebApp || null;
        },
        self() {
            return this.userStore.self;
        },

        // 🔹 Версия из Inertia shared props
        serverVersion() {
            // Основной источник — Inertia shared props Laravel.
            return this.page?.props?.appVersion
                ?? this.page?.props?.app_version
                ?? null;
        },

        // Если серверная версия ещё не передана, показываем версию клиентской сборки.
        displayVersion() {
            return this.serverVersion || getLocalVersion() || null;
        },

        // 🔹 Флаг принудительного обновления
        forceUpdateFlag() {
            return this.page.props.forceUpdate || false;
        },
    },

    watch: {


        // 🔹 Следим за изменениями версии (работает при SPA-навигации Inertia)
        serverVersion: {
            immediate: true,
            handler(newVersion) {
                if (!newVersion) return;
                this.checkAndShowUpdateModal(newVersion);
            },
        },
    },

    mounted() {


        startMonitoring();

        window.addEventListener('beforeinstallprompt', this.handleInstallPrompt);
        window.addEventListener('appinstalled', this.handleAppInstalled);
        // 🔹 Слушатели сети
        window.addEventListener('online', this.handleOnline);
        window.addEventListener('offline', this.handleOffline);
        window.addEventListener('offline-queue-changed', this.onQueueChanged);

        this.unsubscribeNetwork = onStatusChange((isOnline) => {
            this.isOffline = !isOnline;
            if (isOnline) {
                window.dispatchEvent(new CustomEvent('trigger-queue-sync'));
            }
        });


        // 🔹 Инициализация очереди
        this.offlineQueueCount = getQueue().length;
        this.queueItems = getQueue();

        // 🔹 Telegram WebApp
        if (this.canUseTG) {
            this.tg?.expand();
            this.tg?.BackButton?.hide();
        }
    },

    beforeUnmount() {

        window.removeEventListener('beforeinstallprompt', this.handleInstallPrompt);
        window.removeEventListener('appinstalled', this.handleAppInstalled);
        // 🔹 Очистка слушателей
        window.removeEventListener('online', this.handleOnline);
        window.removeEventListener('offline', this.handleOffline);
        window.removeEventListener('offline-queue-changed', this.onQueueChanged);
        this.unsubscribeNetwork?.();
        stopMonitoring();
        document.body.classList.remove('has-offline-banner');
    },

    methods: {
        handleInstallPrompt(event) {
            // Отключаем стандартную подсказку браузера
            event.preventDefault();

            this.deferredInstallPrompt = event;
            this.canInstallPwa = true;
        },

        async installPwa() {
            if (!this.deferredInstallPrompt) {
                this.alertStore.show(
                    'Если установка недоступна, откройте меню браузера и выберите «Установить приложение» или «Добавить на главный экран».',
                    'warning'
                );
                return;
            }

            this.deferredInstallPrompt.prompt();

            const {outcome} = await this.deferredInstallPrompt.userChoice;

            if (outcome === 'accepted') {
                this.canInstallPwa = false;
            }

            this.deferredInstallPrompt = null;
        },

        handleAppInstalled() {
            this.canInstallPwa = false;
            this.deferredInstallPrompt = null;
        },
        // 🔹 Проверка версии и показ модалки обновления
        checkAndShowUpdateModal(newVersion) {
            const localVersion = getLocalVersion();

            // Первый запуск — сохраняем версию без модалки
            if (!localVersion) {
                setLocalVersion(newVersion);
                return;
            }

            // Версии совпадают и нет принудительного обновления
            if (localVersion === newVersion && !this.forceUpdateFlag) {
                return;
            }

            // Версии отличаются или принудительное обновление
            this.updateAvailable = true;
            this.updateInfo = {
                version: newVersion,
                localVersion: localVersion,
                message: 'Доступна новая версия приложения. Пожалуйста, обновите для продолжения работы.',
                forceUpdate: this.forceUpdateFlag,
            };

            // Показываем модалку
            this.$nextTick(() => {
                const modalEl = document.getElementById('updateModal');
                if (modalEl && !modalEl.classList.contains('show')) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            });
        },

        // 🔹 Обработчики сети
        handleOnline() {
            this.isOffline = false;
            window.dispatchEvent(new CustomEvent('trigger-queue-sync'));
        },

        handleOffline() {
            this.isOffline = true;
        },

        onQueueChanged(event) {
            this.offlineQueueCount = event.detail.count;
            if (document.getElementById('offlineQueueModal')?.classList.contains('show')) {
                this.queueItems = getQueue();
            }
        },

        refreshQueueList() {
            this.queueItems = getQueue();
        },

        // 🔹 Форматирование
        formatMoney(value) {
            return new Intl.NumberFormat('ru-RU').format(value || 0) + ' ₽';
        },

        formatDate(isoString) {
            try {
                const date = new Date(isoString);
                return date.toLocaleString('ru-RU', {
                    day: '2-digit',
                    month: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            } catch {
                return isoString;
            }
        },

        // 🔹 Управление очередью
        confirmRemoveFromQueue(item) {
            this.modalStore.open(
                `Удалить заявку "${item.formData.title || 'без названия'}" из очереди?`,
                () => {
                    removeFromQueue(item.id);
                    this.refreshQueueList();
                    this.modalStore.close();
                    this.alertStore.show('Заявка удалена из очереди');
                },
                () => this.modalStore.close()
            );
        },

        confirmClearQueue() {
            this.modalStore.open(
                `Удалить ВСЕ заявки (${this.queueItems.length}) из очереди? Это действие нельзя отменить.`,
                () => {
                    this.queueItems.forEach(item => removeFromQueue(item.id));
                    this.refreshQueueList();
                    this.modalStore.close();
                    this.alertStore.show('Очередь очищена');
                },
                () => this.modalStore.close()
            );
        },

        async syncAllQueue() {
            if (this.isOffline || this.syncingIds.length > 0) return;

            const items = [...this.queueItems];
            let successCount = 0;
            let errorCount = 0;

            for (const item of items) {
                if (this.isOffline) {
                    this.alertStore.show('Связь пропала во время отправки', 'warning');
                    break;
                }

                this.syncingIds.push(item.id);
                await new Promise(r => setTimeout(r, 50));

                try {
                    const file = item.file
                        ? base64ToFile(item.file, item.fileName, item.fileType)
                        : null;

                    if (item.isEdit) {
                        await this.salesStore.update(item.formData.id, item.formData, file);
                    } else {
                        await this.salesStore.create(item.formData, file);
                    }

                    removeFromQueue(item.id);
                    successCount++;
                } catch (e) {
                    console.error(`Ошибка отправки ${item.id}:`, e);
                    errorCount++;
                    incrementAttempts(item.id);

                    const idx = this.queueItems.findIndex(q => q.id === item.id);
                    if (idx !== -1) {
                        this.queueItems[idx].error =
                            e?.response?.data?.message ||
                            e?.message ||
                            'Не удалось отправить';
                    }

                    if (e?.response?.status === 401) {
                        removeFromQueue(item.id);
                    }
                } finally {
                    this.syncingIds = this.syncingIds.filter(id => id !== item.id);
                }
            }

            this.refreshQueueList();

            if (successCount > 0) {
                this.alertStore.show(`Успешно отправлено: ${successCount}`);
            }
            if (errorCount > 0) {
                this.alertStore.show(`Ошибок при отправке: ${errorCount}`, 'error');
            }
            if (successCount > 0 && this.queueItems.length === 0) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('offlineQueueModal'));
                modal?.hide();
            }
        },

        // 🔹 Навигация и утилиты
        onPrimaryFormSuccess() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('primaryUserModal'));
            modal?.hide();
        },

        goTo(name) {
            this.$router.push({name});
        },

        scrollTop() {
            window.scrollTo({top: 80, behavior: 'smooth'});
        },

        openLink(url) {
            if (this.canUseTG) {
                this.tg.openLink(url, {try_instant_view: true});
            } else {
                window.location.href = url;
            }
        },

        async copy(text) {
            try {
                await navigator.clipboard.writeText(text);
                alert("Данные скопированы");
            } catch (e) {
                console.error('Ошибка копирования:', e);
            }
        },

        confirmLogout() {
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
                await this.userStore.logout();

                if (navigator.serviceWorker?.controller) {
                    navigator.serviceWorker.controller.postMessage({type: 'CLEAR_CACHE'});
                }

                window.location.href = '/login';
            } catch (e) {
                console.error('Ошибка при выходе:', e);
                alert('Произошла ошибка при выходе');
            }
        },
    },
};
</script>

<style>
body.has-offline-banner .fixed-top-menu {
    top: 36px;
}
</style>

<style scoped>
.offline-banner {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background: #dc3545;
    color: #ffffff;
    font-weight: 700;
    text-align: center;
    padding: 8px 16px;
    height: 36px;
    z-index: 1055;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 14px;
    letter-spacing: 0.3px;
}

.offline-banner-enter-active,
.offline-banner-leave-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.offline-banner-enter-from,
.offline-banner-leave-to {
    transform: translateY(-100%);
    opacity: 0;
}

.fixed-top-menu {
    position: sticky;
    top: 0;
    z-index: 100;
    background: #ffffff;
    transition: top 0.3s ease;
}

.queue-badge {
    cursor: pointer;
    transition: all 0.2s ease;
    animation: pulse-badge 2s infinite;
    padding: 6px 10px;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(255, 193, 7, 0.4);
}

.queue-badge:hover {
    transform: scale(1.08);
    background: #ffca2c !important;
}

.version-badge {
    font-size: 11px;
    padding: 4px 8px;
    opacity: 0.8;
    cursor: help;
}

@keyframes pulse-badge {
    0%, 100% {
        box-shadow: 0 2px 6px rgba(255, 193, 7, 0.4);
    }
    50% {
        box-shadow: 0 2px 12px rgba(255, 193, 7, 0.7);
    }
}

.queue-item {
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.queue-item:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
}


/* Единый визуальный стиль приложения «Мойпроцент» */
.fixed-top-menu {
    z-index: 1030;
    background: rgba(255, 255, 255, 0.96);
    border-bottom: 1px solid #e5eaf2;
    box-shadow: 0 4px 18px rgba(23, 35, 59, 0.055);
    backdrop-filter: blur(14px);
}

.app-navbar {
    width: 100%;
}

.app-navbar-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    width: min(100% - 32px, 1320px);
    min-height: 76px;
    margin: 0 auto;
}

.brand-lockup {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    cursor: pointer;
}

.brand-mark {
    display: flex;
    flex: 0 0 auto;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    color: #fff;
    background: linear-gradient(135deg, #2457d6, #173b91);
    border-radius: 14px;
    box-shadow: 0 6px 15px rgba(36, 87, 214, .2);
    font-size: 20px;
}

.brand-copy, .sidebar-brand > div:last-child {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.brand-name {
    color: #17233b;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -.35px;
}

.brand-caption {
    color: #778399;
    font-size: 12px;
    line-height: 1.3;
}

.navbar-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.menu-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    color: #2457d6;
    background: #edf3ff;
    border: 1px solid #dce7ff;
    border-radius: 13px;
    font-size: 19px;
    transition: color .2s ease, background .2s ease, transform .2s ease;
}

.menu-toggle:hover {
    color: #fff;
    background: #2457d6;
    transform: translateY(-1px);
}

.version-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 9px;
    color: #63718a;
    background: #f4f6fa;
    border: 1px solid #e5eaf2;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
}

.queue-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-width: 42px;
    min-height: 38px;
    padding: 7px 11px;
    color: #8b5b00;
    background: #fff5d8;
    border: 1px solid #ffe5a0;
    border-radius: 11px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    transition: background .2s ease, transform .2s ease;
}

.queue-badge:hover {
    background: #ffebad;
    transform: translateY(-1px);
}

.app-main {
    min-height: calc(100vh - 150px);
    padding-top: 28px;
    padding-bottom: 34px;
}

.app-footer {
    padding: 18px 0 94px;
    color: #778399;
    background: #fff;
    border-top: 1px solid #e9edf4;
}

.app-footer-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.footer-brand {
    color: #53627a;
    font-size: 13px;
    font-weight: 750;
}

.footer-brand i {
    color: #2457d6;
}

.back-to-top {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 12px;
    color: #2457d6;
    background: #edf3ff;
    border: 1px solid #dce7ff;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    transition: background .2s ease, color .2s ease;
}

.back-to-top:hover {
    color: #fff;
    background: #2457d6;
}

.custom-offcanvas {
    width: min(390px, 88vw) !important;
    color: #17233b;
    background: #fff;
    border: 0;
    border-radius: 0 20px 20px 0;
    box-shadow: 16px 0 50px rgba(23, 35, 59, .14);
}

.sidebar-heading {
    padding: 20px;
    border-bottom: 1px solid #e9edf4;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.sidebar-brand .brand-mark {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    font-size: 17px;
}

.sidebar-brand .offcanvas-title {
    color: #17233b;
    font-size: 17px;
    font-weight: 800;
}

.sidebar-brand span {
    color: #778399;
    font-size: 11px;
}

.custom-offcanvas .offcanvas-body {
    padding: 18px;
}

.custom-offcanvas .list-group {
    gap: 6px;
}

.custom-offcanvas .list-group-item {
    padding: 0 !important;
    overflow: hidden;
    background: transparent;
    border: 0;
    border-radius: 11px;
}

.custom-offcanvas .list-group-item a {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 13px 14px;
    color: #43516a;
    font-size: 14px;
    font-weight: 600 !important;
    transition: color .2s ease, background .2s ease;
}

.custom-offcanvas .list-group-item a:hover,
.custom-offcanvas .list-group-item a.fw-bold {
    color: #2457d6;
    background: #edf3ff;
}

.custom-offcanvas .list-group-item a i {
    width: 20px;
    text-align: center;
    font-size: 16px;
}

.sidebar-version {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 18px;
    padding: 11px 12px;
    color: #778399;
    background: #f7f9fc;
    border: 1px solid #e5eaf2;
    border-radius: 12px;
    font-size: 12px;
}

.sidebar-version > i {
    color: #2457d6;
}

.sidebar-version-label {
    flex: 1;
    font-weight: 600;
}

.sidebar-version .version-badge {
    color: #2457d6;
    background: #edf3ff;
    border-color: #dce7ff;
}

.custom-offcanvas .alert {
    border-radius: 13px;
}

/* Карточки и диалоги в общей палитре */
.queue-item {
    overflow: hidden;
    border: 1px solid #e5eaf2;
    border-radius: 13px;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.queue-item:hover {
    border-color: #b9ccfa;
    box-shadow: 0 5px 16px rgba(36, 87, 214, .08);
}

.modal-content {
    overflow: hidden;
    border: 1px solid #e5eaf2;
    border-radius: 18px;
    box-shadow: 0 20px 60px rgba(23, 35, 59, .16);
}

.modal-header {
    padding: 18px 22px;
    background: #f8faff;
    border-bottom: 1px solid #e5eaf2;
}

.modal-title {
    color: #17233b;
    font-weight: 750;
}

.modal-body {
    padding: 22px;
}

.modal-footer {
    padding: 16px 22px;
    border-top: 1px solid #e5eaf2;
}

.btn-primary {
    background-color: #2457d6;
    border-color: #2457d6;
}

.btn-primary:hover {
    background-color: #173b91;
    border-color: #173b91;
}

@media (max-width: 575.98px) {
    .app-navbar-inner {
        width: calc(100% - 24px);
        min-height: 66px;
        gap: 8px;
    }

    .brand-mark {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        font-size: 18px;
    }

    .brand-name {
        font-size: 16px;
    }

    .brand-caption {
        font-size: 10px;
    }

    .navbar-actions {
        gap: 6px;
    }

    .version-badge {
        display: none;
    }

    .menu-toggle {
        width: 40px;
        height: 40px;
    }

    .app-main {
        padding: 0;
    }

    .app-footer-inner {
        align-items: flex-start;
        flex-direction: column;
    }

    .modal-body {
        padding: 16px;
    }

    .modal-header {
        padding: 15px 17px;
    }

    .modal-footer {
        padding: 13px 16px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .menu-toggle, .queue-badge, .back-to-top, .custom-offcanvas .list-group-item a, .queue-item {
        transition: none;
    }
}

.install-pwa-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 11px;
    padding: 11px 18px;

    color: #fff;
    background: linear-gradient(135deg, #3478f6 0%, #1746bd 100%);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 13px;

    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.1px;

    box-shadow: 0 5px 14px rgba(36, 87, 214, 0.25),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);

    cursor: pointer;
    overflow: hidden;
    transition: transform .2s ease,
    box-shadow .2s ease,
    filter .2s ease;

    animation: install-pwa-glow 2.5s ease-in-out infinite;
}

.install-pwa-btn i {
    display: grid;
    place-items: center;
    width: 30px;
    height: 30px;
    color: #2457d6;
    background: #fff;
    border-radius: 9px;
    font-size: 15px;
}

.install-pwa-btn:hover {
    color: #fff;
    filter: brightness(1.08);
    transform: translateY(-2px);
    box-shadow: 0 9px 24px rgba(36, 87, 214, 0.35);
}

.install-pwa-btn:active {
    transform: translateY(0) scale(.98);
}

@keyframes install-pwa-glow {
    0%, 100% {
        box-shadow: 0 5px 14px rgba(36, 87, 214, 0.22);
    }
    50% {
        box-shadow: 0 6px 22px rgba(36, 87, 214, 0.4);
    }
}

@media (max-width: 575.98px) {
    .install-pwa-btn {
        width: 100%;
        min-height: 48px;
        font-size: 14px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .install-pwa-btn {
        animation: none;
        transition: none;
    }
}
</style>

