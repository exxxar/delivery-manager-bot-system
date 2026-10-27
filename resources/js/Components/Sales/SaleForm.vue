<script setup>
const today = new Date().toISOString().split('T')[0]
</script>

<template>

    <form class="sale-form" @submit.prevent="submitForm">

        <!-- Индикатор офлайн-очереди -->
        <div v-if="offlineCount > 0" class="alert alert-warning d-flex align-items-center mb-3 py-2">
            <i class="fa-solid fa-cloud-arrow-up me-2"></i>
            <span class="flex-grow-1 small">
                В очереди <strong>{{ offlineCount }}</strong>
                {{ pluralize(offlineCount, ['заявка', 'заявки', 'заявок']) }},
                ожидающих отправки
            </span>
            <button
                type="button"
                class="btn btn-sm btn-warning ms-2"
                :disabled="!isOnline || isSyncing"
                @click="syncQueue"
            >
                <i class="fa-solid fa-rotate" :class="{'fa-spin': isSyncing}"></i>
                Отправить
            </button>
        </div>

        <template v-if="tab==='main'">
            <header class="sale-form__header">
                <div class="sale-form__header-icon">
                    <i class="fa-solid fa-file-circle-plus"></i>
                </div>
                <div class="sale-form__header-copy">
                    <span class="sale-form__eyebrow">{{ isEdit ? 'Редактирование' : 'Новая операция' }}</span>
                    <h2>{{ isEdit ? 'Изменение сделки' : 'Создание сделки' }}</h2>
                    <p>Заполните данные, чтобы сохранить заявку в системе</p>
                </div>
            </header>

            <div class="sale-form__section">
                <div class="form-check form-switch mb-2 sale-form__switch">
                    <input
                        v-model="form.need_automatic_naming"
                        class="form-check-input" type="checkbox" role="switch" id="need_automatic_naming">
                    <label class="form-check-label" for="need_automatic_naming">
                        Автоматическое название и описание
                        <span class="fw-bold text-primary" v-if="form.need_automatic_naming">включено</span>
                        <span class="fw-bold text-primary" v-else>выключено</span>
                    </label>
                </div>

                <template v-if="!form.need_automatic_naming">
                    <div class="form-floating mb-2">
                        <input v-model="form.title" type="text" class="form-control" id="title"
                               placeholder="Название" required>
                        <label for="title">Название задания</label>
                    </div>

                    <div class="form-floating mb-2">
                    <textarea v-model="form.description" class="form-control" id="description"
                              placeholder="Описание" style="height: 120px" required></textarea>
                        <label for="description">Описание задания</label>
                    </div>
                </template>

                <!-- Поставщик -->
                <div class="input-group mb-2">
                    <div class="form-floating flex-grow-1">
                        <input required type="text" class="form-control" id="supplier"
                               :value="supplierName" placeholder="Поставщик" readonly>
                        <label for="supplier">Поставщик</label>
                    </div>
                    <button type="button" class="btn btn-outline-light text-primary find-btn"
                            @click="tab='supplier'">Выбрать</button>
                </div>

                <!-- Продукт -->
                <div class="input-group mb-2">
                    <div class="form-floating flex-grow-1">
                        <input type="text" required class="form-control" id="product"
                               :value="productName" placeholder="Продукт" readonly>
                        <label for="product">Продукт</label>
                    </div>
                    <button type="button" :disabled="form.supplier_id == null"
                            class="btn btn-outline-light text-primary find-btn"
                            @click="tab='product'">Выбрать</button>
                </div>

                <template v-if="form.supplier_id && form.product_id">
                    <div class="form-floating mb-2">
                        <input v-model="form.total_price" required type="number" step="0.01"
                               class="form-control" id="total_price" placeholder="Сумма">
                        <label for="total_price">Сумма сделки</label>
                    </div>
                </template>

                <div class="form-floating mb-2">
                    <select class="form-select" v-model="form.payment_type" id="payment-type"
                            aria-label="Floating label select example">
                        <option :value="'0'">Наличный расчет</option>
                        <option :value="'1'">Безналичный расчет</option>
                    </select>
                    <label for="payment-type">Тип оплаты</label>
                </div>

                <template v-if="form.payment_type==='1'">
                    <h6>Фотография чека</h6>

                    <template v-if="form.payment_document_name">
                    <span @click="sendPaymentDocumentToTg"
                          style="cursor:pointer;text-align:left;"
                          class="mb-2 w-100 badge bg-success text-decoration-underline">
                        <i style="margin-right:5px;" class="fa-brands fa-telegram"></i>
                        Чек прикреплен к сделке.
                    </span>
                    </template>

                    <div class="form-floating mb-2">
                        <input type="file" class="form-control" @change="onFileChange"
                               accept=".jpg,.png,.pdf"/>
                        <label for="payment-type">
                            Прикрепить <span class="text-primary fw-bold">(не обязательно)</span>
                        </label>
                    </div>
                </template>

                <div class="form-floating mb-2" v-if="isEdit">
                    <select v-model="form.status" class="form-select" id="status" required>
                        <option value="pending">Ожидает</option>
                        <option value="assigned">Назначено</option>
                        <option value="delivered">Доставляется</option>
                        <option value="completed">Завершено</option>
                        <option value="rejected">Отклонено</option>
                    </select>
                    <label for="status">Статус</label>
                </div>

                <CustomDatePicker
                    v-model="form.actual_delivery_date"
                    input-id="actual_delivery_date"
                    label="Фактическая дата доставки"
                    placeholder="Выберите дату"
                    :required="true"
                    class="mb-2"
                />

                <template v-if="user?.role>=3">
                    <p class="alert alert-info mb-2">Назначение ответственного по данной задаче</p>

                    <div class="input-group mb-2">
                        <div class="form-floating flex-grow-1">
                            <input type="text" class="form-control" id="agent" :value="agentName"
                                   placeholder="администратор" readonly>
                            <label for="agent">Администратор</label>
                        </div>
                        <button type="button" class="btn btn-outline-light text-primary find-btn"
                                @click="tab='agent'">Выбрать</button>
                    </div>
                </template>

                <template v-if="!isEdit">
                    <div class="form-check form-switch mb-2">
                        <input v-model="form.is_already_delivered" class="form-check-input"
                               type="checkbox" role="switch" id="is_already_delivered">
                        <label class="form-check-label" for="is_already_delivered">
                            Товар уже доставлен
                        </label>
                    </div>
                </template>

                <!-- Кнопка отправки -->
                <button :disabled="spent_time > 0 || salesStore.loading"

                        type="submit" class="btn btn-primary w-100 p-3">
                    <span v-if="spent_time > 0">{{ spent_time }} сек.</span>
                    <span v-else-if="!isOnline">
                    <i class="fa-solid fa-wifi me-1"></i>
                    Сохранить офлайн
                </span>
                    <span v-else>
                    {{ isEdit ? 'Сохранить изменения' : 'Создать задание' }}
                </span>
                </button>
            </div>
        </template>

        <template v-if="tab==='agent'">
            <button @click="tab='main'" class="btn btn-light text-secondary mb-3"
                    style="position: sticky; top:0; z-index: 100;">Назад</button>
            <AgentList :for-select="true" @select="selectAgent"/>
        </template>

        <template v-if="tab==='customer'">
            <button @click="tab='main'" class="btn btn-light text-secondary mb-3"
                    style="position: sticky; top:0; z-index: 100;">Назад</button>
            <CustomerList :for-select="true" @select="selectCustomer"/>
        </template>

        <template v-if="tab==='supplier'">
            <button @click="tab='main'" class="btn btn-light text-secondary mb-3"
                    style="position: sticky; top:0; z-index: 100;">Назад</button>
            <SupplierListGroup :for-select="true" @select="selectSupplier"/>
        </template>

        <template v-if="tab==='product'">
            <button @click="tab='main'" class="btn btn-light text-secondary mb-3"
                    style="position: sticky; top:0; z-index: 100;">Назад</button>
            <ProductList :for-select="true" :filters="product_filters" @select="selectProduct"/>
        </template>
    </form>

