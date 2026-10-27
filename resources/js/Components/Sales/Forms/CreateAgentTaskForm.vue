<template>
    <div class="agent-task-card">
        <!-- Основная форма -->
        <template v-if="tab === 'form'">
            <div class="task-heading">
                <div class="task-heading__icon">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div>
                    <h5 class="task-heading__title">Новое задание</h5>
                    <p class="task-heading__subtitle">Заполните информацию и назначьте участников</p>
                </div>
            </div>

            <form class="task-form" @submit.prevent="createTask">
                <section class="task-section">
                    <div class="task-section__heading">
                        <span class="task-section__number">01</span>
                        <div>
                            <h6>Информация о задаче</h6>
                            <p>Название, описание и срок выполнения</p>
                        </div>
                    </div>

                    <div class="form-floating task-field">
                        <input
                            id="agent-task-title"
                            v-model="form.title"
                            type="text"
                            class="form-control"
                            placeholder="Название задачи"
                            autocomplete="off"
                            required
                        />
                        <label for="agent-task-title">Название задачи *</label>
                    </div>

                    <div class="form-floating task-field">
                        <textarea
                            id="agent-task-description"
                            v-model="form.description"
                            class="form-control task-description"
                            placeholder="Описание"
                        ></textarea>
                        <label for="agent-task-description">Описание задачи</label>
                    </div>

                    <div class="form-floating task-field">
                        <input
                            id="agent-task-due-date"
                            v-model="form.due_date"
                            type="date"
                            class="form-control"
                            required
                        />
                        <label for="agent-task-due-date">Дата выполнения *</label>
                    </div>
                </section>

                <section class="task-section">
                    <div class="task-section__heading">
                        <span class="task-section__number">02</span>
                        <div>
                            <h6>Назначение задачи</h6>
                            <p>Выберите участников и связанные данные</p>
                        </div>
                    </div>

                    <div class="assignment-grid">
                        <div class="assignment-item">
                            <div class="assignment-item__label">
                                <span class="assignment-item__icon assignment-item__icon--supplier">
                                    <i class="fa-solid fa-truck"></i>
                                </span>
                                <span>Поставщик</span>
                            </div>
                            <button
                                type="button"
                                class="assignment-select"
                                :class="{ 'assignment-select--selected': form.supplier }"
                                @click="tab = 'suppliers'"
                            >
                                <span class="assignment-select__text">
                                    {{ form.supplier ? form.supplier.name : 'Выберите поставщика' }}
                                </span>
                                <i class="fa-solid fa-chevron-right assignment-select__arrow"></i>
                            </button>
                        </div>

                        <div class="assignment-item">
                            <div class="assignment-item__label">
                                <span class="assignment-item__icon assignment-item__icon--product">
                                    <i class="fa-solid fa-box"></i>
                                </span>
                                <span>Продукт</span>
                            </div>
                            <button
                                type="button"
                                class="assignment-select"
                                :class="{ 'assignment-select--selected': form.product }"
                                @click="tab = 'products'"
                            >
                                <span class="assignment-select__text">
                                    {{ form.product ? form.product.name : 'Выберите продукт' }}
                                </span>
                                <i class="fa-solid fa-chevron-right assignment-select__arrow"></i>
                            </button>
                        </div>

                        <div class="assignment-item">
                            <div class="assignment-item__label">
                                <span class="assignment-item__icon assignment-item__icon--agent">
                                    <i class="fa-solid fa-user-tie"></i>
                                </span>
                                <span>Торговый представитель</span>
                            </div>
                            <button
                                type="button"
                                class="assignment-select"
                                :class="{ 'assignment-select--selected': form.agent }"
                                @click="tab = 'agents'"
                            >
                                <span class="assignment-select__text">
                                    {{ form.agent ? form.agent.name : 'Выберите представителя' }}
                                </span>
                                <i class="fa-solid fa-chevron-right assignment-select__arrow"></i>
                            </button>
                        </div>

                        <div class="assignment-item">
                            <div class="assignment-item__label">
                                <span class="assignment-item__icon assignment-item__icon--client">
                                    <i class="fa-solid fa-user"></i>
                                </span>
                                <span>Клиент</span>
                            </div>
                            <button
                                type="button"
                                class="assignment-select"
                                :class="{ 'assignment-select--selected': form.client }"
                                @click="tab = 'clients'"
                            >
                                <span class="assignment-select__text">
                                    {{ form.client ? form.client.name : 'Выберите клиента' }}
                                </span>
                                <i class="fa-solid fa-chevron-right assignment-select__arrow"></i>
                            </button>
                        </div>
                    </div>
                </section>

                <button type="submit" class="task-submit">
                    <i class="fa-solid fa-plus"></i>
                    <span>Создать задание</span>
                    <i class="fa-solid fa-arrow-right task-submit__arrow"></i>
                </button>
            </form>
        </template>

        <!-- Заглушка для поставщиков -->
        <template v-else-if="tab === 'suppliers'">
            <div class="selection-placeholder">
                <div class="selection-placeholder__icon selection-placeholder__icon--supplier">
                    <i class="fa-solid fa-truck"></i>
                </div>
                <h5>Выбор поставщика</h5>
                <p>Здесь будет отображаться список поставщиков для назначения задаче.</p>
                <span class="selection-placeholder__badge">Раздел в разработке</span>
                <button type="button" class="task-back" @click="tab = 'form'">
                    <i class="fa-solid fa-arrow-left"></i>
                    Назад к форме
                </button>
            </div>
        </template>

        <!-- Заглушка для продуктов -->
        <template v-else-if="tab === 'products'">
            <div class="selection-placeholder">
                <div class="selection-placeholder__icon selection-placeholder__icon--product">
                    <i class="fa-solid fa-box"></i>
                </div>
                <h5>Выбор продукта</h5>
                <p>Здесь будет отображаться список продуктов, доступных для задания.</p>
                <span class="selection-placeholder__badge">Раздел в разработке</span>
                <button type="button" class="task-back" @click="tab = 'form'">
                    <i class="fa-solid fa-arrow-left"></i>
                    Назад к форме
                </button>
            </div>
        </template>

        <!-- Заглушка для агентов -->
        <template v-else-if="tab === 'agents'">
            <div class="selection-placeholder">
                <div class="selection-placeholder__icon selection-placeholder__icon--agent">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <h5>Выбор торгового представителя</h5>
                <p>Здесь будет отображаться список торговых представителей.</p>
                <span class="selection-placeholder__badge">Раздел в разработке</span>
                <button type="button" class="task-back" @click="tab = 'form'">
                    <i class="fa-solid fa-arrow-left"></i>
                    Назад к форме
                </button>
            </div>
        </template>

        <!-- Заглушка для клиентов -->
        <template v-else-if="tab === 'clients'">
            <div class="selection-placeholder">
                <div class="selection-placeholder__icon selection-placeholder__icon--client">
                    <i class="fa-solid fa-user"></i>
                </div>
                <h5>Выбор клиента</h5>
                <p>Здесь будет отображаться список клиентов для назначения задаче.</p>
                <span class="selection-placeholder__badge">Раздел в разработке</span>
                <button type="button" class="task-back" @click="tab = 'form'">
                    <i class="fa-solid fa-arrow-left"></i>
                    Назад к форме
                </button>
            </div>
        </template>
    </div>
