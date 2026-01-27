<template>
    <form @submit.prevent="onSubmit">

        <div class="form-floating mb-2">
            <input
                v-model="localForm.actual_delivery_date"
                type="date"
                class="form-control"
                id="sale_date"
                required
            />
            <label for="sale_date">Дата доставки</label>
        </div>

        <div
            class="form-check form-switch mb-2">
            <input
                v-model="localForm.same_sale_delivery_date"
                class="form-check-input" type="checkbox" role="switch" id="need_automatic_naming">
            <label class="form-check-label" for="need_automatic_naming">Дата доставки и оплаты совпадает
            </label>
        </div>

        <template v-if="!localForm.same_sale_delivery_date">
            <div class="form-floating mb-2">
                <input
                    v-model="localForm.sale_date"
                    type="date"
                    class="form-control"
                    id="sale_date"
                    required
                />
                <label for="sale_date">Дата оплаты</label>
            </div>
        </template>

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


            <div class="form-floating mb-2">
                <select class="form-select"
                        v-model="localForm.payment_type"
                        id="payment-type" aria-label="Floating label select example">
                    <option :value="'0'">Наличный расчет</option>
                    <option :value="'1'">Безналичный расчет</option>
                </select>
                <label for="payment-type">Тип оплаты</label>
            </div>

            <template v-if="localForm.payment_type==='1'">
                <h6>Фотография чека</h6>
                <div class="form-floating mb-2">

                    <input
                        type="file"
                        class="form-control"
                        @change="onFileChange"
                        accept=".jpg,.png,.pdf"
                        :required="!localForm.receipt_is_lost"
                    />
                    <label for="payment-type">Прикрепить</label>
                </div>
                <div
                    class="form-check form-switch mb-2">
                    <input
                        v-model="localForm.receipt_is_lost"
                        class="form-check-input" type="checkbox" role="switch" id="need_automatic_naming">
                    <label class="form-check-label" for="need_automatic_naming">Чек был утрачен
                    </label>
                </div>

            </template>


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
            localForm: {...this.modelValue}
        };
    },

    watch: {
        localForm: {
            deep: true,
            handler(newVal) {
                this.$emit("update:modelValue", newVal);
            }
        }
    },

    mounted() {
        this.localForm = {...this.modelValue};
    },
    methods: {
        onFileChange(e) {
            this.localForm.file = e.target.files[0]
        },
        onSubmit() {
            this.$emit("callback", this.localForm);
        }
    }
};
</script>
