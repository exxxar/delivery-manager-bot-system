<template>
    <ul class="list-group list-group-flush">
        <li class="list-group-item">
            <strong>Имя в системе:</strong> {{ user.name }}
        </li>
        <li class="list-group-item">
            <strong>Имя из Telegram:</strong> {{ user.fio_from_telegram || 'не указано' }}
        </li>
        <li class="list-group-item">
            <strong>Email:</strong> {{ user.agent?.email || 'не указан' }}
        </li>
        <li class="list-group-item">
            <strong>Телефон:</strong> {{ user.agent?.phone || 'не указан' }}
        </li>
        <li class="list-group-item">
            <strong>Id чата телеграм:</strong> {{ user.telegram_chat_id || 'не указан' }}
        </li>

        <li class="list-group-item">
            <strong>Роль:</strong> {{ roleName(user.role) }}
        </li>

        <li class="list-group-item">
            <strong>Дата рождения:</strong> {{ formattedBirthday }}
        </li>


    </ul>

    <button class="btn btn-primary w-100 p-3" @click="$emit('edit', user)">Редактировать</button>

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
    computed:{
        formattedBirthday(){
            return this.user?.birthday
                ? moment(this.user.birthday).format('DD.MM.YYYY')
                : 'не указана';
        }
    },
    methods: {
        roleName(role) {
            switch (role) {
                case 0: return 'Пользователь'
                case 1: return 'Администратор'
                case 2: return 'Поставщик'
                case 3: return 'Старший администратор'
                case 4: return 'Суперадмин'
                default: return 'Неизвестно'
            }
        }
    }
}
</script>
