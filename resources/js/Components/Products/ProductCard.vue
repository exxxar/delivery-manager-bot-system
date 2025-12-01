<template>

    <ul class="list-group list-group-flush">
        <li class="list-group-item d-flex justify-content-between">
            <span><strong>Название:</strong></span>
            <span>{{ product.name }} </span>
        </li>

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
            <span style="text-align: right;">{{ product.supplier?.name || 'не выбран' }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">
            <span><strong>Категория:</strong></span>
            <span style="text-align: right;">{{ product.category?.name || 'не выбрана' }}</span>
        </li>
        <li class="list-group-item" style="text-align: justify;">
            {{ product.description }}
        </li>

    </ul>

    <button class="btn btn-primary p-3 w-100" @click="$emit('edit', product)">Редактировать</button>

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
