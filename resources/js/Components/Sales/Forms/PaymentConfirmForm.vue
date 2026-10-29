<template>
    <form class="payment-confirm-form" @submit.prevent="onSubmit">
        <div class="payment-form-header">
            <div class="payment-form-icon" aria-hidden="true">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="payment-form-heading">
                <p class="payment-form-eyebrow">Подтверждение сделки</p>
                <h5 class="payment-form-title">Подтверждение оплаты</h5>
                <p class="payment-form-subtitle">Укажите способ оплаты и приложите чек, если он доступен.</p>
            </div>
        </div>

        <div class="deal-number">
            <span class="deal-number-label"><i class="fa-solid fa-hashtag"></i> Номер сделки</span>
            <strong>{{ localForm.id || 'Номер сделки не найден' }}</strong>
        </div>

        <div class="form-section">
            <label class="field-label" for="payment-type">
                <i class="fa-solid fa-wallet"></i>
                Тип оплаты
            </label>
            <div class="select-wrap">
                <select
                    id="payment-type"
                    v-model="localForm.payment_type"
                    class="form-select payment-select"
                    aria-label="Тип оплаты"
                >
                    <option :value="'0'">Наличный расчёт</option>
                    <option :value="'1'">Безналичный расчёт</option>
                </select>
                <i class="fa-solid fa-chevron-down select-chevron" aria-hidden="true"></i>
            </div>
        </div>

        <transition name="payment-fields">
            <div v-if="localForm.payment_type === '1'" class="receipt-section">
                <div class="section-heading">
                    <div class="section-heading-icon"><i class="fa-solid fa-file-invoice"></i></div>
                    <div>
                        <h6>Фотография чека</h6>
                        <p>Прикрепите изображение или PDF-файл</p>
                    </div>
                </div>

                <label class="file-upload" for="payment-receipt">
                    <input
                        id="payment-receipt"
                        type="file"
                        class="file-input"
                        accept=".jpg,.jpeg,.png,.pdf"
                        :required="!localForm.receipt_is_lost"
                        @change="handleFile"
                    />
                    <span class="file-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                    <span class="file-upload-copy">
            <strong>{{ selectedFileName || 'Выберите файл чека' }}</strong>
            <small>JPG, PNG или PDF</small>
          </span>
                    <span class="file-upload-action">Обзор</span>
                </label>

                <label class="receipt-lost-option" for="receipt-is-lost">
          <span class="receipt-lost-check">
            <input
                id="receipt-is-lost"
                v-model="localForm.receipt_is_lost"
                class="form-check-input"
                type="checkbox"
                role="switch"
            />
          </span>
                    <span class="receipt-lost-copy">
            <strong>Чек был утрачен</strong>
            <small>Если чек отсутствует, отметьте этот пункт</small>
          </span>
                </label>
            </div>
        </transition>

        <button type="submit" class="confirm-button">
            <i class="fa-solid fa-check-circle" aria-hidden="true"></i>
            <span>Подтвердить оплату</span>
            <i class="fa-solid fa-arrow-right confirm-arrow" aria-hidden="true"></i>
        </button>
        <p class="form-footnote"><i class="fa-solid fa-lock" aria-hidden="true"></i> Проверьте данные перед подтверждением.</p>
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
            localForm: { ...this.modelValue },
            selectedFileName: ""
        };
    },

    watch: {
        modelValue: {
            deep: true,
            handler(newVal) {
                this.localForm = { ...newVal };
                if (!newVal.file) this.selectedFileName = "";
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
            const file = event.target.files && event.target.files[0];
            this.localForm.file = file || null;
            this.selectedFileName = file ? file.name : "";
        },

        onSubmit() {
            this.$emit("submit", this.localForm);
        }
    }
};
</script>

<style scoped>
.payment-confirm-form {
    --payment-blue: #2457d6;
    --payment-blue-dark: #1945b2;
    --payment-ink: #17233d;
    --payment-muted: #77839a;
    --payment-border: #e1e7f0;
    width: 100%;
    padding: clamp(18px, 4vw, 28px);
    color: var(--payment-ink);
    background: #fff;
    border: 1px solid #e9edf4;
    border-radius: 22px;
    box-shadow: 0 14px 38px rgba(25, 45, 85, 0.08);
}

.payment-form-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 22px;
}

.payment-form-icon,
.section-heading-icon {
    display: grid;
    flex: 0 0 auto;
    place-items: center;
    color: var(--payment-blue);
    background: #edf3ff;
}

.payment-form-icon {
    width: 50px;
    height: 50px;
    border-radius: 16px;
    font-size: 21px;
}

.payment-form-heading { min-width: 0; }
.payment-form-eyebrow {
    margin: 0 0 4px;
    color: var(--payment-blue);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .09em;
    text-transform: uppercase;
}
.payment-form-title {
    margin: 0;
    color: var(--payment-ink);
    font-size: clamp(18px, 3vw, 22px);
    font-weight: 800;
    letter-spacing: -.025em;
}
.payment-form-subtitle {
    margin: 6px 0 0;
    color: var(--payment-muted);
    font-size: 13px;
    line-height: 1.5;
}

.deal-number {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 23px;
    padding: 13px 15px;
    background: #f6f8fc;
    border: 1px solid #e9edf5;
    border-radius: 13px;
}
.deal-number-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #68758c;
    font-size: 13px;
    font-weight: 600;
}
.deal-number-label i { color: var(--payment-blue); }
.deal-number strong {
    color: var(--payment-ink);
    font-size: 14px;
    font-weight: 800;
    text-align: right;
    overflow-wrap: anywhere;
}