</template>

<script>
import AgentList from '../Agents/AgentList.vue'
import CustomerList from '../Customers/CustomerList.vue'
import SupplierListGroup from '../Suppliers/SupplierList.vue'
import ProductList from '../Products/ProductList.vue'
import { useUsersStore } from "@/stores/users";
import { useSalesStore } from "@/stores/sales";
import { startTimer, checkTimer } from "@/utilites/commonMethods.js";
import { useAlertStore } from "@/stores/utillites/useAlertStore";
import CustomDatePicker from '@/Components/UI/CustomDatePicker.vue'
import {
    addToQueue,
    getQueue,
    removeFromQueue,
    incrementAttempts,
    base64ToFile,
    isOnline
} from "@/utilites/offlineQueue.js";

export default {
    name: 'SaleForm',
    components: { AgentList, CustomerList, SupplierListGroup, ProductList, CustomDatePicker },
    props: {
        initialData: {
            type: Object,
            default: null
        }
    },
    computed: {
        user() {
            return this.userStore.self || null
        },
        productName() {
            return this.product?.name || ''
        },
        isOnline() {
            return this.onlineStatus
        }
    },
    data() {
        return {
            tab: 'main',
            spent_time: 0,
            onlineStatus: navigator.onLine,
            offlineCount: 0,
            isSyncing: false,
            alertStore: useAlertStore(),
            salesStore: useSalesStore(),
            userStore: useUsersStore(),
            file: null,
            product_filters: { supplier_id: null },
            form: {
                title: '',
                description: '',
                status: 'pending',
                due_date: '',
                actual_delivery_date: '',
                sale_date: '',
                quantity: 1,
                total_price: 0,
                agent_id: null,
                customer_id: null,
                supplier_id: null,
                product_id: null,
                payment_type: '0',
                payment_document_name: null,
                need_automatic_naming: true,
                receipt_is_lost: false,
                is_already_delivered: false
            },
            agentName: '',
            customerName: '',
            supplierName: '',
            product: null,
            isEdit: false
        }
    },
    created() {
        if (this.initialData) {
            this.form = { ...this.initialData }
            this.form.need_automatic_naming = true
            this.form.payment_type = "" + this.form.payment_type
            this.isEdit = true
            this.agentName = this.initialData.agent?.name || ''
            this.customerName = this.initialData.customer?.name || ''
            this.supplierName = this.initialData.supplier?.name || ''
            this.product = this.initialData.product || null
        }

        this.updateOfflineCount();
    },
    mounted() {
        checkTimer();

        window.addEventListener("trigger-spent-timer", (event) => {
            this.spent_time = event.detail
        });

        // Подписка на изменения сети
        window.addEventListener('online', this.handleOnline);
        window.addEventListener('offline', this.handleOffline);
        window.addEventListener('offline-queue-changed', this.updateOfflineCount);
    },
    beforeUnmount() {
        window.removeEventListener('online', this.handleOnline);
        window.removeEventListener('offline', this.handleOffline);
        window.removeEventListener('offline-queue-changed', this.updateOfflineCount);
    },
    methods: {
        pluralize(n, forms) {
            const abs = Math.abs(n) % 100;
            const n1 = abs % 10;
            if (abs > 10 && abs < 20) return forms[2];
            if (n1 > 1 && n1 < 5) return forms[1];
            if (n1 === 1) return forms[0];
            return forms[2];
        },

        updateOfflineCount() {
            this.offlineCount = getQueue().length;
        },

        handleOnline() {
            this.onlineStatus = true;
            // При появлении сети — автоматически синхронизируем
            this.syncQueue();
        },

        handleOffline() {
            this.onlineStatus = false;
        },

        async sendPaymentDocumentToTg() {
            await this.salesStore.sendPaymentDocumentToTg(this.form.id).then(() => {
                this.alertStore.show("Чек отправлен вам в телеграм бот!");
            })
        },

        onFileChange(e) {
            this.file = e.target.files[0]
        },

        selectAgent(agent) {
            this.form.agent_id = agent.id
            this.agentName = agent.name
            this.tab = 'main'
        },
        selectCustomer(customer) {
            this.form.customer_id = customer.id
            this.customerName = customer.name
            this.tab = 'main'
        },
        selectSupplier(supplier) {
            const oldSupplierId = this.form.supplier_id
            this.form.supplier_id = null
            this.supplierName = null
            this.product_filters.supplier_id = null

            if (oldSupplierId !== supplier.id) {
                this.form.product_id = null
                this.product = null
            }

            this.$nextTick(() => {
                this.form.supplier_id = supplier.id
                this.supplierName = supplier.name
                this.tab = 'main'
                this.product_filters.supplier_id = supplier.id
            })
        },
        selectProduct(product) {
            this.form.product_id = product.id
            this.product = product
            this.tab = 'main'
            this.form.quantity = 1
        },

        async submitForm() {
            // Если нет сети — сохраняем в офлайн-очередь
            if (!this.onlineStatus) {
                return await this.saveOffline();
            }

            // Штатная отправка
            this.alertStore.show("Началось добавление заявки");
            startTimer(10)

            try {
                this.isEdit
                    ? await this.salesStore.update(this.form.id, this.form, this.file)
                    : await this.salesStore.create(this.form, this.file)

                this.$emit('saved')
                this.resetForm();
            } catch (e) {
                console.error('Ошибка отправки:', e);
                this.alertStore.show(
                    "Ошибка отправки. Заявка сохранена локально и будет отправлена автоматически.",
                    'warning'
                );
                // Если не удалось отправить онлайн — кладём в очередь
                await this.saveOffline();
            }
        },

        async saveOffline() {
            try {
                await addToQueue(this.form, this.file, this.isEdit);
                this.updateOfflineCount();
                this.alertStore.show(
                    "Нет интернета. Заявка сохранена и будет отправлена автоматически при восстановлении связи.",
                    'warning'
                );
                this.resetForm();
                this.$emit('saved');
            } catch (e) {
                console.error('Ошибка сохранения в очередь:', e);
                this.alertStore.show("Не удалось сохранить заявку локально", 'error');
            }
        },

        resetForm() {
            this.file = null;
            if (!this.isEdit) {
                this.form = {
                    title: '', description: '', status: 'pending',
                    due_date: '', actual_delivery_date: '', sale_date: '',
                    quantity: 1, total_price: 0, agent_id: null,
                    customer_id: null, supplier_id: null, product_id: null,
                    payment_type: '0', payment_document_name: null,
                    need_automatic_naming: true, receipt_is_lost: false,
                    is_already_delivered: false
                };
                this.agentName = '';
                this.customerName = '';
                this.supplierName = '';
                this.product = null;
            }
        },

        async syncQueue() {
            if (this.isSyncing || !this.onlineStatus) return;

            const queue = getQueue();
            if (queue.length === 0) return;

            this.isSyncing = true;
            this.alertStore.show(
                `Синхронизация: отправляем ${queue.length} ${this.pluralize(queue.length, ['заявку', 'заявки', 'заявок'])}...`
            );

            const MAX_ATTEMPTS = 3;

            for (const entry of queue) {
                if (!this.onlineStatus) {
                    this.alertStore.show("Связь пропала. Синхронизация приостановлена.", 'warning');
                    break;
                }

                // Превышено количество попыток — удаляем с предупреждением
                if (entry.attempts >= MAX_ATTEMPTS) {
                    console.warn(`Заявка ${entry.id} превысила лимит попыток, удаляем`);
                    removeFromQueue(entry.id);
                    this.updateOfflineCount();
                    continue;
                }

                try {
                    const file = entry.file
                        ? base64ToFile(entry.file, entry.fileName, entry.fileType)
                        : null;

                    entry.isEdit
                        ? await this.salesStore.update(entry.formData.id, entry.formData, file)
                        : await this.salesStore.create(entry.formData, file);

                    // Успех — удаляем из очереди
                    removeFromQueue(entry.id);
                    this.updateOfflineCount();
                } catch (e) {
                    console.error(`Ошибка синхронизации ${entry.id}:`, e);
                    incrementAttempts(entry.id);

                    // Если это ошибка авторизации (401) — не имеет смысла повторять
                    if (e?.response?.status === 401) {
                        removeFromQueue(entry.id);
                        this.updateOfflineCount();
                    }
                }
            }

            this.isSyncing = false;
            this.updateOfflineCount();

            if (getQueue().length === 0) {
                this.alertStore.show("Все офлайн-заявки успешно отправлены!");
            }
        },
    }
}
</script>

