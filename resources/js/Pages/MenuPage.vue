
<script setup>
import { onMounted, computed, ref } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";

import { useUsersStore } from "@/stores/users";
import { useUserSettingsStore } from "@/stores/userSettings";

import MenuAdmin from "@/Components/MenuAdmin.vue";
import MenuUser from "@/Components/MenuUser.vue";
import MenuAgent from "@/Components/MenuAgent.vue";
import MenuSupplier from "@/Components/MenuSupplier.vue";
import MenuSuperAdmin from "@/Components/MenuSuperAdmin.vue";
import RoleSwitcher from "@/Components/Users/RoleSwitcher.vue";
import FontSettingsModal from "@/Components/Settings/FontSettingsModal.vue";

const { t } = useI18n();
const router = useRouter();

const userStore = useUsersStore();
const settingsStore = useUserSettingsStore();

const user = computed(() => userStore.self || null);





const openPrimaryRegistration = () => {
    const element = document.getElementById("primaryUserModal");

    if (element && window.bootstrap?.Modal) {
        window.bootstrap.Modal
            .getOrCreateInstance(element)
            .show();
    }
};

onMounted(async () => {

    await settingsStore.fetchSettings();

    if (user.value?.blocked_at != null) {
        router.push({ name: "BlockedPage" });
    }
});
</script>

<template>
    <main class="account-page container-fluid py-3 py-lg-4">
        <!-- Заголовок -->
        <header class="account-header mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="account-header__icon">
                    <i class="fa-solid fa-table-columns"></i>
                </div>

                <div class="flex-grow-1">
                    <h1 class="account-title mb-1">
                        Личный кабинет
                    </h1>
                    <p class="account-subtitle mb-0">
                        Управление профилем и доступными разделами
                    </p>
                </div>

                <button
                    class="account-settings-btn"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#fontSettingsModal"
                    title="Настройки отображения"
                    aria-label="Настройки отображения"
                >
                    <i class="fa-solid fa-text-height"></i>
                    <span class="d-none d-sm-inline">
                        Настройки шрифта
                    </span>
                </button>
            </div>
        </header>

        <template v-if="user">

            <!-- Профиль -->
            <section
                v-if="user.role > 0"
                class="profile-card mb-4"
            >
                <div class="profile-card__avatar">
                    <i class="fa-solid fa-user-gear"></i>
                </div>

                <div class="profile-card__content">
                    <span class="profile-card__eyebrow">
                        Настройки аккаунта
                    </span>
                    <h2 class="profile-card__title">
                        Ваш профиль
                    </h2>
                    <p class="profile-card__description mb-0">
                        Проверьте и обновите свои персональные данные.
                    </p>
                </div>

                <button
                    type="button"
                    class="profile-card__button"
                    @click="openPrimaryRegistration"
                >
                    <span>Редактировать профиль</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </section>

            <!-- Меню пользователя -->
            <section v-if="user.role === 0" class="menu-section">
                <MenuUser />
            </section>

            <!-- Меню соответствующей роли -->
            <section
                v-if="user.role === 1"
                class="menu-section"
            >
                <MenuAgent />
            </section>

            <section
                v-if="user.role === 2"
                class="menu-section"
            >
                <MenuSupplier />
            </section>

            <section
                v-if="user.role === 3"
                class="menu-section"
            >
                <MenuAdmin />
            </section>

            <section
                v-if="user.role === 4"
                class="menu-section"
            >
                <MenuSuperAdmin />
            </section>

            <!-- Переключение ролей -->
            <section
                v-if="user.base_role === 4"
                class="role-switcher-section mt-4"
            >
                <div class="section-heading mb-3">
                    <div class="section-heading__icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h2 class="section-heading__title mb-1">
                            Управление доступом
                        </h2>
                        <p class="section-heading__subtitle mb-0">
                            Переключение доступных ролей
                        </p>
                    </div>
                </div>

                <div class="role-switcher-card">
                    <RoleSwitcher />
                </div>
            </section>
        </template>

        <FontSettingsModal />
    </main>
</template>

<style scoped>
.account-page {
    --account-primary: #2457d6;
    --account-primary-dark: #173b91;
    --account-text: #17233b;
    --account-muted: #718096;
    --account-border: #e5eaf2;

    max-width: 1600px;
    margin-inline: auto;
    color: var(--account-text);
}

.account-header {
    padding: 8px 0 20px;
    border-bottom: 1px solid var(--account-border);
}

.account-header__icon,
.section-heading__icon {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    border-radius: 15px;
    color: var(--account-primary);
    background: #edf3ff;
    font-size: 21px;
}

