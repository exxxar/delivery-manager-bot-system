<template>
    <article class="user-profile-card" :class="{ 'is-expanded': !minimize }">
        <div class="profile-card-accent"></div>

        <div class="profile-card-body">
            <button
                type="button"
                class="profile-heading"
                :aria-expanded="String(!minimize)"
                @click="minimize = !minimize"
            >
                <span class="profile-avatar" aria-hidden="true">
                    <i class="fa-solid fa-user"></i>
                </span>
                <span class="profile-heading-text">
                    <span class="profile-name">{{ user.name }}</span>
                    <span class="profile-role" :class="roleClass(user.role)">{{ roleLabel(user.role) }}</span>
                </span>
                <span class="expand-indicator" aria-hidden="true">
                    <i :class="minimize ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-up'"></i>
                </span>
            </button>

            <transition name="profile-details">
                <div v-if="!minimize" class="profile-details">
                    <div class="detail-row">
                        <span class="detail-icon"><i class="fa-brands fa-telegram"></i></span>
                        <div class="detail-content">
                            <span class="detail-label">Имя в Telegram</span>
                            <span class="detail-value">{{ user.fio_from_telegram || '—' }}</span>
                        </div>
                    </div>

                    <div v-if="user.id" class="detail-row">
                        <span class="detail-icon"><i class="fa-solid fa-hashtag"></i></span>
                        <div class="detail-content">
                            <span class="detail-label">ID в системе</span>
                            <span class="detail-value">{{ user.id }}</span>
                        </div>
                    </div>

                    <div v-if="user.telegram_chat_id" class="detail-row">
                        <span class="detail-icon"><i class="fa-solid fa-comment-dots"></i></span>
                        <div class="detail-content">
                            <span class="detail-label">Telegram Chat ID</span>
                            <span class="detail-value">{{ user.telegram_chat_id }}</span>
                        </div>
                    </div>

                    <template v-if="user.role >= 3">
                        <div class="detail-row">
                            <span class="detail-icon"><i class="fa-solid fa-percent"></i></span>
                            <div class="detail-content">
                                <span class="detail-label">Админский процент</span>
                                <span class="detail-value percent-value">{{ user.percent ?? 0 }}%</span>
                            </div>
                        </div>

                        <div class="work-status-panel" :class="{ 'is-working': user.is_work }">
                            <div class="work-status-copy">
                                <span class="work-status-title">Рабочий статус</span>
                                <span class="work-status-description">
                                    {{ user.is_work ? 'Вы отмечены как работающий' : 'Вы отмечены как неработающий' }}
                                </span>
                            </div>
                            <div class="form-check form-switch work-switch mb-0">
                                <input
                                    @change="changeAdminWorkStatus(user)"
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    v-model="user.is_work"
                                    :id="`is-work-${user.id}`"
                                />
                                <label class="visually-hidden" :for="`is-work-${user.id}`">Рабочий статус</label>
                            </div>
                        </div>
                    </template>
                </div>
            </transition>
        </div>
    </article>
</template>

<script>
import { useUsersStore } from "@/stores/users";

export default {
    name: 'UserProfileCard',
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            userStore: useUsersStore(),
            minimize: true
        };
    },
    methods: {
        async changeAdminWorkStatus(user) {
            await this.userStore.updateWorkStatus(user.id, user.is_work);
        },
        roleLabel(role) {
            const roles = {
                0: 'Пользователь',
                1: 'Администратор',
                2: 'Поставщик',
                3: 'Старший администратор',
                4: 'Суперадмин'
            };
            return roles[role] || 'Неизвестно';
        },
        roleClass(role) {
            if (role >= 4) return 'role-superadmin';
            if (role >= 3) return 'role-senior';
            if (role === 2) return 'role-supplier';
            if (role === 1) return 'role-admin';
            return 'role-user';
        }
    }
};
</script>

<style scoped>
.user-profile-card {
    --profile-navy: #172b4d;
    --profile-blue: #246bfd;
    --profile-red: #e5484d;
    position: relative;
    overflow: hidden;
    width: 100%;
    background: #fff;
    border: 1px solid #e6ebf2;
    border-radius: 18px;
    box-shadow: 0 5px 20px rgba(23, 43, 77, 0.06);
    transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
}

