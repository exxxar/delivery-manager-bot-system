<template>
    <form class="deal-form" @submit.prevent="onSubmit">
        <header class="deal-form__header">
            <div class="deal-form__header-icon"><i class="fa-regular fa-file-lines" aria-hidden="true"></i></div>
            <div>
                <h2 class="deal-form__title">Информация о сделке</h2>
                <p class="deal-form__subtitle">Заполните основные данные для подтверждения сделки</p>
                <p v-if="localForm.id" class="deal-form__deal-id">Номер сделки: <strong>#{{ localForm.id }}</strong></p>
            </div>
        </header>

        <section class="deal-form__section">
            <div class="deal-field">
                <label class="deal-field__label" for="actual_delivery_date">
                    <i class="fa-regular fa-calendar" aria-hidden="true"></i> Дата доставки <span class="required-mark">*</span>
                </label>
                <input
                    id="actual_delivery_date"
                    v-model="localForm.actual_delivery_date"
                    type="date"
                    class="deal-control"
                    required
                />
            </div>

            <label class="deal-toggle">
                <input
                    v-model="localForm.same_sale_delivery_date"
                    type="checkbox"
                    role="switch"
                    class="deal-toggle__input"
                    id="same_sale_delivery_date"
                />
                <span class="deal-toggle__track" aria-hidden="true"><span></span></span>
                <span class="deal-toggle__copy">
                    <strong>Дата доставки и оплаты совпадает</strong>
                    <small>Если даты одинаковые, дату оплаты можно не заполнять</small>
                </span>
            </label>

            <div v-if="!localForm.same_sale_delivery_date" class="deal-field deal-field--highlight">
                <label class="deal-field__label" for="deal_payment_date">
                    <i class="fa-regular fa-calendar-check" aria-hidden="true"></i> Дата оплаты <span class="required-mark">*</span>
                </label>
                <input
                    id="deal_payment_date"
                    v-model="localForm.sale_date"
                    type="date"
                    class="deal-control"
                    required
                />
            </div>

            <div class="deal-form__grid">
                <div class="deal-field">
                    <label class="deal-field__label" for="total_price">
                        <i class="fa-solid fa-coins" aria-hidden="true"></i> Сумма сделки <span class="required-mark">*</span>
                    </label>
                    <div class="deal-control-wrap">
                        <input
                            id="total_price"
                            v-model.number="localForm.total_price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="deal-control"
                            placeholder="0.00"
                            required
                        />
                        <span class="deal-control-suffix" aria-hidden="true">₽</span>
                    </div>
                </div>
                <div class="deal-field">
                    <label class="deal-field__label" for="payment_type">
                        <i class="fa-regular fa-credit-card" aria-hidden="true"></i> Тип оплаты <span class="required-mark">*</span>
                    </label>
                    <select id="payment_type" v-model="localForm.payment_type" class="deal-control">
                        <option :value="'0'">Наличный расчет</option>
                        <option :value="'1'">Безналичный расчет</option>
                    </select>
                </div>
            </div>
        </section>

        <section v-if="localForm.payment_type === '1'" class="receipt-panel">
            <div class="receipt-panel__heading">
                <div class="receipt-panel__title">
                    <span class="receipt-panel__icon"><i class="fa-solid fa-camera" aria-hidden="true"></i></span>
                    <div>
                        <h3>Фотография чека</h3>
                        <p>Прикрепите чек в формате JPG, PNG или PDF</p>
                    </div>
                </div>
                <button
                    v-if="localForm.payment_document_name"
                    type="button"
                    class="receipt-panel__telegram"
                    @click="sendPaymentDocumentToTg"
                >
                    <i class="fa-brands fa-telegram" aria-hidden="true"></i>
                    Отправить в Telegram
                </button>
            </div>

            <div v-if="localForm.payment_document_name" class="receipt-attached">
                <span class="receipt-attached__check"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                <span class="receipt-attached__text">Чек уже прикреплен к сделке</span>
                <span class="receipt-attached__name">{{ localForm.payment_document_name }}</span>
            </div>

            <label class="receipt-upload" for="deal_receipt_files">
                <span class="receipt-upload__icon"><i class="fa-regular fa-file-lines" aria-hidden="true"></i></span>
                <span class="receipt-upload__copy">
                    <strong>Выберите файлы или перетащите сюда</strong>
                    <small>Поддерживаются: JPG, PNG, PDF</small>
                </span>
                <span class="receipt-upload__button"><i class="fa-regular fa-folder-open" aria-hidden="true"></i> Выбрать файлы</span>
                <input
                    id="deal_receipt_files"
                    type="file"
                    multiple
                    accept=".jpg,.jpeg,.png,.pdf"
                    @change="onFileChange"
                    :required="!localForm.payment_document_name && !localForm.receipt_is_lost && !files.length"
                />
            </label>

            <ul v-if="files.length" class="receipt-file-list">
                <li v-for="(file, index) in files" :key="`${file.name}-${file.lastModified}-${index}`" class="receipt-file">
                    <span class="receipt-file__icon"><i class="fa-regular fa-file-lines" aria-hidden="true"></i></span>
                    <span class="receipt-file__name">{{ file.name }}</span>
                    <span class="receipt-file__size">{{ formatFileSize(file.size) }}</span>
                    <button type="button" class="receipt-file__remove" :aria-label="`Удалить файл ${file.name}`" @click="removeFile(index)">
                        <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                    </button>
                </li>
            </ul>

            <label class="deal-toggle deal-toggle--compact">
                <input
                    id="receipt_is_lost"
                    v-model="localForm.receipt_is_lost"
                    type="checkbox"
                    role="switch"
                    class="deal-toggle__input"
                />
                <span class="deal-toggle__track" aria-hidden="true"><span></span></span>
                <span class="deal-toggle__copy">
                    <strong><i class="fa-regular fa-circle-question" aria-hidden="true"></i> Чек был утрачен</strong>
                    <small>Отметьте, если не можете предоставить чек</small>
                </span>
            </label>
        </section>

        <label class="deal-toggle deal-toggle--comment">
            <input
                id="need_add_comment"
                v-model="localForm.need_additional_comment"
                type="checkbox"
                role="switch"
                class="deal-toggle__input"
            />
            <span class="deal-toggle__track" aria-hidden="true"><span></span></span>
            <span class="deal-toggle__copy">
                <strong><i class="fa-regular fa-comment-dots" aria-hidden="true"></i> Дополнительный комментарий к сделке</strong>
                <small>При необходимости добавьте пояснение</small>
            </span>
        </label>

        <div v-if="localForm.need_additional_comment" class="deal-field deal-comment-field">
            <label class="deal-field__label" for="additional_comment">Комментарий <span class="required-mark">*</span></label>
            <textarea
                id="additional_comment"
                v-model="localForm.additional_comment"
                class="deal-control deal-control--textarea"
                placeholder="Введите дополнительную информацию о сделке..."
                rows="4"
                required
            ></textarea>
        </div>

        <section v-if="salesStore.progress > 0" class="upload-progress" aria-live="polite">
            <div class="upload-progress__heading">
                <span><i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i> Загрузка файлов</span>
                <strong>Уже загружено {{ salesStore.progress }}%</strong>
            </div>
            <div class="upload-progress__track" role="progressbar" :aria-valuenow="salesStore.progress" aria-valuemin="0" aria-valuemax="100">
                <div class="upload-progress__bar" :style="{ width: `${salesStore.progress}%` }"></div>
            </div>
        </section>

        <button type="submit" class="deal-submit">
            <i class="fa-solid fa-check" aria-hidden="true"></i>
            Подтвердить
        </button>
    </form>
