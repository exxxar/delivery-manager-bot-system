<template>
    <form class="agent-form" @submit.prevent="submitForm">
        <template v-if="tab === 'main'">
            <section v-if="initialData?.user_info" class="form-section info-section mb-4">
                <div class="section-heading">
                    <span class="section-icon"><i class="fa-solid fa-circle-info"></i></span>
                    <div>
                        <h6 class="section-title">Информация об агенте</h6>
                        <p class="section-subtitle">Дополнительные сведения о пользователе</p>
                    </div>
                </div>
                <div class="user-info-content" v-html="initialData.user_info"></div>
            </section>

            <section class="form-section mb-4">
                <div class="section-heading">
                    <span class="section-icon"><i class="fa-solid fa-user"></i></span>
                    <div>
                        <h6 class="section-title">Основные данные</h6>
                        <p class="section-subtitle">Контактная информация и регион работы</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="agent-name">Имя <span class="required-mark">*</span></label>
                        <div class="field-wrap">
                            <i class="fa-solid fa-user field-icon"></i>
                            <input v-model.trim="form.name" type="text" class="form-control" id="agent-name" placeholder="Введите имя" autocomplete="name" required />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="agent-phone">Телефон <span class="required-mark">*</span></label>
                        <div class="field-wrap">
                            <i class="fa-solid fa-phone field-icon"></i>
                            <input v-model="form.phone" v-mask="'+7(###) ###-##-##'" minlength="3" type="text" class="form-control" id="agent-phone" placeholder="+7 (___) ___-__-__" autocomplete="tel" required />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="agent-email">Email <span class="required-mark">*</span></label>
                        <div class="field-wrap">
                            <i class="fa-solid fa-envelope field-icon"></i>
                            <input v-model.trim="form.email" minlength="3" type="email" class="form-control" id="agent-email" placeholder="name@example.com" autocomplete="email" required />
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="agent-region">Регион <span class="required-mark">*</span></label>
                        <div class="field-wrap">
                            <i class="fa-solid fa-location-dot field-icon"></i>
                            <input v-model.trim="form.region" minlength="3" type="text" class="form-control" id="agent-region" placeholder="Укажите регион работы" required />
                        </div>
                    </div>

                    <div v-if="user?.role >= 3" class="col-12">
                        <label class="form-label" for="agent-percent">Процент</label>
                        <div class="field-wrap">
                            <i class="fa-solid fa-percent field-icon"></i>
                            <input v-model="form.percent" type="number" min="0" max="100" step="0.1" class="form-control" id="agent-percent" placeholder="Например, 5" />
                        </div>
                        <div class="form-hint">Значение от 0 до 100%</div>
                    </div>
                </div>
            </section>

            <section class="form-section mb-4">
                <div class="section-heading">
                    <span class="section-icon"><i class="fa-solid fa-sliders"></i></span>
                    <div>
                        <h6 class="section-title">Настройки агента</h6>
                        <p class="section-subtitle">Дополнительные параметры учётной записи</p>
                    </div>
                </div>

                <label class="setting-row" for="agent-is-test">
                    <span class="setting-icon"><i class="fa-solid fa-flask"></i></span>
                    <span class="setting-copy">
            <span class="setting-title">Тестовый агент</span>
            <span class="setting-description">Помечает учётную запись как тестовую</span>
          </span>
                    <span class="setting-state">{{ form.is_test ? 'Включено' : 'Выключено' }}</span>
                    <input v-model="form.is_test" class="form-check-input setting-switch" type="checkbox" role="switch" id="agent-is-test" />
                </label>
            </section>

            <div v-if="form.id" class="form-actions mb-3">
                <button type="button" class="btn btn-secondary-soft w-100" @click="tab = 'percentage'">
                    <i class="fa-solid fa-percent me-2"></i>Выплаты процентов
                    <i class="fa-solid fa-chevron-right ms-auto"></i>
                </button>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary btn-save">
                    <i class="fa-solid fa-floppy-disk me-2"></i>{{ isEdit ? 'Сохранить изменения' : 'Создать агента' }}
                </button>
            </div>
        </template>

        <template v-else-if="tab === 'admin'">
            <div class="subpage-header">
                <button type="button" class="btn btn-back" @click="tab = 'main'"><i class="fa-solid fa-arrow-left me-2"></i>Назад</button>
                <div>
                    <h5>Выбор наставника</h5>
                    <p>Выберите администратора из списка</p>
                </div>
            </div>
            <AdminList :for-select="true" @select="selectAdmin" />
        </template>

        <template v-else-if="tab === 'percentage'">
            <div class="subpage-header">
                <button type="button" class="btn btn-back" @click="tab = 'main'"><i class="fa-solid fa-arrow-left me-2"></i>Назад</button>
                <div>
                    <h5>Выплаты процентов</h5>
                    <p>Настройка распределения процентов агента</p>
                </div>
            </div>

            <div v-if="loadPercentageForm" class="percentage-workspace">
                <PercentageForm v-on:saved="saveForm" :agent-id="form.id" :initial-data="selectedPercentage">
                    <template #back>
                        <button type="button" class="btn btn-back mb-3" @click="tab = 'main'"><i class="fa-solid fa-arrow-left me-2"></i>Назад к агенту</button>
                    </template>
                    <template #list>
                        <PercentageList :for-select="true" v-on:select="selectPercentage" :agent-id="form.id" />
                    </template>
                </PercentageForm>
            </div>
        </template>
    </form>