.form-section { margin-bottom: 22px; }
.field-label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 9px;
    color: #35425a;
    font-size: 13px;
    font-weight: 750;
}
.field-label i { color: var(--payment-blue); }
.select-wrap { position: relative; }
.payment-select {
    min-height: 52px;
    padding: 12px 44px 12px 14px;
    color: var(--payment-ink);
    background-color: #fff;
    border: 1px solid var(--payment-border);
    border-radius: 12px;
    box-shadow: none;
    font-size: 14px;
    font-weight: 600;
    appearance: none;
}
.payment-select:focus {
    border-color: #7ca1ff;
    box-shadow: 0 0 0 4px rgba(36, 87, 214, .1);
}
.select-chevron {
    position: absolute;
    top: 50%;
    right: 16px;
    color: #7b879c;
    font-size: 12px;
    pointer-events: none;
    transform: translateY(-50%);
}

.receipt-section {
    margin-bottom: 23px;
    padding: 17px;
    background: #fbfcff;
    border: 1px solid #e7edf7;
    border-radius: 16px;
}
.section-heading {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 15px;
}
.section-heading-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    font-size: 16px;
}
.section-heading h6 {
    margin: 0 0 3px;
    color: var(--payment-ink);
    font-size: 14px;
    font-weight: 800;
}
.section-heading p {
    margin: 0;
    color: var(--payment-muted);
    font-size: 12px;
}

.file-upload {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    padding: 13px;
    background: #fff;
    border: 1px dashed #b9c8e4;
    border-radius: 13px;
    cursor: pointer;
    transition: border-color .2s ease, background-color .2s ease;
}
.file-upload:hover {
    background: #f6f9ff;
    border-color: var(--payment-blue);
}
.file-input {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    clip-path: inset(50%);
}
.file-input:focus-visible + .file-upload-icon { outline: 3px solid rgba(36, 87, 214, .25); }
.file-upload-icon {
    display: grid;
    flex: 0 0 auto;
    place-items: center;
    width: 42px;
    height: 42px;
    color: var(--payment-blue);
    background: #edf3ff;
    border-radius: 12px;
    font-size: 18px;
}
.file-upload-copy {
    display: flex;
    flex: 1 1 auto;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}
.file-upload-copy strong {
    overflow: hidden;
    color: #263550;
    font-size: 13px;
    font-weight: 750;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.file-upload-copy small { color: var(--payment-muted); font-size: 11px; }
.file-upload-action {
    flex: 0 0 auto;
    padding: 7px 10px;
    color: var(--payment-blue);
    background: #edf3ff;
    border-radius: 9px;
    font-size: 12px;
    font-weight: 800;
}

.receipt-lost-option {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-top: 13px;
    padding: 12px;
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 12px;
    cursor: pointer;
}
.receipt-lost-check { display: flex; align-items: center; }
.receipt-lost-check .form-check-input {
    width: 2.2em;
    height: 1.2em;
    margin: 0;
    cursor: pointer;
}
.receipt-lost-check .form-check-input:checked {
    background-color: var(--payment-blue);
    border-color: var(--payment-blue);
}
.receipt-lost-copy { display: flex; flex-direction: column; gap: 3px; }
.receipt-lost-copy strong { color: #35425a; font-size: 13px; font-weight: 750; }
.receipt-lost-copy small { color: var(--payment-muted); font-size: 11px; line-height: 1.4; }

.confirm-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    min-height: 54px;
    padding: 13px 17px;
    color: #fff;
    background: linear-gradient(135deg, var(--payment-blue), var(--payment-blue-dark));
    border: 0;
    border-radius: 13px;
    box-shadow: 0 8px 18px rgba(36, 87, 214, .22);
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
}
.confirm-button:hover {
    filter: brightness(1.04);
    box-shadow: 0 11px 22px rgba(36, 87, 214, .28);
    transform: translateY(-1px);
}
.confirm-button:active { transform: translateY(0); }
.confirm-button:focus-visible { outline: 3px solid rgba(36, 87, 214, .3); outline-offset: 3px; }
.confirm-arrow { margin-left: auto; opacity: .85; }
.form-footnote {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    margin: 12px 0 0;
    color: #8a95a8;
    font-size: 11px;
    text-align: center;
}
.form-footnote i { color: #4e9b78; }

.payment-fields-enter-active,
.payment-fields-leave-active { transition: opacity .18s ease, transform .18s ease; }
.payment-fields-enter-from,
.payment-fields-leave-to { opacity: 0; transform: translateY(-6px); }

@media (max-width: 480px) {
    .payment-confirm-form { padding: 17px; border-radius: 18px; }
    .payment-form-header { gap: 11px; }
    .payment-form-icon { width: 44px; height: 44px; border-radius: 13px; font-size: 18px; }
    .deal-number { align-items: flex-start; flex-direction: column; gap: 7px; }
    .deal-number strong { text-align: left; }
    .receipt-section { padding: 13px; }
    .file-upload { gap: 9px; padding: 10px; }
    .file-upload-icon { width: 36px; height: 36px; }
    .file-upload-action { padding: 7px 8px; }
}

@media (prefers-reduced-motion: reduce) {
    .payment-confirm-form *,
    .payment-confirm-form *::before,
    .payment-confirm-form *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; }
}
</style>
