<template>
    <div class="container-fluid p-3">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">{{ customer.name }}</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Компания:</strong> {{ customer.company_name }}
                    </li>
                    <li class="list-group-item">
                        <strong>Адрес:</strong> {{ customer.address }}
                    </li>
                    <li class="list-group-item">
                        <strong>Телефон:</strong> {{ customer.phone }}
                    </li>
                    <li class="list-group-item">
                        <strong>Email:</strong> {{ customer.email }}
                    </li>
                </ul>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-outline-secondary me-2" @click="$emit('close')">Закрыть</button>
                <button class="btn btn-primary" @click="$emit('edit', customer)">Редактировать</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CustomerCard',
    props: {
        customer: {
            type: Object,
            required: true
        }
    }
}
</script>