</template>

<script>
import { useAgentsStore } from '@/stores/agents'
import { useUsersStore } from '@/stores/users'
import AdminList from '@/Components/Admins/AdminList.vue'
import PercentageList from '@/Components/Percentage/PercentageList.vue'
import PercentageForm from '@/Components/Percentage/PercentageForm.vue'

export default {
    name: 'AgentForm',
    components: { AdminList, PercentageList, PercentageForm },
    props: {
        initialData: { type: Object, default: null },
    },
    emits: ['callback'],
    data() {
        return {
            selectedPercentage: null,
            loadPercentageForm: true,
            agentStore: useAgentsStore(),
            userStore: useUsersStore(),
            isEdit: false,
            configLearningPeriod: false,
            tab: 'main',
            form: {
                name: '',
                phone: '',
                email: '',
                region: '',
                percent: null,
                start_learning_date: null,
                end_learning_date: null,
                in_learning: false,
                is_test: false,
            },
            mentorName: null,
        }
    },
    created() {
        if (this.initialData) {
            this.form = { ...this.form, ...this.initialData }
            this.isEdit = true
            this.mentorName = this.initialData.mentor?.name || ''
        }
    },
    computed: {
        user() {
            return this.userStore.self || null
        },
    },
    methods: {
        saveForm() {
            this.selectedPercentage = null
            this.loadPercentageForm = false
            this.$nextTick(() => {
                this.loadPercentageForm = true
            })
        },
        selectPercentage(percentage) {
            this.selectedPercentage = null
            this.loadPercentageForm = false
            this.$nextTick(() => {
                this.selectedPercentage = percentage
                this.loadPercentageForm = true
            })
        },
        selectAdmin(admin) {
            this.form.mentor_id = admin.id
            this.mentorName = admin.name
            this.tab = 'main'
        },
        async submitForm() {
            if (!this.isEdit) {
                await this.agentStore.create(this.form)
            } else {
                await this.agentStore.update(this.form.id, this.form)
            }
            this.$emit('callback')
        },
    },
}
</script>

<style scoped>
.agent-form {
    --agent-blue: #2457d6;
    --agent-blue-dark: #173b91;
    --agent-ink: #17243b;
    --agent-muted: #738099;
    --agent-border: #e4eaf3;
    color: var(--agent-ink);
}

.form-section {
    padding: 1.15rem;
    border: 1px solid var(--agent-border);
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 5px 20px rgba(22, 43, 82, .045);
}