</template>

<script>
export default {
    name: 'CreateAgentTaskForm',
    data() {
        return {
            tab: 'form',
            form: {
                title: '',
                description: '',
                due_date: '',
                supplier: null,
                product: null,
                agent: null,
                client: null
            }
        }
    },
    methods: {
        createTask() {
            console.log('Создано задание:', this.form)
        }
    }
}
</script>

<style scoped>
.agent-task-card {
    --task-navy: #172b4d;
    --task-blue: #2864dc;
    --task-red: #e34d59;
    --task-muted: #78869b;
    --task-border: #e5eaf2;
    padding: clamp(1rem, 3vw, 1.75rem);
    color: var(--task-navy);
    background: #fff;
    border: 1px solid #edf0f5;
    border-radius: 20px;
    box-shadow: 0 12px 36px rgba(23, 43, 77, 0.07);
}

.task-heading {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-bottom: 1.25rem;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--task-border);
}

.task-heading__icon {
    display: grid;
    flex: 0 0 52px;
    width: 52px;
    height: 52px;
    place-items: center;
    color: #fff;
    font-size: 1.3rem;
    background: linear-gradient(135deg, #3478f6, #2052c7);
    border-radius: 16px;
    box-shadow: 0 7px 16px rgba(40, 100, 220, 0.2);
}

.task-heading__title {
    margin: 0 0 0.3rem;
    color: var(--task-navy);
    font-size: 1.15rem;
    font-weight: 750;
}

.task-heading__subtitle {
    margin: 0;
    color: var(--task-muted);
    font-size: 0.88rem;
    line-height: 1.5;
}

.task-section + .task-section {
    margin-top: 1.75rem;
}

.task-section__heading {
    display: flex;
    align-items: flex-start;
    gap: 0.8rem;
    margin-bottom: 1rem;
}

.task-section__number {
    display: grid;
    flex: 0 0 36px;
    width: 36px;
    height: 36px;
    place-items: center;
    color: var(--task-blue);
    font-size: 0.78rem;
    font-weight: 800;
    background: #edf4ff;
    border-radius: 11px;
}

.task-section__heading h6 {
    margin: 0 0 0.25rem;
    color: var(--task-navy);
    font-size: 0.98rem;
    font-weight: 750;
}

.task-section__heading p {
    margin: 0;
    color: var(--task-muted);
    font-size: 0.82rem;
}

.task-field {
    margin-bottom: 0.9rem;
}

.task-field .form-control {
    min-height: 58px;
    color: var(--task-navy);
    background-color: #fbfcfe;
    border: 1px solid var(--task-border);
    border-radius: 12px;
    box-shadow: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.task-field .form-control:focus {
    background-color: #fff;
    border-color: #8bb2ff;
    box-shadow: 0 0 0 4px rgba(40, 100, 220, 0.1);
}

.task-field label {
    color: #748198;
}

.task-field .task-description {
    min-height: 112px;
    resize: vertical;
}

.assignment-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.assignment-item {
    min-width: 0;
    padding: 1rem;
    background: #fbfcfe;
    border: 1px solid var(--task-border);
    border-radius: 15px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.assignment-item:focus-within,
.assignment-item:hover {
    border-color: #c6d8fb;
    box-shadow: 0 5px 16px rgba(23, 43, 77, 0.04);
}

.assignment-item__label {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.8rem;
    color: #4d5d75;
    font-size: 0.86rem;
    font-weight: 700;
}

.assignment-item__icon,
.selection-placeholder__icon {
    display: grid;
    flex: 0 0 34px;
    width: 34px;
    height: 34px;
    place-items: center;
    border-radius: 10px;
}

.assignment-item__icon--supplier,
.selection-placeholder__icon--supplier {
    color: #2864dc;
    background: #eaf1ff;
}

.assignment-item__icon--product,
.selection-placeholder__icon--product {
    color: #138b72;
    background: #e4f7f1;
}

.assignment-item__icon--agent,
.selection-placeholder__icon--agent {
    color: #8055c7;
    background: #f1eaff;
}

.assignment-item__icon--client,
.selection-placeholder__icon--client {
    color: #c14e5b;
    background: #fff0f1;
}

.assignment-select {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    width: 100%;
    min-height: 46px;
    padding: 0.7rem 0.8rem;
    color: #77849a;
    text-align: left;
    background: #fff;
    border: 1px solid #e3e9f2;
    border-radius: 10px;
    cursor: pointer;
    transition: color 0.2s ease, border-color 0.2s ease, background-color 0.2s ease;
}

.assignment-select:hover,
.assignment-select:focus-visible {
    color: var(--task-blue);
    border-color: #9bbaff;
    outline: none;
}

.assignment-select--selected {
    color: #176b57;
    background: #f0fbf7;
    border-color: #bce9da;
}

.assignment-select__text {
    overflow: hidden;
    font-size: 0.88rem;
    font-weight: 600;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.assignment-select__arrow {
    flex: 0 0 auto;
    color: #9aa6b8;
    font-size: 0.75rem;
}

.task-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    width: 100%;
    min-height: 56px;
    margin-top: 1.75rem;
    padding: 0.9rem 1.25rem;
    color: #fff;
    font-size: 0.98rem;
    font-weight: 750;
    background: linear-gradient(110deg, #2864dc, #1749b8);
    border: 0;
    border-radius: 13px;
    box-shadow: 0 8px 18px rgba(40, 100, 220, 0.2);
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.task-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 11px 22px rgba(40, 100, 220, 0.27);
}

.task-submit:active {
    transform: translateY(0);
}

.task-submit__arrow {
    margin-left: auto;
    opacity: 0.8;
}

.selection-placeholder {
    display: flex;
    min-height: 320px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    text-align: center;
}

.selection-placeholder__icon {
    width: 64px;
    height: 64px;
    margin-bottom: 1rem;
    font-size: 1.5rem;
    border-radius: 20px;
}

.selection-placeholder h5 {
    margin: 0 0 0.5rem;
    color: var(--task-navy);
    font-weight: 750;
}

.selection-placeholder p {
    max-width: 420px;
    margin: 0;
    color: var(--task-muted);
    font-size: 0.9rem;
    line-height: 1.6;
}

.selection-placeholder__badge {
    margin-top: 1rem;
    padding: 0.4rem 0.75rem;
    color: #69778d;
    font-size: 0.78rem;
    font-weight: 650;
    background: #f0f3f8;
    border-radius: 999px;
}

.task-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    min-height: 44px;
    margin-top: 1.5rem;
    padding: 0.7rem 1rem;
    color: var(--task-blue);
    font-size: 0.9rem;
    font-weight: 700;
    background: #edf4ff;
    border: 1px solid #dce8ff;
    border-radius: 11px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.task-back:hover {
    background: #e0ebff;
}

@media (max-width: 640px) {
    .agent-task-card {
        padding: 1rem;
        border-radius: 16px;
    }

    .task-heading {
        align-items: flex-start;
    }

    .task-heading__icon {
        flex-basis: 44px;
        width: 44px;
        height: 44px;
        border-radius: 13px;
    }

    .assignment-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 0.75rem;
    }

    .assignment-item {
        padding: 0.85rem;
    }

    .task-section__heading p {
        line-height: 1.45;
    }
}
</style>
