<script setup>
import ProductCategoryList from "@/Components/ProductCategory/ProductCategoryList.vue";
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
</script>

<template>
    <div class="container-fluid p-0">

        <form @submit.prevent="submitForm">

            <!-- Название -->
            <div class="form-floating mb-2">
                <input v-model="form.name" type="text" class="form-control" id="name" placeholder="Название" required>
                <label for="name">Название продукта</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.category" type="text" class="form-control" id="category" placeholder="Название"
                       required>
                <label for="category">Название категории</label>
            </div>

            <button
                :disabled="spent_time>0"
                type="submit" class="btn btn-primary w-100 p-3">
                <span v-if="spent_time>0">{{ spent_time }} сек.</span>
                <span v-else>
                       Добавить продукт
                </span>
            </button>

        </form>
    </div>
</template>

<script>
import axios from 'axios'
import {useProductsStore} from "@/stores/products";
import {startTimer, checkTimer, getSpentTimeCounter} from "@/utilites/commonMethods.js";

export default {
    name: 'ProductSimpleForm',
    props: ["supplierId"],
    data() {
        return {
            tab: 'main',
            productStore: useProductsStore(),
            spent_time: 0,
            form: {
                name: '',
                category: '',
                supplier_id: null,
            },

        }
    },
    created() {
        this.form.supplier_id = this.supplierId || null
    },
    mounted() {
        checkTimer();

        window.addEventListener("trigger-spent-timer", (event) => { // (1)
            this.spent_time = event.detail
        });
    },
    methods: {


        async submitForm() {
            startTimer(10)

            try {
                await this.productStore.create(this.form)
                    .then((resp) => {
                        this.$emit('saved', resp)
                    })


            } catch (error) {
                console.error('Ошибка сохранения продукта:', error)
            }
        }
    }
}
</script>
