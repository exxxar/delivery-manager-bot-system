<template>
    <main class="agents-page">
        <div class="page-shell">
            <div class="page-top">
                <BackBtn />
            </div>

            <header class="page-heading">
                <div class="heading-icon" aria-hidden="true">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="heading-copy">
                    <span class="eyebrow">УПРАВЛЕНИЕ КОМАНДОЙ</span>
                    <h1>Список администраторов</h1>
                    <p>Просматривайте администраторов и управляйте их данными и активностью.</p>
                </div>


                <button
                    type="button"
                    class="without-percent-card"
                    @click="openWithoutPercentModal"
                    :disabled="loadingWithoutPercent"
                >
                    <span class="without-percent-icon">
                        <i class="fa-solid fa-percent"></i>
                    </span>

                                    <span class="without-percent-copy">
                        <strong>Администраторы без процента</strong>
                        <small>Нажмите, чтобы посмотреть список и настроить процент</small>
                    </span>

                                    <span class="without-percent-count">
                        {{ loadingWithoutPercent ? '…' : withoutPercentCount }}
                    </span>

                    <i class="fa-solid fa-chevron-right without-percent-arrow"></i>
                </button>

            </header>

            <section class="agents-panel" aria-label="Список администраторов">
                <div class="panel-heading">
                    <div class="panel-heading-icon"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <h2>Администраторы</h2>
                        <p>Список сотрудников и доступные действия</p>
                    </div>
                </div>
                <div class="panel-content">
                    <AgentList />
                </div>
            </section>
        </div>

        <div class="bottom-action-spacer" aria-hidden="true"></div>
        <nav class="bottom-action" aria-label="Действия со списком">
            <div class="bottom-action-inner">
                <div class="bottom-action-copy">
                    <span class="action-indicator"></span>
                    <div>
                        <strong>Новый администратор</strong>
                        <span>Добавить сотрудника в систему</span>
                    </div>
                </div>
                <button type="button" class="add-admin-button" @click="goTo('UserPage')">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Добавить администратора</span>
                    <i class="fa-solid fa-arrow-right button-arrow"></i>
                </button>
            </div>
        </nav>


        <!-- Администраторы без процента -->
        <div
            class="modal fade"
            id="withoutPercentModal"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content without-percent-modal">

                    <div class="modal-header">
                        <div>
                            <span class="modal-eyebrow">ТРЕБУЕТСЯ НАСТРОЙКА</span>
                            <h5 class="modal-title">Администраторы без процента</h5>
                            <p class="modal-subtitle mb-0">
                                Выберите сотрудника, чтобы настроить процент в его профиле.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>
                    </div>

                    <div class="modal-body">

                        <div v-if="loadingWithoutPercent" class="text-center py-5">
                            <div class="spinner-border text-primary"></div>
                        </div>

                        <div
                            v-else-if="withoutPercentAgents.length === 0"
                            class="empty-without-percent"
                        >
                            <i class="fa-solid fa-circle-check"></i>
                            <strong>Все проценты настроены</strong>
                            <span>Администраторов без установленного процента нет.</span>
                        </div>

                        <div v-else class="without-percent-list">

                            <article
                                v-for="agent in withoutPercentAgents"
                                :key="agent.id"
                                class="without-percent-item"
                            >
                                <div class="without-percent-avatar">
                                    <i class="fa-solid fa-user-tie"></i>
                                </div>

                                <div class="without-percent-person">
                                    <strong>{{ agent.name }}</strong>
                                    <span>{{ agent.phone || agent.user?.email || 'Контакт не указан' }}</span>
                                    <small>Агент #{{ agent.id }}</small>
                                </div>

                                <span class="missing-percent-label">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Не задан
                        </span>

                                <button
                                    type="button"
                                    class="edit-percent-button"
                                    :disabled="!agent.user"
                                    @click="openUserEditor(agent)"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Настроить
                                </button>
                            </article>

                        </div>
                    </div>

                    <div class="modal-footer">
                <span class="modal-footer-note">
                    Найдено: <strong>{{ withoutPercentAgents.length }}</strong>
                </span>

                        <button
                            type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal"
                        >
                            Закрыть
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Редактирование пользователя -->
        <div
            class="modal fade"
            id="editUserPercentModal"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Редактирование администратора</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <UserForm
                            v-if="selectedUser"
                            :key="selectedUser.id"
                            :initialData="selectedUser"
                            @saved="onUserSaved"
                        />
                    </div>

                </div>
            </div>
        </div>

    </main>
