<script setup>
import { Head } from '@inertiajs/vue3'
import GlobalAlert from "@/Components/GlobalAlert.vue";
import GlobalConfirmModal from "@/Components/GlobalConfirmModal.vue";
import UserProfileCard from "@/Components/Users/UserProfileCard.vue";
import PrimaryForm from "@/Components/Users/Forms/PrimaryForm.vue";
import {
    getQueue,
    removeFromQueue,
    base64ToFile,
} from "@/utilites/offlineQueue.js";
</script>

<template>
    <Head>
        <title>Автоматический учет доставки</title>
        <meta name="description" content="CashMan - система твоего бизнеса внутри"/>
    </Head>

    <!-- Индикатор отсутствия сети -->
    <Transition name="offline-banner">
        <div v-if="isOffline" class="offline-banner">
            <i class="fa-solid fa-wifi"></i>
            <span>Нет подключения к интернету</span>
        </div>
    </Transition>

    <header class="fixed-top-menu" data-bs-theme="dark">
        <div class="navbar shadow shadow-sm">
            <div class="container flex-row-reverse p-2">

                <!-- Счётчик офлайн-заявок (кликабельный) -->
                <span
                    v-if="offlineQueueCount > 0"
                    class="badge bg-warning text-dark me-2 align-middle queue-badge"
                    data-bs-toggle="modal"
                    data-bs-target="#offlineQueueModal"
                    @click="refreshQueueList"
                    title="Нажмите, чтобы просмотреть очередь"
                >
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    {{ offlineQueueCount }}
                </span>

                <span data-bs-toggle="modal" data-bs-target="#bot-info-modal"
                      class="text-primary fw-bold cursor-pointer">
                    Система управления доставками
                </span>

                <button class="btn btn-link rounded-0 border-0 p-1" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#sidebar-menu"
                        aria-controls="sidebar-menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <GlobalAlert />
    <GlobalConfirmModal />

    <div class="container-lg py-3">
        <slot/>
    </div>

    <footer class="text-body-secondary" style="padding: 0 0 90px 0;">
        <div class="container d-flex justify-content-center flex-column align-items-center">
            <p class="d-flex justify-content-center my-3">
                <a href="javascript:void(0)" @click="scrollTop">
                    <i class="fa-solid fa-arrow-up mr-2"></i>Вернуться наверх
                </a>
            </p>
        </div>
    </footer>

    <!-- Боковое меню -->
    <div
        class="offcanvas offcanvas-start custom-offcanvas"
        style="width: 70%; border-radius: 0 10px 10px 0;"
        tabindex="-1"
        id="sidebar-menu"
        aria-labelledby="offcanvasExampleLabel"
    >
        <div class="offcanvas-header">
            <h6 class="offcanvas-title" id="offcanvasExampleLabel">Меню</h6>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
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
                    <p @click="copy(self.telegram_chat_id)" class="small mb-0">
                        Ваш пароль
                        <span class="fw-bold text-primary">
                            <i class="fa-solid fa-copy me-1"></i>{{ self.telegram_chat_id }}
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
    <div class="modal fade" id="offlineQueueModal" tabindex="-1" aria-labelledby="offlineQueueModalLabel" aria-hidden="true">
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

                    <!-- Блок: нет интернета -->
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

                    <!-- Блок: есть интернет -->
                    <div v-else class="alert alert-success d-flex align-items-center mb-3">
                        <i class="fa-solid fa-circle-check fs-4 me-2"></i>
                        <div class="flex-grow-1">
                            <strong>Подключение восстановлено</strong>
                            <p class="mb-0 small">Можно отправить все заявки прямо сейчас.</p>
                        </div>
                    </div>

                    <!-- Список задач -->
                    <div v-if="queueItems.length > 0">
                        <div
                            v-for="(item, index) in queueItems"
                            :key="item.id"
                            class="queue-item card mb-2"
                        >
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1 me-2">
                                        <!-- Заголовок -->
                                        <div class="fw-bold mb-1">
                                            <i class="fa-solid fa-file-lines text-primary me-1"></i>
                                            {{ item.formData.title || 'Без названия' }}
                                            <span class="badge ms-2"
                                                  :class="item.isEdit ? 'bg-info' : 'bg-success'">
                                                {{ item.isEdit ? 'Редактирование' : 'Создание' }}
                                            </span>
                                        </div>

                                        <!-- Описание (если есть) -->
                                        <p v-if="item.formData.description" class="small text-muted mb-2 text-truncate">
                                            {{ item.formData.description }}
                                        </p>

                                        <!-- Метаданные -->
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

                                        <!-- Статус ошибки после попытки синхронизации -->
                                        <div v-if="item.error" class="small text-danger mt-1">
                                            <i class="fa-solid fa-circle-xmark me-1"></i>
                                            {{ item.error }}
                                        </div>
                                    </div>

                                    <!-- Кнопка удаления -->
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

                                <!-- Индикатор отправки конкретной заявки -->
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

                    <!-- Пусто -->
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
                        <!-- Очистить всю очередь -->
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

                        <!-- Отправить все -->
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
</template>