.info-section {
    background: linear-gradient(135deg, #f4f8ff 0%, #fff 75%);
    border-color: #dbe7ff;
}

.section-heading {
    display: flex;
    align-items: center;
    gap: .8rem;
    margin-bottom: 1.15rem;
}

.section-icon {
    display: grid;
    place-items: center;
    flex: 0 0 42px;
    width: 42px;
    height: 42px;
    border-radius: 13px;
    color: var(--agent-blue);
    background: #edf3ff;
    font-size: 1.05rem;
}

.section-title {
    margin: 0 0 .2rem;
    font-size: .98rem;
    font-weight: 750;
    letter-spacing: -.015em;
}

.section-subtitle {
    margin: 0;
    color: var(--agent-muted);
    font-size: .8rem;
}

.form-label {
    margin-bottom: .45rem;
    color: #3d4b63;
    font-size: .84rem;
    font-weight: 650;
}

.required-mark { color: #dc3545; }

.field-wrap { position: relative; }
.field-icon {
    position: absolute;
    z-index: 2;
    top: 50%;
    left: .95rem;
    color: #8290a8;
    transform: translateY(-50%);
    pointer-events: none;
}

.field-wrap .form-control {
    min-height: 46px;
    padding: .7rem .9rem .7rem 2.65rem;
    border: 1px solid #dce4f0;
    border-radius: 11px;
    color: var(--agent-ink);
    background-color: #fbfcff;
    box-shadow: none;
    transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
}

.field-wrap .form-control:focus {
    border-color: #7ea4ff;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(36, 87, 214, .11);
}

.form-hint { margin-top: .4rem; color: var(--agent-muted); font-size: .77rem; }

.setting-row {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .9rem;
    border: 1px solid var(--agent-border);
    border-radius: 13px;
    background: #fbfcff;
    cursor: pointer;
}

.setting-icon {
    display: grid;
    place-items: center;
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    border-radius: 11px;
    color: #bd6a13;
    background: #fff2df;
}

.setting-copy { display: flex; flex: 1; flex-direction: column; gap: .2rem; }
.setting-title { font-size: .88rem; font-weight: 700; }
.setting-description { color: var(--agent-muted); font-size: .77rem; }
.setting-state { color: var(--agent-muted); font-size: .78rem; font-weight: 650; }
.setting-switch { flex: 0 0 auto; width: 2.4rem; height: 1.25rem; margin: 0; cursor: pointer; }
.setting-switch:checked { background-color: var(--agent-blue); border-color: var(--agent-blue); }

.btn-primary {
    border-color: var(--agent-blue);
    background: var(--agent-blue);
    box-shadow: 0 4px 10px rgba(36, 87, 214, .14);
}
.btn-primary:hover { border-color: var(--agent-blue-dark); background: var(--agent-blue-dark); }
.btn-save { min-height: 49px; width: 100%; border-radius: 12px; font-weight: 700; }
.btn-secondary-soft {
    display: flex;
    align-items: center;
    padding: .9rem 1rem;
    border: 1px solid #dbe5fa;
    border-radius: 12px;
    color: var(--agent-blue);
    background: #f3f7ff;
    font-weight: 650;
    text-align: left;
}
.btn-secondary-soft:hover { border-color: #b9ccfa; background: #eaf1ff; color: var(--agent-blue-dark); }
.btn-back {
    align-self: flex-start;
    border: 1px solid var(--agent-border);
    border-radius: 10px;
    color: #4c5a70;
    background: #fff;
    font-weight: 600;
}
.btn-back:hover { border-color: #c9d7ef; color: var(--agent-blue); background: #f7f9ff; }

.subpage-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1rem;
    border: 1px solid var(--agent-border);
    border-radius: 15px;
    background: #f8faff;
}
.subpage-header h5 { margin: .1rem 0 .25rem; font-size: 1rem; font-weight: 750; }
.subpage-header p { margin: 0; color: var(--agent-muted); font-size: .82rem; }
.percentage-workspace { padding: .25rem 0; }
.user-info-content { color: #46546b; font-size: .9rem; overflow-wrap: anywhere; }
.user-info-content :deep(p:last-child) { margin-bottom: 0; }

@media (max-width: 575.98px) {
    .form-section { padding: .9rem; border-radius: 13px; }
    .section-heading { gap: .65rem; }
    .section-icon { flex-basis: 38px; width: 38px; height: 38px; }
    .setting-row { flex-wrap: wrap; gap: .65rem; }
    .setting-copy { min-width: calc(100% - 55px); }
    .setting-state { margin-left: 3.2rem; }
    .setting-switch { margin-left: auto; }
    .subpage-header { flex-direction: column; gap: .7rem; }
}
</style>
