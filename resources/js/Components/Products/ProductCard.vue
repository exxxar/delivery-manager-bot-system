<template>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">{{ product.name }}</h5>
            </div>
            <div class="card-body">
                <p class="card-text">{{ product.description }}</p>

                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span><strong>Цена:</strong></span>
                        <span>{{ product.price }} ₽</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><strong>Количество:</strong></span>
                        <span>{{ product.count }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><strong>Поставщик:</strong></span>
                        <span>{{ product.supplier?.name || 'не выбран' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><strong>Категория:</strong></span>
                        <span>{{ product.category?.name || 'не выбрана' }}</span>
                    </li>
                </ul>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-outline-secondary me-2" @click="$emit('close')">Закрыть</button>
                <button class="btn btn-primary" @click="$emit('edit', product)">Редактировать</button>
            </div>
        </div>

</template>

<script>
export default {
    name: 'ProductCard',
    props: {
        product: {
            type: Object,
            required: true
        }
    }
}
</script>
