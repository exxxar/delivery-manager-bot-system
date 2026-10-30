
<template>
    <div v-if="agent" class="agent-info">

        <!-- Шапка профиля -->
        <div class="agent-profile-header">
            <div class="agent-avatar">
                <i class="fa-solid fa-user-tie"></i>
            </div>

            <div class="agent-profile-main">
                <h4>{{ agent.name || 'Без имени' }}</h4>
                <div class="agent-profile-meta">
                    <span class="agent-id">ID: {{ agent.id }}</span>
                    <span v-if="roleName !== 'Неизвестно'" class="agent-role">
                        {{ roleName }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Контактная информация -->
        <section class="agent-info-section">
            <div class="agent-section-title">
                <i class="fa-solid fa-address-card"></i>
                Контактная информация
            </div>

            <div class="agent-info-grid">
                <div class="agent-info-item">
                    <div class="agent-info-icon">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div>
                        <small>Телефон</small>
                        <strong>{{ agent.phone || 'Не указан' }}</strong>
                    </div>
                </div>

                <div class="agent-info-item">
                    <div class="agent-info-icon">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <div>
                        <small>Email</small>
                        <strong>{{ agent.email || 'Не указан' }}</strong>
                    </div>
                </div>

                <div class="agent-info-item">
                    <div class="agent-info-icon">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <small>Регион</small>
                        <strong>{{ agent.region || 'Не указан' }}</strong>
                    </div>
                </div>

                <div class="agent-info-item">
                    <div class="agent-info-icon">
                        <i class="fa-solid fa-cake-candles"></i>
                    </div>
                    <div>
                        <small>Дата рождения</small>
                        <strong>{{ formattedBirthday }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <!-- Пользователь -->
        <section v-if="agent.user" class="agent-info-section">
            <div class="agent-section-title">
                <i class="fa-solid fa-user-shield"></i>
                Учётная запись
            </div>

            <div class="agent-user-card">
                <div class="agent-user-icon">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <small>Связанный пользователь</small>
                    <strong>{{ agent.user.name || 'Не указан' }}</strong>
                </div>
            </div>
        </section>

        <!-- Статистика -->
        <section
            v-if="agent.month_sales_count !== undefined"
            class="agent-info-section"
        >
            <div class="agent-section-title">
                <i class="fa-solid fa-chart-line"></i>
                Статистика за месяц
            </div>

            <div class="agent-stat-grid">
                <div class="agent-stat-card">
                    <div class="agent-stat-icon is-green">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <small>Количество сделок</small>
                        <strong>{{ agent.month_sales_count }}</strong>
                    </div>
                </div>

                <div class="agent-stat-card">
                    <div class="agent-stat-icon is-blue">
                        <i class="fa-solid fa-money-bill-trend-up"></i>
                    </div>
                    <div>
                        <small>Товарооборот</small>
                        <strong>{{ formatMoney(agent.month_turnover) }}</strong>
                    </div>
                </div>

                <div class="agent-stat-card is-salary">
                    <div class="agent-stat-icon is-violet">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div>
                        <small>Расчётная зарплата</small>
                        <strong>{{ formatMoney(agent.month_salary) }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <!-- Проценты -->
        <section
            v-if="agent.percentages?.length > 0"
            class="agent-info-section"
        >
            <div class="agent-section-title">
                <i class="fa-solid fa-percent"></i>
                Распределение процентов
            </div>

            <div class="agent-percentages">
                <PercentageList :percentages="agent.percentages" />
            </div>
        </section>

        <!-- Дополнительная информация -->
        <section v-if="agent.user_info" class="agent-info-section">
            <div class="agent-section-title">
                <i class="fa-brands fa-telegram"></i>
                Дополнительная информация
            </div>

            <div class="agent-user-info" v-html="agent.user_info"></div>
        </section>

    </div>

    <!-- Загрузка -->
    <div v-else class="agent-loading">
        <div class="spinner-border text-primary" role="status"></div>
        <p>Загрузка информации...</p>
    </div>
</template>

<script>
import PercentageList from "@/Components/Percentage/PercentageList.vue";
import moment from "moment/moment.js";

export default {
    name: 'AgentInfo',

    components: {
        PercentageList
    },

    props: {
        edit: {
            type: Boolean,
            default: true
        },
        agent: {
            type: Object,
            required: true,
            default: null
        }
    },

    computed: {
        roleName() {
            if (!this.agent || this.agent.role === undefined || this.agent.role === null) {
                return 'Неизвестно';
            }

            switch (Number(this.agent.role)) {
                case 0: return 'Пользователь';
                case 1: return 'Администратор';
                case 2: return 'Поставщик';
                case 3: return 'Старший администратор';
                case 4: return 'Суперадмин';
                default: return 'Неизвестно';
            }
        },

        formattedBirthday() {
            if (!this.agent?.birthday) {
                return 'Не указана';
            }

            const date = moment(this.agent.birthday);

            return date.isValid()
                ? date.format('DD.MM.YYYY')
                : 'Не указана';
        }
    },

    methods: {
        formatMoney(value) {
            return new Intl.NumberFormat('ru-RU', {
                maximumFractionDigits: 2
            }).format(Number(value) || 0) + ' ₽';
        }
    }
}
</script>

<style scoped>
.agent-info {
    --info-blue: #2056b4;
    --info-ink: #172d50;
    --info-muted: #8190a7;
    color: var(--info-ink);
}

/* Шапка */
.agent-profile-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    margin-bottom: 20px;
    background: linear-gradient(120deg, #edf4ff, #f8faff);
    border: 1px solid #e0eaff;
    border-radius: 18px;
}

.agent-avatar {
    width: 64px;
    height: 64px;
    flex: 0 0 64px;
    display: grid;
    place-items: center;
    border-radius: 19px;
    background: #2056b4;
    color: #fff;
    font-size: 26px;
    box-shadow: 0 5px 15px #2056b42b;
}

.agent-profile-main {
    min-width: 0;
}

.agent-profile-main h4 {
    font-size: 19px;
    font-weight: 750;
    margin: 0 0 8px;
    overflow-wrap: anywhere;
}

.agent-profile-meta {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
}

.agent-id,
.agent-role {
    padding: 5px 9px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
}

.agent-id {
    background: #fff;
    color: #63748e;
}

.agent-role {
    background: #dce9ff;
    color: #2056b4;
}

/* Секции */
.agent-info-section {
    margin-bottom: 20px;
}

.agent-section-title {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 12px;
    font-size: 14px;
    font-weight: 750;
    color: var(--info-ink);
}

.agent-section-title i {
    width: 30px;
    height: 30px;
    display: grid;
    place-items: center;
    border-radius: 9px;
    background: #edf3ff;
    color: var(--info-blue);
}

/* Информация */
.agent-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.agent-info-item {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
    padding: 13px;
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 13px;
}

.agent-info-icon,
.agent-user-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    display: grid;
    place-items: center;
    border-radius: 11px;
    background: #f0f5ff;
    color: #3768b8;
}

.agent-info-item > div:last-child,
.agent-user-card > div:last-child {
    min-width: 0;
}

.agent-info-item small,
.agent-user-card small,
.agent-stat-card small {
    display: block;
    margin-bottom: 4px;
    color: var(--info-muted);
    font-size: 11px;
}

.agent-info-item strong,
.agent-user-card strong {
    display: block;
    font-size: 13px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

/* Пользователь */
.agent-user-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    border: 1px solid #e8edf5;
    border-radius: 13px;
    background: #fff;
}

/* Статистика */
.agent-stat-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.agent-stat-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    border: 1px solid #e8edf5;
    border-radius: 14px;
    background: #fff;
    min-width: 0;
}

.agent-stat-card > div:last-child {
    min-width: 0;
}

.agent-stat-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    font-size: 17px;
}

