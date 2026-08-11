<template>
    <div v-if="agent">
        <!-- Основная информация -->
        <div class="mb-3">
            <p><strong>Имя:</strong> {{ agent.name || 'Не указано' }}</p>
            <p><strong>Телефон:</strong> {{ agent.phone || 'Не указан' }}</p>
            <p><strong>Email:</strong> {{ agent.email || 'Не указан' }}</p>
            <p><strong>Регион:</strong> {{ agent.region || 'Не указан' }}</p>
        </div>

        <!-- Если есть вложенные объекты -->
        <p v-if="agent.user">
            <strong>Пользователь:</strong> {{ agent.user?.name || 'Не указан' }}
        </p>

        <!-- Дата рождения -->
        <p v-if="formattedBirthday !== 'не указана'">
            <strong>Дата рождения:</strong> {{ formattedBirthday }}
        </p>

        <!-- Роль -->
        <p v-if="roleName !== 'Неизвестно'">
            <strong>Роль:</strong> {{ roleName }}
        </p>

        <!-- Для массивов процентов -->
        <div v-if="agent.percentages?.length > 0" class="mt-3">
            <strong>Проценты:</strong>
            <PercentageList :percentages="agent.percentages" />
        </div>

        <!-- Дополнительная информация из Telegram -->
        <div v-if="agent.user_info" v-html="agent.user_info" class="mt-3"></div>

        <!-- Статистика -->
        <div v-if="agent.month_sales_count !== undefined" class="mt-3 p-2 bg-light rounded">
            <strong>Статистика за месяц:</strong>
            <div class="d-flex gap-3 mt-2">
                <span class="badge bg-success">
                    {{ agent.month_sales_count }} сделок
                </span>
                <span class="badge bg-info text-dark">
                    {{ formatMoney(agent.month_turnover) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Показываем loader, если агент не загружен -->
    <div v-else class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Загрузка...</span>
        </div>
        <p class="text-muted mt-2">Загрузка информации...</p>
    </div>
</template>

<script>
import PercentageList from "@/Components/Percentage/PercentageList.vue";
import moment from "moment/moment.js";

export default {
    name: 'AgentInfo',
    components: { PercentageList },
    props: {
        edit: {
            type: Boolean,
            required: false,
            default: true,
        },
        agent: {
            type: Object,
            required: true,
            default: null
        }
    },
    mounted() {
        console.log("AgentInfo mounted, agent:", this.agent);
        console.log("Agent phone:", this.agent?.phone);
        console.log("Agent role:", this.agent?.role);
    },
    computed: {
        roleName() {
            // Защита от undefined/null
            if (!this.agent || this.agent.role === undefined || this.agent.role === null) {
                return 'Неизвестно';
            }

            switch (this.agent.role) {
                case 0: return 'Пользователь';
                case 1: return 'Администратор';
                case 2: return 'Поставщик';
                case 3: return 'Старший администратор';
                case 4: return 'Суперадмин';
                default: return 'Неизвестно';
            }
        },
        formattedBirthday() {
            // Защита от undefined/null
            if (!this.agent || !this.agent.birthday) {
                return 'не указана';
            }

            try {
                return moment(this.agent.birthday).format('DD.MM.YYYY');
            } catch (e) {
                console.error('Ошибка форматирования даты:', e);
                return 'не указана';
            }
        }
    },
    methods: {
        formatMoney(value) {
            return new Intl.NumberFormat('ru-RU').format(value || 0) + ' ₽';
        }
    }
}
</script>
