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
    </main>
</template>

<script setup>
import AgentList from "@/Components/Agents/AgentList.vue";
import BackBtn from "@/Components/BackBtn.vue";
</script>

<script>
export default {
    name: 'AgentsPage',
    methods: {
        goTo(name) {
            this.$router.push({ name });
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
</style>
