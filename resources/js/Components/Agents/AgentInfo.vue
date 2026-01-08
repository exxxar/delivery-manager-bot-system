<template>

    <ul class="list-group list-group-flush">
        <li class="list-group-item">
            <strong>ID:</strong> {{ agent.id }}
        </li>
        <li class="list-group-item">
            <strong>Имя:</strong> {{ agent.name }}
        </li>
        <li class="list-group-item">
            <strong>Телефон:</strong> {{ agent.phone }}
        </li>
        <li class="list-group-item">
            <strong>Email:</strong> {{ agent.email }}
        </li>
        <li class="list-group-item">
            <strong>Регион:</strong> {{ agent.region }}
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


    <button class="btn btn-primary p-3 w-100" @click="$emit('edit', agent)">Редактировать</button>
</template>

<script>
export default {
    name: 'AgentInfo',
    props: {
        agent: {
            type: Object,
            required: true
        }
    }
}
</script>
