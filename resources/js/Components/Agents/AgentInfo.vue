<template>



    <ul class="list-group list-group-flush">
        <li class="list-group-item">
            <strong>ID:</strong> {{ agent.id }}
        </li>
        <li class="list-group-item">
            <strong>Имя:</strong> {{ agent.name }}
        </li>
        <li class="list-group-item">
            <strong>Имя из телеграм:</strong> {{ agent.fio_from_telegram }}
        </li>

        <li class="list-group-item">
            <strong>Id из телеграм:</strong> {{ agent.telegram_chat_id }}
        </li>

        <li class="list-group-item">
            <strong>Роль:</strong> {{ roleName }}
        </li>


        <li class="list-group-item">
            <strong>Дата рождения:</strong> {{ formattedBirthday }}
        </li>

        <li class="list-group-item">
            <strong>Телефон:</strong> {{ agent.agent.phone }}
        </li>
        <li class="list-group-item">
            <strong>Email:</strong> {{ agent.agent.email }}
        </li>
        <li class="list-group-item">
            <strong>Регион:</strong> {{ agent.agent.region }}
        </li>
        <li v-if="agent.in_learning" class="list-group-item">
            <strong>Обучается у: </strong>
            <template v-if="agent.mentor">
                {{ agent.mentor?.name || '-' }}
            </template>
            <template v-else>
                Наставник не указан
            </template>
        </li>


    </ul>




    <h6 class="fw-bold my-2">Начисление процентов</h6>
    <PercentageList
        class="mb-3"
        :for-select="false"
        :agent-id="agent.id"></PercentageList>

    <template v-if="edit">
        <button class="btn btn-primary p-3 w-100" @click="$emit('edit', agent)">Редактировать</button>
    </template>
</template>

<script>

import PercentageList from "@/Components/Percentage/PercentageList.vue";
import moment from "moment/moment.js";

export default {
    name: 'AgentInfo',
    components: {PercentageList},
    props: {
        edit: {
            type: Boolean,
            required: false,
            default: true,
        },
        agent: {
            type: Object,
            required: true
        }
    },
    mounted() {
        console.log("agent",this.agent)
    },
    computed:{
        roleName() {
            switch (this.agent.role) {
                case 0: return 'Пользователь'
                case 1: return 'Администратор'
                case 2: return 'Поставщик'
                case 3: return 'Старший администратор'
                case 4: return 'Суперадмин'
                default: return 'Неизвестно'
            }
        },
        formattedBirthday(){
            return this.agent?.birthday
                ? moment(this.agent.birthday).format('DD.MM.YYYY')
                : 'не указана';
        }
    },
}
</script>