</template>

<script setup>
import AgentList from "@/Components/Agents/AgentList.vue";
import BackBtn from "@/Components/BackBtn.vue";
import axios from 'axios'
import UserForm from "@/Components/Users/UserForm.vue"
</script>

<script>
export default {
    name: 'AgentsPage',
    components: {
        UserForm,
    },
    data(){
      return {
          withoutPercentCount: 0,
          withoutPercentAgents: [],
          loadingWithoutPercent: false,
          selectedUser: null,
      }
    },
    mounted() {
        this.loadWithoutPercent()
    },
    methods: {
        goTo(name) {
            this.$router.push({ name });
        },

        async loadWithoutPercent() {
            this.loadingWithoutPercent = true

            try {
                const response = await axios.get('/api/agents/without-percent')

                this.withoutPercentAgents = response.data.data || []
                this.withoutPercentCount = Number(response.data.count || 0)

            } catch (error) {
                console.error('Ошибка загрузки администраторов без процента:', error)
            } finally {
                this.loadingWithoutPercent = false
            }
        },

        async openWithoutPercentModal() {
            await this.loadWithoutPercent()

            this.$nextTick(() => {
                const element = document.getElementById('withoutPercentModal')

                if (element) {
                    bootstrap.Modal.getOrCreateInstance(element).show()
                }
            })
        },

        openUserEditor(agent) {
            if (!agent?.user) return

            this.selectedUser = { ...agent.user }

            this.$nextTick(() => {
                const listModal = document.getElementById('withoutPercentModal')
                const editModal = document.getElementById('editUserPercentModal')

                const showEditor = () => {
                    if (editModal) {
                        bootstrap.Modal.getOrCreateInstance(editModal).show()
                    }
                }

                if (listModal) {
                    const instance = bootstrap.Modal.getInstance(listModal)

                    if (instance && listModal.classList.contains('show')) {
                        listModal.addEventListener('hidden.bs.modal', showEditor, { once: true })
                        instance.hide()
                        return
                    }
                }

                showEditor()
            })
        },

        async onUserSaved() {
            const editModal = document.getElementById('editUserPercentModal')

            if (editModal) {
                bootstrap.Modal.getInstance(editModal)?.hide()
            }

            await this.loadWithoutPercent()

            this.selectedUser = null
        },

    },
};
</script>

<style scoped>
.agents-page {
    --navy: #172b4d;
    --blue: #2463eb;
    --blue-dark: #1747b5;
    --red: #e44755;
    --muted: #718096;
    --line: #e7edf5;
    min-height: 100%;
    padding: 0 0 1.5rem;
    color: var(--navy);
    background: #f5f7fb;
}

.page-shell {
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    padding: 1rem clamp(1rem, 2.5vw, 2rem) 0;
}

.page-top {
    margin-bottom: 1rem;
}