<script>
import { useUsersStore } from "@/stores/users";
import { useSalesStore } from "@/stores/sales";
import { useModalStore } from "@/stores/utillites/useConfitmModalStore";
import { useAlertStore } from "@/stores/utillites/useAlertStore";
import {
    getQueue,
    removeFromQueue,
    incrementAttempts,
    base64ToFile,
} from "@/utilites/offlineQueue.js";

export default {
    data() {
        return {
            offlineQueueCount: 0,
            queueItems: [],
            syncingIds: [],
            userStore: useUsersStore(),
            salesStore: useSalesStore(),
            modalStore: useModalStore(),
            alertStore: useAlertStore(),
            isOffline: !navigator.onLine,
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
    },

    watch: {
        isOffline: {
            immediate: true,
            handler(isOffline) {
                document.body.classList.toggle('has-offline-banner', isOffline);
            },
        },
    },

    mounted() {
        window.addEventListener('online', this.handleOnline);
        window.addEventListener('offline', this.handleOffline);
        window.addEventListener('offline-queue-changed', this.onQueueChanged);

        this.offlineQueueCount = getQueue().length;
        this.queueItems = getQueue();

        if (this.canUseTG) {
            this.tg?.expand();
            this.tg?.BackButton?.hide();
        }
    },

    beforeUnmount() {
        window.removeEventListener('online', this.handleOnline);
        window.removeEventListener('offline', this.handleOffline);
        window.removeEventListener('offline-queue-changed', this.onQueueChanged);
        document.body.classList.remove('has-offline-banner');
    },

    methods: {
        onQueueChanged(event) {
            this.offlineQueueCount = event.detail.count;
            // Обновляем список, если модалка открыта
            if (document.getElementById('offlineQueueModal')?.classList.contains('show')) {
                this.queueItems = getQueue();
            }
        },

        handleOnline() {
            this.isOffline = false;
            window.dispatchEvent(new CustomEvent('trigger-queue-sync'));
        },

        handleOffline() {
            this.isOffline = true;
        },

        refreshQueueList() {
            this.queueItems = getQueue();
        },

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
                // Небольшая задержка, чтобы UI успел обновиться
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

                    // Помечаем ошибку в карточке
                    const idx = this.queueItems.findIndex(q => q.id === item.id);
                    if (idx !== -1) {
                        this.queueItems[idx].error =
                            e?.response?.data?.message ||
                            e?.message ||
                            'Не удалось отправить';
                    }

                    // 401 — удаляем сразу
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
                // Закрываем модалку, если всё отправлено
                const modal = bootstrap.Modal.getInstance(document.getElementById('offlineQueueModal'));
                modal?.hide();
            }
        },

        onPrimaryFormSuccess() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('primaryUserModal'));
            modal?.hide();
        },

        goTo(name) {
            this.$router.push({ name });
        },

        scrollTop() {
            window.scrollTo({ top: 80, behavior: 'smooth' });
        },

        openLink(url) {
            if (this.canUseTG) {
                this.tg.openLink(url, { try_instant_view: true });
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
                    navigator.serviceWorker.controller.postMessage({ type: 'CLEAR_CACHE' });
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
    z-index: 1100;
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

/* 🔹 Кликабельный badge с анимацией */
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

@keyframes pulse-badge {
    0%, 100% { box-shadow: 0 2px 6px rgba(255, 193, 7, 0.4); }
    50% { box-shadow: 0 2px 12px rgba(255, 193, 7, 0.7); }
}

/* 🔹 Карточки в списке очереди */
.queue-item {
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.queue-item:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
}
</style>
