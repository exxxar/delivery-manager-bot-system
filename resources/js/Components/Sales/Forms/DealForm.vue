<template>
    <form @submit.prevent="onSubmit">
        <div class="form-floating mb-2">
            <input
                v-model="localForm.sale_date"
                type="date"
                class="form-control"
                id="sale_date"
                required
            />
            <label for="sale_date">Дата</label>
        </div>

        <div class="form-floating mb-2">
            <input
                v-model.number="localForm.quantity"
                type="number"
                class="form-control"
                id="quantity"
                placeholder="Количество"
                required
            />
            <label for="quantity">Количество</label>
        </div>

        <div class="form-floating mb-2">
            <input
                v-model.number="localForm.total_price"
                type="number"
                step="0.01"
                class="form-control"
                id="total_price"
                placeholder="Сумма"
                required
            />
            <label for="total_price">Сумма сделки</label>
        </div>

        <button type="submit" class="btn btn-success w-100 p-3">
            Подтвердить
        </button>
    </form>
</template>

<script>
export default {
    name: "DealForm",

    props: {
        modelValue: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            // локальная копия, чтобы не мутировать проп напрямую
            localForm: { ...this.modelValue }
        };
    },

    watch: {
        // если родитель обновил данные — обновляем локальные
        modelValue: {
            deep: true,
            handler(newVal) {
                this.localForm = { ...newVal };
            }
        },

        // если локальные данные изменились — отправляем наверх
        localForm: {
            deep: true,
            handler(newVal) {
                this.$emit("update:modelValue", newVal);
            }
        }
    },

    methods: {
        onSubmit() {
            this.$emit("submit", this.localForm);
        }
    }
};
</script>