.page-heading {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: clamp(1.1rem, 3vw, 1.75rem);
    border: 1px solid #e5ebf5;
    border-radius: 22px;
    background: linear-gradient(120deg, #fff 0%, #f1f6ff 100%);
    box-shadow: 0 8px 28px rgba(27, 55, 100, 0.06);
}

.heading-icon {
    display: grid;
    flex: 0 0 60px;
    width: 60px;
    height: 60px;
    place-items: center;
    border-radius: 18px;
    color: #fff;
    background: linear-gradient(145deg, var(--blue), #5488ff);
    box-shadow: 0 8px 18px rgba(36, 99, 235, 0.22);
    font-size: 1.45rem;
}

.heading-copy { min-width: 0; }
.eyebrow {
    display: block;
    margin-bottom: .35rem;
    color: var(--red);
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .13em;
}

.heading-copy h1 {
    margin: 0;
    color: var(--navy);
    font-size: clamp(1.35rem, 2.6vw, 1.9rem);
    font-weight: 800;
    letter-spacing: -.035em;
}

.heading-copy p {
    margin: .45rem 0 0;
    color: var(--muted);
    font-size: .92rem;
    line-height: 1.5;
}

.agents-panel {
    overflow: hidden;
    border: 1px solid var(--line);
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 8px 26px rgba(27, 55, 100, .045);
}

.panel-heading {
    display: flex;
    align-items: center;
    gap: .8rem;
    padding: 1.15rem 1.35rem;
    border-bottom: 1px solid var(--line);
    background: #fff;
}

.panel-heading-icon {
    display: grid;
    flex: 0 0 42px;
    width: 42px;
    height: 42px;
    place-items: center;
    border-radius: 13px;
    color: var(--blue);
    background: #edf3ff;
    font-size: 1rem;
}

.panel-heading h2 {
    margin: 0;
    color: var(--navy);
    font-size: 1.05rem;
    font-weight: 800;
}

.panel-heading p {
    margin: .2rem 0 0;
    color: var(--muted);
    font-size: .8rem;
}

.panel-content { padding: clamp(.75rem, 2vw, 1.25rem); }
.bottom-action-spacer { height: 110px; }

.bottom-action {
    position: fixed;
    z-index: 1020;
    right: 0;
    bottom: 0;
    left: 0;
    padding: .85rem clamp(1rem, 2.5vw, 2rem) calc(.85rem + env(safe-area-inset-bottom));
    border-top: 1px solid rgba(222, 230, 242, .9);
    background: rgba(255, 255, 255, .94);
    box-shadow: 0 -8px 28px rgba(27, 55, 100, .07);
    backdrop-filter: blur(14px);
}

.bottom-action-inner {
    display: flex;
    width: 100%;
    max-width: 1440px;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin: 0 auto;
}

.bottom-action-copy { display: flex; align-items: center; gap: .75rem; }
.action-indicator {
    width: 9px;
    height: 36px;
    border-radius: 10px;
    background: var(--red);
}
.bottom-action-copy strong,
.bottom-action-copy span:not(.action-indicator) { display: block; }
.bottom-action-copy strong { color: var(--navy); font-size: .92rem; font-weight: 800; }
.bottom-action-copy div span { margin-top: .15rem; color: var(--muted); font-size: .78rem; }

.add-admin-button {
    display: inline-flex;
    min-height: 50px;
    align-items: center;
    justify-content: center;
    gap: .65rem;
    padding: .85rem 1.2rem;
    border: 0;
    border-radius: 14px;
    color: #fff;
    background: linear-gradient(135deg, var(--blue), var(--blue-dark));
    box-shadow: 0 7px 16px rgba(36, 99, 235, .22);
    font: inherit;
    font-size: .9rem;
    font-weight: 750;
    cursor: pointer;
    transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
}

.add-admin-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 22px rgba(36, 99, 235, .3);
    filter: brightness(1.04);
}
.add-admin-button:active { transform: translateY(0); }
.add-admin-button:focus-visible { outline: 3px solid rgba(36, 99, 235, .3); outline-offset: 3px; }
.button-arrow { margin-left: .2rem; color: rgba(255,255,255,.8); }

@media (max-width: 640px) {
    .page-shell { padding: .75rem .75rem 0; }
    .page-heading { align-items: flex-start; gap: .8rem; margin-bottom: 1rem; border-radius: 17px; }
    .heading-icon { flex-basis: 46px; width: 46px; height: 46px; border-radius: 14px; font-size: 1.15rem; }
    .heading-copy p { font-size: .83rem; }
    .agents-panel { border-radius: 16px; }
    .panel-heading { padding: .9rem; }
    .panel-content { padding: .65rem; }
    .bottom-action-inner { display: block; }
    .bottom-action-copy { display: none; }
    .add-admin-button { width: 100%; min-height: 52px; }
    .bottom-action { padding: .65rem .75rem calc(.65rem + env(safe-area-inset-bottom)); }
    .bottom-action-spacer { height: 85px; }
}


.without-percent-card {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 1rem;
    margin: 0 0 1.5rem;
    padding: 1rem 1.2rem;
    border: 1px solid #f1d9a8;
    border-radius: 18px;
    color: #684b17;
    background: linear-gradient(110deg, #fffaf0, #fff 78%);
    box-shadow: 0 7px 22px rgba(140, 100, 30, .07);
    text-align: left;
    cursor: pointer;
    transition: .2s ease;
}

.without-percent-card:hover {
    transform: translateY(-2px);
    border-color: #e8c477;
    box-shadow: 0 11px 26px rgba(140, 100, 30, .12);
}

.without-percent-icon,
.without-percent-avatar {
    display: grid;
    flex: 0 0 46px;
    width: 46px;
    height: 46px;
    place-items: center;
    border-radius: 14px;
    color: #a86c12;
    background: #ffedc7;
    font-size: 1.15rem;
}

.without-percent-copy {
    display: flex;
    flex: 1;
    min-width: 0;
    flex-direction: column;
    gap: .22rem;
}

.without-percent-copy strong {
    color: #604515;
    font-size: .96rem;
    font-weight: 800;
}

.without-percent-copy small {
    color: #94794c;
    font-size: .78rem;
}

.without-percent-count {
    min-width: 48px;
    padding: .35rem .7rem;
    border-radius: 12px;
    color: #fff;
    background: #c58a2c;
    font-size: 1.2rem;
    font-weight: 850;
    text-align: center;
}

.without-percent-arrow {
    color: #b28a4d;
}

.without-percent-modal {
    overflow: hidden;
    border: 0;
    border-radius: 20px;
    box-shadow: 0 22px 70px rgba(18, 39, 73, .2);
}

.modal-eyebrow {
    display: block;
    margin-bottom: .35rem;
    color: #e44755;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .13em;
}

.modal-subtitle {
    margin-top: .4rem;
    color: #7b899e;
    font-size: .82rem;
}

.without-percent-list {
    display: grid;
    gap: .65rem;
}

.without-percent-item {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .85rem;
    border: 1px solid #e8edf5;
    border-radius: 14px;
    background: #fff;
}

.without-percent-avatar {
    color: #2463eb;
    background: #edf3ff;
}

.without-percent-person {
    display: flex;
    flex: 1;
    min-width: 0;
    flex-direction: column;
    gap: .18rem;
}

.without-percent-person strong {
    color: #172b4d;
    font-size: .9rem;
}

.without-percent-person span {
    color: #63738c;
    font-size: .78rem;
}

.without-percent-person small {
    color: #9aa5b5;
    font-size: .68rem;
}

.missing-percent-label {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .35rem .55rem;
    border-radius: 8px;
    color: #996313;
    background: #fff3d9;
    font-size: .7rem;
    font-weight: 750;
    white-space: nowrap;
}

.edit-percent-button {
    display: inline-flex;
    min-height: 38px;
    align-items: center;
    gap: .45rem;
    padding: .5rem .75rem;
    border: 1px solid #dbe6fb;
    border-radius: 10px;
    color: #2056b4;
    background: #f4f7ff;
    font: inherit;
    font-size: .78rem;
    font-weight: 750;
    white-space: nowrap;
}

.edit-percent-button:hover:not(:disabled) {
    border-color: #b9cef5;
    background: #eaf1ff;
}

.empty-without-percent {
    display: grid;
    justify-items: center;
    gap: .45rem;
    padding: 2.5rem 1rem;
    color: #7c8ca2;
    text-align: center;
}

.empty-without-percent i {
    color: #20a36b;
    font-size: 2.2rem;
}

.empty-without-percent strong {
    color: #233a5b;
}

.modal-footer-note {
    margin-right: auto;
    color: #8794a7;
    font-size: .8rem;
}

@media (max-width: 640px) {
    .without-percent-card {
        gap: .65rem;
        padding: .8rem;
    }

    .without-percent-copy strong {
        font-size: .82rem;
    }

    .without-percent-copy small {
        font-size: .68rem;
    }

    .without-percent-item {
        flex-wrap: wrap;
        gap: .6rem;
    }

    .without-percent-person {
        flex-basis: calc(100% - 55px);
    }

    .edit-percent-button {
        margin-left: auto;
    }
}

</style>
