<template>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">{{ user.name }}</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Email:</strong> {{ user.email }}
                    </li>
                    <li class="list-group-item">
                        <strong>Telegram Chat ID:</strong> {{ user.telegram_chat_id || 'не указан' }}
                    </li>
                    <li class="list-group-item">
                        <strong>Роль:</strong> {{ roleName(user.role) }}
                    </li>
                    <li class="list-group-item">
                        <strong>Процент:</strong> {{ user.percent }}%
                    </li>
                </ul>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-outline-secondary me-2" @click="$emit('close')">Закрыть</button>
                <button class="btn btn-primary" @click="$emit('edit', user)">Редактировать</button>
            </div>
        </div>

</template>

<script>
export default {
    name: 'UserCard',
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    methods: {
        roleName(role) {
            switch (role) {
                case 0: return 'Пользователь'
                case 1: return 'Агент'
                case 2: return 'Поставщик'
                case 3: return 'Администратор'
                default: return 'Неизвестно'
            }
        }
    }
}
</script>