</template>

<script>
import { useSalesStore } from "@/stores/sales";

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
            localForm: {
                files: [],
                ...this.modelValue,
                files: Array.isArray(this.modelValue.files) ? [...this.modelValue.files] : []
            }
        };
    },

    watch: {
        modelValue: {
            deep: true,
            handler(newValue) {
                const incoming = { ...newValue };
                if (!Array.isArray(incoming.files)) incoming.files = this.files;
                this.localForm = { ...this.localForm, ...incoming };
            }
        },
        localForm: {
            deep: true,
            handler(newValue) {
                this.$emit("update:modelValue", newValue);
            }
        }
    },

    computed: {
        files() {
            return Array.isArray(this.localForm.files) ? this.localForm.files : [];
        }
    },

    methods: {
        async sendPaymentDocumentToTg() {
            try {
                await this.salesStore.sendPaymentDocumentToTg(this.localForm.id);
                if (this.alertStore?.show) {
                    this.alertStore.show("Чек отправлен вам в телеграм бот!");
                }
            } catch (error) {
                console.error("Не удалось отправить чек в Telegram:", error);
            }
        },

        onFileChange(event) {
            const selectedFiles = Array.from(event.target.files || []);
            this.localForm.files = [...this.files, ...selectedFiles];
            // Позволяет повторно выбрать тот же файл после удаления.
            event.target.value = "";
        },

        onSubmit() {
            this.$emit("callback", this.localForm);
        },

        removeFile(index) {
            this.localForm.files.splice(index, 1);
        },

        formatFileSize(bytes) {
            if (!Number.isFinite(bytes) || bytes < 0) return "";
            if (bytes < 1024) return `${bytes} Б`;
            if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} КБ`;
            return `${(bytes / (1024 * 1024)).toFixed(1)} МБ`;
        }
    }
};
</script>

<style scoped>
.deal-form {
    --deal-blue: #1769e8;
    --deal-blue-dark: #1055c7;
    --deal-blue-soft: #eef5ff;
    --deal-ink: #172b4d;
    --deal-muted: #70819b;
    --deal-border: #dce5f0;
    display: flex;
    flex-direction: column;
    gap: 16px;
    width: 100%;
    color: var(--deal-ink);
    font-family: inherit;
}

.deal-form__header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px;
    border: 1px solid #e0ebfb;
    border-radius: 18px;
    background: linear-gradient(135deg, #f0f6ff 0%, #ffffff 100%);
}

.deal-form__header-icon,
.receipt-upload__icon,
.receipt-file__icon {
    display: grid;
    flex: 0 0 auto;
    place-items: center;
    color: var(--deal-blue);
    background: #e3efff;
}

.deal-form__header-icon {
    width: 54px;
    height: 54px;
    border-radius: 15px;
    font-size: 23px;
}

.deal-form__title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 750;
    letter-spacing: -0.02em;
}

.deal-form__subtitle,
.deal-form__deal-id {
    margin: 4px 0 0;
    color: var(--deal-muted);
    font-size: 0.9rem;
}

.deal-form__deal-id strong { color: var(--deal-ink); }
.deal-form__section { display: flex; flex-direction: column; gap: 14px; }
.deal-field { min-width: 0; }
.deal-field__label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 8px;
    font-size: 0.92rem;
    font-weight: 700;
}
.deal-field__label > i { color: var(--deal-blue); font-size: 1rem; }
.required-mark { color: #e53952; }
.deal-control {
    display: block;
    width: 100%;
    min-height: 48px;
    padding: 11px 14px;
    border: 1px solid #cdd8e7;
    border-radius: 11px;
    outline: none;
    color: var(--deal-ink);
    background-color: #fff;
    box-shadow: 0 1px 2px rgb(18 42 76 / 3%);
    font: inherit;
    transition: border-color .18s ease, box-shadow .18s ease;
}
.deal-control:focus { border-color: var(--deal-blue); box-shadow: 0 0 0 3px rgb(23 105 232 / 13%); }
.deal-control::placeholder { color: #91a0b5; }
.deal-control-wrap { position: relative; }
.deal-control-wrap .deal-control { padding-right: 48px; }
.deal-control-suffix {
    position: absolute;
    top: 1px;
    right: 1px;
    display: grid;
    width: 42px;
    height: calc(100% - 2px);
    place-items: center;
    border-radius: 0 10px 10px 0;
    color: #5f718a;
    background: #f3f6fa;
    font-weight: 700;
}
.deal-form__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.deal-field--highlight { padding: 14px; border: 1px dashed #b5d2ff; border-radius: 14px; background: #f7faff; }

.deal-toggle {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
    padding: 14px;
    border: 1px solid transparent;
    border-radius: 14px;
    background: #f3f6fa;
    cursor: pointer;
    transition: background .18s ease, border-color .18s ease;
}
.deal-toggle:hover { border-color: #d9e7fb; background: #eff5fd; }
.deal-toggle__input { position: absolute; width: 1px; height: 1px; opacity: 0; }
.deal-toggle__track {
    position: relative;
    display: block;
    flex: 0 0 42px;
    width: 42px;
    height: 24px;
    border-radius: 99px;
    background: #c4cedb;
    transition: background .2s ease;
}
.deal-toggle__track span {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 3px rgb(0 0 0 / 16%);
    transition: transform .2s ease;
}
.deal-toggle__input:checked + .deal-toggle__track { background: var(--deal-blue); }
.deal-toggle__input:checked + .deal-toggle__track span { transform: translateX(18px); }
.deal-toggle__input:focus-visible + .deal-toggle__track { outline: 3px solid rgb(23 105 232 / 24%); outline-offset: 2px; }
.deal-toggle__copy { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.deal-toggle__copy strong { font-size: .93rem; font-weight: 700; }
.deal-toggle__copy strong i { margin-right: 6px; color: var(--deal-blue); }
.deal-toggle__copy small { color: var(--deal-muted); font-size: .82rem; line-height: 1.4; }

.receipt-panel { display: flex; flex-direction: column; gap: 13px; padding: 16px; border: 1px solid #d9eee9; border-radius: 16px; background: linear-gradient(145deg, #f4fcfa 0%, #fff 100%); }
.receipt-panel__heading, .receipt-panel__title { display: flex; align-items: center; }
.receipt-panel__heading { justify-content: space-between; gap: 12px; }
.receipt-panel__title { gap: 11px; min-width: 0; }
.receipt-panel__icon { display: grid; flex: 0 0 42px; width: 42px; height: 42px; place-items: center; border-radius: 12px; color: var(--deal-blue); background: #e5f0ff; font-size: 19px; }
.receipt-panel__title h3 { margin: 0; font-size: 1rem; font-weight: 750; }
.receipt-panel__title p { margin: 3px 0 0; color: var(--deal-muted); font-size: .82rem; }
.receipt-panel__telegram { display: inline-flex; align-items: center; gap: 7px; padding: 9px 12px; border: 1px solid #cfe2ff; border-radius: 10px; color: var(--deal-blue); background: #fff; font: inherit; font-size: .84rem; font-weight: 700; cursor: pointer; }
.receipt-panel__telegram:hover { background: var(--deal-blue-soft); }
.receipt-attached { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; padding: 10px 12px; border: 1px solid #bfe9d6; border-radius: 11px; color: #18794e; background: #eafaf2; font-size: .84rem; }
.receipt-attached__check { display: grid; width: 22px; height: 22px; place-items: center; border-radius: 50%; color: #fff; background: #24a36a; font-size: .7rem; }
.receipt-attached__text { font-weight: 700; }
.receipt-attached__name { margin-left: auto; color: #4c8068; overflow-wrap: anywhere; }
.receipt-upload { display: flex; align-items: center; gap: 12px; padding: 14px; border: 1px dashed #b8cce6; border-radius: 13px; background: #fff; cursor: pointer; transition: border-color .18s ease, background .18s ease; }
.receipt-upload:hover { border-color: var(--deal-blue); background: #f8fbff; }
.receipt-upload input { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; clip-path: inset(50%); }
.receipt-upload__icon { width: 42px; height: 42px; border-radius: 12px; font-size: 18px; }
.receipt-upload__copy { display: flex; flex: 1; flex-direction: column; gap: 3px; min-width: 0; }
.receipt-upload__copy strong { font-size: .88rem; font-weight: 700; }
.receipt-upload__copy small { color: var(--deal-muted); font-size: .8rem; }
.receipt-upload__button { display: inline-flex; align-items: center; gap: 7px; flex: 0 0 auto; padding: 9px 11px; border-radius: 9px; color: var(--deal-blue); background: var(--deal-blue-soft); font-size: .82rem; font-weight: 700; }
.receipt-file-list { display: flex; flex-direction: column; gap: 7px; margin: 0; padding: 0; list-style: none; }
.receipt-file { display: flex; align-items: center; gap: 10px; min-width: 0; padding: 9px 10px; border: 1px solid var(--deal-border); border-radius: 11px; background: #fff; }
.receipt-file__icon { width: 35px; height: 35px; border-radius: 10px; font-size: 15px; }
.receipt-file__name { min-width: 0; flex: 1; overflow-wrap: anywhere; font-size: .86rem; font-weight: 600; }
.receipt-file__size { flex: 0 0 auto; color: var(--deal-muted); font-size: .78rem; }
.receipt-file__remove { display: grid; flex: 0 0 32px; width: 32px; height: 32px; place-items: center; border: 0; border-radius: 8px; color: #df354c; background: #fff0f2; cursor: pointer; }
.receipt-file__remove:hover { color: #fff; background: #df354c; }
.deal-toggle--compact { padding: 11px; background: #f2f6fa; }
.deal-toggle--compact .deal-toggle__copy strong { font-size: .88rem; }
.deal-toggle--comment { border-color: #e5ebf3; background: #f7f9fc; }
.deal-comment-field { padding: 14px; border: 1px solid var(--deal-border); border-radius: 13px; background: #fff; }
.deal-control--textarea { min-height: 110px; resize: vertical; }

.upload-progress { padding: 13px 15px; border-radius: 13px; background: #edf5ff; }
.upload-progress__heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 9px; font-size: .84rem; }
.upload-progress__heading span { font-weight: 700; }
.upload-progress__heading i { margin-right: 6px; color: var(--deal-blue); }
.upload-progress__heading strong { color: var(--deal-blue); white-space: nowrap; }
.upload-progress__track { height: 9px; overflow: hidden; border-radius: 99px; background: #d6e1ef; }
.upload-progress__bar { height: 100%; border-radius: inherit; background: linear-gradient(90deg, #2b83f6, #075bd8); transition: width .25s ease; }
.deal-submit { display: inline-flex; align-items: center; justify-content: center; gap: 10px; width: 100%; min-height: 52px; padding: 14px 18px; border: 0; border-radius: 12px; color: #fff; background: linear-gradient(105deg, #2479f3, #075bd8); box-shadow: 0 5px 14px rgb(23 105 232 / 20%); font: inherit; font-weight: 750; cursor: pointer; transition: transform .16s ease, box-shadow .16s ease, filter .16s ease; }
.deal-submit:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgb(23 105 232 / 27%); filter: saturate(1.08); }
.deal-submit:active { transform: translateY(0); }

@media (max-width: 640px) {
    .deal-form { gap: 12px; }
    .deal-form__header { align-items: flex-start; padding: 14px; gap: 11px; }
    .deal-form__header-icon { width: 44px; height: 44px; border-radius: 12px; font-size: 19px; }
    .deal-form__title { font-size: 1.08rem; }
    .deal-form__subtitle { font-size: .82rem; }
    .deal-form__grid { grid-template-columns: 1fr; gap: 13px; }
    .receipt-panel { padding: 12px; }
    .receipt-panel__heading { align-items: flex-start; flex-direction: column; }
    .receipt-panel__telegram { width: 100%; justify-content: center; }
    .receipt-upload { flex-wrap: wrap; padding: 11px; }
    .receipt-upload__copy { flex-basis: calc(100% - 58px); }
    .receipt-upload__button { width: 100%; justify-content: center; }
    .receipt-attached__name { width: 100%; margin-left: 30px; }
    .deal-toggle { gap: 10px; padding: 12px; }
    .deal-toggle__copy strong { font-size: .87rem; }
    .deal-toggle__copy small { font-size: .78rem; }
    .upload-progress__heading { align-items: flex-start; flex-direction: column; gap: 5px; }
}

@media (prefers-reduced-motion: reduce) {
    .deal-form *, .deal-form *::before, .deal-form *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; }
}
</style>