.account-title {
    font-size: clamp(23px, 3vw, 30px);
    font-weight: 750;
    letter-spacing: -0.7px;
}

.account-subtitle,
.section-heading__subtitle {
    color: var(--account-muted);
    font-size: 14px;
    line-height: 1.6;
}

.account-settings-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 42px;
    padding: 10px 14px;
    border: 1px solid var(--account-border);
    border-radius: 12px;
    background: #fff;
    color: var(--account-text);
    font-size: 14px;
    font-weight: 600;
    transition: border-color .2s, background .2s;
}

.account-settings-btn:hover {
    border-color: #b7c9f5;
    background: #f5f8ff;
}

.profile-card {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 24px;
    border: 1px solid #dce6fb;
    border-radius: 20px;
    background: linear-gradient(115deg, #f4f7ff, #fff 75%);
}

.profile-card__avatar {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    border-radius: 18px;
    background: var(--account-primary);
    color: #fff;
    font-size: 25px;
}

.profile-card__content {
    flex: 1;
    min-width: 0;
}

.profile-card__eyebrow {
    display: block;
    margin-bottom: 4px;
    color: var(--account-primary);
    font-size: 12px;
    font-weight: 750;
    letter-spacing: .7px;
    text-transform: uppercase;
}

.profile-card__title {
    margin-bottom: 5px;
    font-size: 20px;
    font-weight: 750;
}

.profile-card__description {
    color: var(--account-muted);
    font-size: 14px;
    line-height: 1.6;
}

.profile-card__button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    min-height: 46px;
    padding: 12px 18px;
    border: 0;
    border-radius: 12px;
    background: var(--account-primary);
    color: #fff;
    font-size: 14px;
    font-weight: 650;
    transition: background .2s, transform .2s;
}

.profile-card__button:hover {
    background: var(--account-primary-dark);
    transform: translateY(-1px);
}

.menu-section {
    min-width: 0;
}

.role-switcher-section {
    padding-top: 24px;
    border-top: 1px solid var(--account-border);
}

.section-heading {
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-heading__icon {
    width: 42px;
    height: 42px;
    border-radius: 13px;
    font-size: 18px;
}

.section-heading__title {
    font-size: 18px;
    font-weight: 750;
}

.role-switcher-card {
    padding: 20px;
    border: 1px solid var(--account-border);
    border-radius: 16px;
    background: #fff;
}

@media (max-width: 575.98px) {
    .account-header {
        padding-bottom: 16px;
    }

    .account-header__icon {
        width: 42px;
        height: 42px;
        border-radius: 13px;
    }

    .account-subtitle {
        font-size: 12px;
    }

    .account-settings-btn {
        width: 42px;
        min-width: 42px;
        padding: 0;
    }

    .profile-card {
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 14px;
        padding: 18px;
        border-radius: 16px;
    }

    .profile-card__avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        font-size: 21px;
    }

    .profile-card__content {
        flex-basis: calc(100% - 66px);
    }

    .profile-card__title {
        font-size: 18px;
    }

    .profile-card__button {
        width: 100%;
        justify-content: space-between;
    }

    .role-switcher-card {
        padding: 14px;
    }
}


/* Режим работы от имени другого пользователя */
.impersonation-banner {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    border: 1px solid rgba(37, 99, 235, .22);
    border-radius: 16px;
    background: linear-gradient(110deg, rgba(37, 99, 235, .09), rgba(255, 255, 255, .98));
}

.impersonation-banner__icon {
    display: grid;
    flex: 0 0 44px;
    width: 44px;
    height: 44px;
    place-items: center;
    border-radius: 13px;
    color: #2563eb;
    background: rgba(37, 99, 235, .12);
    font-size: 19px;
}

.impersonation-banner__content {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
    gap: 3px;
}

.impersonation-banner__content strong { color: #173b78; font-size: 14px; }
.impersonation-banner__content span { color: #334155; font-size: 13px; }
.impersonation-banner__content small { color: #64748b; font-size: 11px; }

.impersonation-banner__button {
    display: inline-flex;
    flex: 0 0 auto;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 40px;
    padding: 9px 14px;
    border: 0;
    border-radius: 11px;
    color: #fff;
    background: #1d4ed8;
    font-size: 13px;
    font-weight: 700;
    transition: background .2s ease, opacity .2s ease;
}

.impersonation-banner__button:hover { background: #1e40af; }
.impersonation-banner__button:disabled { cursor: wait; opacity: .65; }

@media (max-width: 575.98px) {
    .impersonation-banner { align-items: flex-start; flex-wrap: wrap; padding: 14px; }
    .impersonation-banner__content { flex-basis: calc(100% - 60px); }
    .impersonation-banner__button { width: 100%; }
}

</style>