<style>
.sale-form {
    --mp-blue: #2457d6;
    --mp-blue-dark: #173b91;
    --mp-red: #df3548;
    --mp-text: #17233b;
    --mp-muted: #778399;
    --mp-border: #e5eaf2;
    color: var(--mp-text);
    width: 100%;
    font-size: 14px;
}
.sale-form__header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px 22px;
    margin-bottom: 18px;
    border: 1px solid var(--mp-border);
    border-radius: 18px;
    background: linear-gradient(120deg, #fff 0%, #f5f8ff 100%);
}
.sale-form__header-icon {
    display: grid;
    place-items: center;
    flex: 0 0 52px;
    width: 52px;
    height: 52px;
    color: #fff;
    border-radius: 15px;
    background: linear-gradient(145deg, var(--mp-blue), var(--mp-blue-dark));
    box-shadow: 0 7px 16px rgba(36,87,214,.2);
    font-size: 21px;
}
.sale-form__eyebrow {
    display: block;
    margin-bottom: 3px;
    color: var(--mp-blue);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.sale-form__header h2 {
    margin: 0;
    color: var(--mp-text);
    font-size: 21px;
    font-weight: 800;
    letter-spacing: -.025em;
}
.sale-form__header p {
    margin: 5px 0 0;
    color: var(--mp-muted);
    font-size: 12px;
}
.sale-form__section {
    padding: 20px;
    border: 1px solid var(--mp-border);
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 8px 26px rgba(23,35,59,.045);
}
.sale-form .form-floating,
.sale-form .input-group {
    //margin-bottom: 14px !important;
}
.sale-form .form-control,
.sale-form .form-select {
    min-height: 48px;
    border: 1px solid #dce3ef;
    border-radius: 11px;
    color: var(--mp-text);
    background-color: #fff;
    box-shadow: none;
    font-size: 14px;
    transition: border-color .18s ease, box-shadow .18s ease;
}
.sale-form textarea.form-control {
    min-height: 120px;
}
.sale-form .form-control:focus,
.sale-form .form-select:focus {
    border-color: #8ba9f4;
    box-shadow: 0 0 0 3px rgba(36,87,214,.1);
}
.sale-form .form-floating > label {
    color: var(--mp-muted);
    font-size: 13px;
}
.sale-form .input-group .form-floating {
    min-width: 0;
}
.sale-form .input-group .form-control {
    border-radius: 11px 0 0 11px;
}
.sale-form .find-btn {
    padding: 0 17px;
    border: 1px solid #dce3ef !important;
    border-radius: 0 11px 11px 0 !important;
    background: #f5f8ff !important;
    color: var(--mp-blue) !important;
    font-weight: 700;
}
.sale-form .find-btn:hover:not(:disabled) {
    background: #eaf0ff !important;
}
.sale-form .find-btn:disabled {
    opacity: .45;
}
.sale-form .form-check {
    padding: 12px 14px 12px 2.7em;
    border: 1px solid var(--mp-border);
    border-radius: 11px;
    background: #f8faff;
}
.sale-form .form-check-input {
    cursor: pointer;
}
.sale-form .form-check-input:checked {
    border-color: var(--mp-blue);
    background-color: var(--mp-blue);
}
.sale-form .form-check-label {
    color: var(--mp-text);
    font-size: 13px;
    font-weight: 600;
}
.sale-form .alert {
    border: 1px solid transparent;
    border-radius: 12px;
    font-size: 13px;
}
.sale-form .alert-info {
    color: #21499d;
    border-color: #dce7ff;
    background: #f1f5ff;
}
.sale-form .alert-warning {
    border-color: #f5dfaa;
}
.sale-form .badge {
    padding: 7px 10px;
    border-radius: 7px;
    font-weight: 700;
}
.sale-form .bg-success {
    background-color: #e5f7ed !important;
    color: #18794e !important;
}
.sale-form .bg-warning {
    background-color: #fff3d6 !important;
    color: #8b5b00 !important;
}
.sale-form .btn-primary {
    min-height: 50px;
    border: 0;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--mp-blue), var(--mp-blue-dark));
    box-shadow: 0 7px 15px rgba(36,87,214,.18);
    font-size: 14px;
    font-weight: 800;
    transition: transform .18s ease, box-shadow .18s ease;
}
.sale-form .btn-primary:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 10px 20px rgba(36,87,214,.24);
}
.sale-form .btn-primary:disabled {
    opacity: .65;
    box-shadow: none;
}
.sale-form > form,
.sale-form .btn-light {
    border-radius: 10px;
}
.sale-form > button.btn-light,
.sale-form button.btn-light {
    border: 1px solid var(--mp-border);
    background: #fff;
    color: var(--mp-blue) !important;
    font-weight: 700;
}
.sale-form h6 {
    margin: 14px 0 9px;
    color: var(--mp-text);
    font-size: 13px;
    font-weight: 800;
}
.sale-form .text-primary {
    color: var(--mp-blue) !important;
}
.sale-form .btn-submitting {
    position: relative;
    pointer-events: none;
    opacity: .7;
}
.sale-form .btn-submitting::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 16px;
    height: 16px;
    margin: -8px 0 0 -8px;
    border: 2px solid #fff;
    border-top-color: transparent;
    border-radius: 50%;
    animation: sale-form-spin .8s linear infinite;
}
@keyframes sale-form-spin {
    to { transform: rotate(360deg); }
}
@media (max-width: 575.98px) {
    .sale-form__header {
        gap: 12px;
        padding: 16px;
        border-radius: 14px;
    }
    .sale-form__header-icon {
        flex-basis: 44px;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        font-size: 18px;
    }
    .sale-form__header h2 {
        font-size: 18px;
    }
    .sale-form__header p {
        font-size: 11px;
    }
    .sale-form__section {
        padding: 14px;
        border-radius: 14px;
    }
    .sale-form .find-btn {
        padding: 0 10px;
        font-size: 12px;
    }
}
</style>
