<template>
    <form @submit.prevent="onSubmit">
        <p class="mb-2 small">Номер сделки: <span class="fw-bold">#{{localForm.id || 'Номер сделки не найден'}}</span></p>
        <div class="form-floating mb-2">
            <select
                class="form-select"
                v-model="localForm.payment_type"
                id="payment-type"
                aria-label="Floating label select example"
            >
                <option :value="'0'">Наличный расчет</option>
                <option :value="'1'">Безналичный расчет</option>
            </select>
            <label for="payment-type">Тип оплаты</label>
        </div>

        <template v-if="localForm.payment_type === '1'">
            <h6>Фотография чека</h6>

            <div class="form-floating mb-2">
                <input
                    type="file"
                    class="form-control"
                    @change="handleFile"
                    accept=".jpg,.png,.pdf"
                    :required="!localForm.receipt_is_lost"
                />
                <label>Прикрепить</label>
            </div>

            <div class="form-check form-switch mb-2">
                <input
                    v-model="localForm.receipt_is_lost"
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    id="need_automatic_naming"
                />
                <label class="form-check-label" for="need_automatic_naming">
                    Чек был утрачен
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
    name: "PaymentConfirmForm",

    props: {
        modelValue: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            localForm: { ...this.modelValue }
        };
    },

    watch: {
        modelValue: {
            deep: true,
            handler(newVal) {
                this.localForm = { ...newVal };
            }
        },
        localForm: {
            deep: true,
            handler(newVal) {
                this.$emit("update:modelValue", newVal);
            }
        }
    },

    methods: {
        handleFile(event) {
                this.localForm.file = event.target.files[0]

        },

        onSubmit() {
            this.$emit("submit", this.localForm);
        }
    }
};
</script>