.agent-stat-icon.is-green {
    background: #e5f7ef;
    color: #16845c;
}

.agent-stat-icon.is-blue {
    background: #e9f2ff;
    color: #2457a7;
}

.agent-stat-icon.is-violet {
    background: #f2eaff;
    color: #8652bf;
}

.agent-stat-card strong {
    display: block;
    font-size: 16px;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.agent-stat-card.is-salary {
    grid-column: 1 / -1;
    background: linear-gradient(110deg, #f4efff, #faf8ff);
    border-color: #e9defe;
}

.agent-stat-card.is-salary strong {
    color: #7046ad;
    font-size: 21px;
}

/* Проценты */
.agent-percentages {
    padding: 14px;
    border: 1px solid #e8edf5;
    border-radius: 14px;
    background: #fff;
}

/* Telegram */
.agent-user-info {
    padding: 14px;
    border: 1px solid #e8edf5;
    border-radius: 14px;
    background: #f8faff;
    font-size: 13px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

/* Загрузка */
.agent-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    color: var(--info-muted);
}

.agent-loading p {
    margin-top: 12px;
    font-size: 13px;
}

@media (max-width: 575px) {
    .agent-profile-header {
        padding: 15px;
    }

    .agent-avatar {
        width: 54px;
        height: 54px;
        flex-basis: 54px;
        font-size: 22px;
    }

    .agent-profile-main h4 {
        font-size: 16px;
    }

    .agent-info-grid {
        grid-template-columns: 1fr;
    }

    .agent-stat-grid {
        grid-template-columns: 1fr;
    }

    .agent-stat-card.is-salary {
        grid-column: auto;
    }
}
</style>
