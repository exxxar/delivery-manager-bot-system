<template>
    <div class="container-fluid p-3">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Информация об агенте</h5>
            </div>
            <div class="card-body">
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

                </ul>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-outline-secondary me-2" @click="$emit('close')">Закрыть</button>
                <button class="btn btn-primary" @click="$emit('edit', agent)">Редактировать</button>
            </div>
        </div>
    </div>
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