.user-profile-card:hover {
    border-color: #d5e2fb;
    box-shadow: 0 10px 28px rgba(23, 43, 77, 0.1);
    transform: translateY(-2px);
}

.profile-card-accent {
    height: 4px;
    background: linear-gradient(90deg, var(--profile-blue) 0%, #69a0ff 68%, var(--profile-red) 100%);
}

.profile-card-body {
    padding: 16px;
}

.profile-heading {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 0;
    color: inherit;
    text-align: left;
    background: transparent;
    border: 0;
    cursor: pointer;
}

.profile-avatar {
    display: inline-flex;
    flex: 0 0 46px;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    color: var(--profile-blue);
    font-size: 19px;
    background: #edf3ff;
    border: 1px solid #dce8ff;
    border-radius: 14px;
}

.profile-heading-text {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-width: 0;
    gap: 5px;
}

.profile-name {
    overflow-wrap: anywhere;
    color: var(--profile-navy);
    font-size: 1rem;
    font-weight: 750;
    line-height: 1.3;
}

.profile-role {
    display: inline-flex;
    align-self: flex-start;
    align-items: center;
    padding: 4px 9px;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 700;
    line-height: 1.2;
}

.role-user { color: #475569; background: #f1f5f9; }
.role-admin { color: #1d4ed8; background: #eaf1ff; }
.role-supplier { color: #087e8b; background: #e5f8fa; }
.role-senior { color: #a45308; background: #fff3df; }
.role-superadmin { color: #b4232d; background: #ffebed; }

.expand-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 32px;
    width: 32px;
    height: 32px;
    color: #64748b;
    background: #f5f7fb;
    border-radius: 10px;
    transition: background .2s ease, color .2s ease;
}

.profile-heading:hover .expand-indicator {
    color: var(--profile-blue);
    background: #edf3ff;
}

.profile-details {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid #edf0f5;
}

.detail-row {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    min-width: 0;
}

.detail-icon {
    display: inline-flex;
    flex: 0 0 34px;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    color: #4c6fae;
    background: #f0f5ff;
    border-radius: 10px;
}

.detail-content {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-width: 0;
    gap: 3px;
}

.detail-label {
    color: #7b8798;
    font-size: .75rem;
    font-weight: 600;
}

.detail-value {
    overflow-wrap: anywhere;
    color: #26364b;
    font-size: .9rem;
    font-weight: 600;
}

.percent-value { color: var(--profile-blue); }

.work-status-panel {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 13px 14px;
    background: #fff0f0;
    border: 1px solid #ffdddd;
    border-radius: 13px;
}

.work-status-panel.is-working {
    background: #ecfdf3;
    border-color: #c9f0d8;
}

.work-status-copy {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.work-status-title {
    color: var(--profile-navy);
    font-size: .86rem;
    font-weight: 750;
}

.work-status-description {
    color: #69778a;
    font-size: .76rem;
}

.work-switch { padding-left: 2.5em; }
.work-switch .form-check-input {
    width: 2.5em;
    height: 1.35em;
    margin-left: -2.5em;
    cursor: pointer;
}
.work-switch .form-check-input:checked {
    background-color: #16a05d;
    border-color: #16a05d;
}

.profile-details-enter-active,
.profile-details-leave-active {
    transition: opacity .18s ease, transform .18s ease;
}
.profile-details-enter-from,
.profile-details-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}

@media (max-width: 480px) {
    .profile-card-body { padding: 13px; }
    .profile-avatar { flex-basis: 40px; width: 40px; height: 40px; border-radius: 12px; }
    .profile-name { font-size: .94rem; }
    .work-status-panel { align-items: flex-start; }
}

@media (prefers-reduced-motion: reduce) {
    .user-profile-card,
    .expand-indicator,
    .profile-details-enter-active,
    .profile-details-leave-active {
        transition: none;
    }
}
</style>
