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
                class="form-check-input" type="checkbox" role="switch" id="same_sale_delivery_date">
            <label class="form-check-label" for="same_sale_delivery_date">Дата доставки и оплаты совпадает
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
                <template v-if="localForm.payment_document_name">
                    <span
                        @click="sendPaymentDocumentToTg"
                        style="cursor:pointer;text-align:left;"
                        class="mb-2 w-100 badge bg-success text-decoration-underline">
                        <i style="margin-right:5px;"
                           class="fa-brands fa-telegram"></i> Чек прикреплен к сделке.
                    </span>
                </template>

                <div class="form-floating mb-2">
                    <template v-if="localForm.payment_document_name">
                        <input
                            type="file"
                            multiple
                            class="form-control"
                            @change="onFileChange"
                            accept=".jpg,.png,.pdf"
                        />
                    </template>
                    <template v-else>
                        <input
                            type="file"
                            multiple
                            class="form-control"
                            @change="onFileChange"
                            accept=".jpg,.png,.pdf"
                            :required="!localForm.receipt_is_lost"
                        />
                    </template>

                    <label>Прикрепить</label>
                </div>

                <!-- Список выбранных файлов -->
                <ul
                    v-if="localForm.files.length"
                    class="list-group mb-2"
                >
                    <li
                        v-for="(file, index) in localForm.files"
                        :key="index"
                        class="list-group-item d-flex justify-content-between align-items-center"
                    >
                        <span class="text-break small lh-1">{{ file.name }}</span>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger"
                            @click="removeFile(index)"
                        >
                            ✕
                        </button>
                    </li>
                </ul>

                <div class="form-check form-switch mb-2">
                    <input
                        v-model="localForm.receipt_is_lost"
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="need_automatic_naming"
                    >
                    <label class="form-check-label" for="need_automatic_naming">
                        Чек был утрачен
                    </label>
                </div>


            </template>

        <div
            class="form-check form-switch mb-2">
            <input
                v-model="localForm.need_additional_comment"
                class="form-check-input" type="checkbox" role="switch" id="need_add_comment">
            <label class="form-check-label" for="need_add_comment">Дополнительный комментарий к сделке
            </label>
        </div>

        <template v-if="localForm.need_additional_comment">
            <div class="form-floating mb-2">
                <textarea v-model="localForm.additional_comment" class="form-control" id="additional_comment" placeholder="Дополнительный комментарий"
                          style="height: 120px" required></textarea>
                <label for="additional_comment">Комментарий</label>
            </div>
        </template>

        <template v-if="salesStore.progress > 0">
            <div
                class="w-100 mb-2"
                style=" background: #eee; height: 10px; border-radius: 4px;">
                <div
                    :style="{ width: salesStore.progress + '%', background: '#42b883', height: '10px', borderRadius: '4px' }"
                ></div>
            </div>
            <p class="fst-italic">Уже загружено <span class="fw-bold">{{ salesStore.progress }}%</span></p>
        </template>

        <button type="submit" class="btn btn-success w-100 p-3">
            Подтвердить
        </button>
    </form>
</template>

<script>
import {useSalesStore} from "@/stores/sales";

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
            salesStore: useSalesStore(),
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
        async sendPaymentDocumentToTg() {
            await this.salesStore.sendPaymentDocumentToTg(this.localForm.id).then(() => {
                this.alertStore.show("Чек отправлен вам в телеграм бот!");
            })


        },
        onFileChange(e) {
            const files = e.target.files

            this.localForm.files = Array.from(files)
        },
        onSubmit() {
            this.$emit("callback", this.localForm);
        },
        removeFile(index) {
            this.localForm.files.splice(index, 1)
        }
    }
};
</script>
