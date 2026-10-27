<template>
    <section class="user-card">
        <div class="user-card__header">
            <div class="user-card__avatar" aria-hidden="true">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="user-card__heading">
                <span class="user-card__eyebrow">Профиль пользователя</span>
                <h5 class="user-card__name">{{ user.name || 'Имя не указано' }}</h5>
                <span class="user-card__role">
                    <i class="fa-solid fa-shield-halved"></i>
                    {{ roleName(user.role) }}
                </span>
            </div>
        </div>

        <div class="user-card__details">
            <div class="user-card__detail">
                <span class="user-card__icon"><i class="fa-solid fa-user-tag"></i></span>
                <div class="user-card__value-wrap">
                    <span class="user-card__label">Имя в системе</span>
                    <span class="user-card__value">{{ user.name || 'не указано' }}</span>
                </div>
            </div>

            <div class="user-card__detail">
                <span class="user-card__icon"><i class="fa-brands fa-telegram"></i></span>
                <div class="user-card__value-wrap">
                    <span class="user-card__label">Имя из Telegram</span>
                    <span class="user-card__value">{{ user.fio_from_telegram || 'не указано' }}</span>
                </div>
            </div>

            <div class="user-card__detail">
                <span class="user-card__icon"><i class="fa-solid fa-envelope"></i></span>
                <div class="user-card__value-wrap">
                    <span class="user-card__label">Email</span>
                    <span class="user-card__value">{{ user.agent?.email || 'не указан' }}</span>
                </div>
            </div>

            <div class="user-card__detail">
                <span class="user-card__icon"><i class="fa-solid fa-phone"></i></span>
                <div class="user-card__value-wrap">
                    <span class="user-card__label">Телефон</span>
                    <span class="user-card__value">{{ user.agent?.phone || 'не указан' }}</span>
                </div>
            </div>

            <div class="user-card__detail">
                <span class="user-card__icon"><i class="fa-solid fa-comments"></i></span>
                <div class="user-card__value-wrap">
                    <span class="user-card__label">ID чата Telegram</span>
                    <span class="user-card__value user-card__value--mono">{{ user.telegram_chat_id || 'не указан' }}</span>
                </div>
            </div>

            <div class="user-card__detail">
                <span class="user-card__icon"><i class="fa-solid fa-cake-candles"></i></span>
                <div class="user-card__value-wrap">
                    <span class="user-card__label">Дата рождения</span>
                    <span class="user-card__value">{{ formattedBirthday }}</span>
                </div>
            </div>
        </div>

        <div class="user-card__footer">
            <button class="btn user-card__edit-btn" type="button" @click="$emit('edit', user)">
                <i class="fa-solid fa-pen-to-square me-2"></i>
                Редактировать профиль
            </button>
        </div>
    </section>
</template>

<script>
import moment from 'moment';

export default {
    name: 'UserCard',
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    emits: ['edit'],
    computed: {
        formattedBirthday() {
            return this.user?.birthday
                ? moment(this.user.birthday).format('DD.MM.YYYY')
                : 'не указана';
        }
    },
    methods: {
        roleName(role) {
            switch (role) {
                case 0: return 'Пользователь';
                case 1: return 'Администратор';
                case 2: return 'Поставщик';
                case 3: return 'Старший администратор';
                case 4: return 'Суперадмин';
                default: return 'Неизвестно';
            }
        }
    }
}
</script>

<style scoped>
.user-card {
    overflow: hidden;
    background: #fff;
    border: 1px solid #e4eaf3;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(20, 43, 78, 0.07);
    color: #172b4d;
}

.user-card__header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 24px;
    background: linear-gradient(120deg, #f0f6ff 0%, #fff 75%);
    border-bottom: 1px solid #e8edf5;
}

.user-card__avatar {
    display: grid;
    flex: 0 0 64px;
    width: 64px;
    height: 64px;
    place-items: center;
    border-radius: 18px;
    background: linear-gradient(145deg, #1769e0, #0d3d91);
    color: #fff;
    font-size: 1.6rem;
    box-shadow: 0 8px 18px rgba(23, 105, 224, 0.22);
}

.user-card__heading { min-width: 0; }

.user-card__eyebrow {
    display: block;
    margin-bottom: 4px;
    color: #6b7c93;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.user-card__name {
    margin: 0 0 9px;
    color: #142b4e;
    font-size: 1.2rem;
    font-weight: 750;
    overflow-wrap: anywhere;
}

.user-card__role {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 5px 10px;
    border: 1px solid #d6e5ff;
    border-radius: 999px;
    background: #eaf2ff;
    color: #1558b0;
    font-size: .78rem;
    font-weight: 700;
}

.user-card__details {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0 22px;
    padding: 8px 24px;
}

.user-card__detail {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    min-width: 0;
    padding: 16px 0;
    border-bottom: 1px solid #edf1f6;
}

.user-card__detail:nth-last-child(-n + 2) { border-bottom: 0; }

.user-card__icon {
    display: grid;
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    place-items: center;
    border-radius: 12px;
    background: #f0f5fc;
    color: #2168c9;
    font-size: 1rem;
}

.user-card__value-wrap {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 4px;
}

.user-card__label {
    color: #718096;
    font-size: .78rem;
    font-weight: 600;
}

.user-card__value {
    color: #1b2f4b;
    font-size: .94rem;
    font-weight: 650;
    overflow-wrap: anywhere;
}

.user-card__value--mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: .85rem;
}

.user-card__footer {
    padding: 18px 24px 24px;
    border-top: 1px solid #edf1f6;
    background: #fbfcfe;
}

.user-card__edit-btn {
    width: 100%;
    padding: 13px 18px;
    border: 0;
    border-radius: 12px;
    background: linear-gradient(110deg, #1769e0, #0d4fae);
    color: #fff;
    font-weight: 700;
    box-shadow: 0 7px 16px rgba(23, 105, 224, .18);
    transition: transform .18s ease, box-shadow .18s ease;
}

.user-card__edit-btn:hover,
.user-card__edit-btn:focus-visible {
    transform: translateY(-1px);
    color: #fff;
    box-shadow: 0 10px 22px rgba(23, 105, 224, .26);
}

@media (max-width: 575.98px) {
    .user-card { border-radius: 16px; }
    .user-card__header { padding: 18px; gap: 12px; }
    .user-card__avatar { flex-basis: 52px; width: 52px; height: 52px; border-radius: 15px; font-size: 1.3rem; }
    .user-card__name { font-size: 1.05rem; }
    .user-card__details { grid-template-columns: minmax(0, 1fr); padding: 4px 18px; }
    .user-card__detail:nth-last-child(-n + 2) { border-bottom: 1px solid #edf1f6; }
    .user-card__detail:last-child { border-bottom: 0; }
    .user-card__footer { padding: 16px 18px 18px; }
}
</style>
