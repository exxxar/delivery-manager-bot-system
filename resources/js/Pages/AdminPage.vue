<template>
    <main class="admins-page">
        <div class="page-shell">
            <header class="page-header">
                <div class="header-top">
                    <BackBtn />
                    <span class="header-icon" aria-hidden="true">
                        <i class="fa-solid fa-user-shield"></i>
                    </span>
                </div>

                <div class="heading-copy">
                    <p class="eyebrow">УПРАВЛЕНИЕ ДОСТУПОМ</p>
                    <h1>Администраторы</h1>
                    <p class="page-description">Управляйте администраторами и их рабочими статусами</p>
                </div>
            </header>

            <section class="admins-content" aria-label="Список администраторов">
                <div class="section-heading">
                    <div>
                        <h2>Команда</h2>
                        <p>Список администраторов системы</p>
                    </div>
                    <span class="section-icon"><i class="fa-solid fa-users-gear"></i></span>
                </div>

                <AdminList />
            </section>
        </div>

        <div class="bottom-action">
            <div class="bottom-action-inner">
                <div class="action-hint">
                    <span class="action-hint-icon"><i class="fa-solid fa-user-plus"></i></span>
                    <div>
                        <strong>Нужен новый администратор?</strong>
                        <span>Перейдите к созданию пользователя</span>
                    </div>
                </div>
                <button type="button" class="add-admin-button" @click="goTo('UserPage')">
                    <i class="fa-solid fa-plus"></i>
                    <span>Добавить старшего администратора</span>
                    <i class="fa-solid fa-arrow-right button-arrow"></i>
                </button>
            </div>
        </div>
    </main>
</template>

<script setup>
import AdminList from "@/Components/Admins/AdminList.vue";
import BackBtn from "@/Components/BackBtn.vue";
</script>

<script>
export default {
    name: "AdminsPage",
    methods: {
        goTo(name) {
            this.$router.push({ name });
        },
    },
};
</script>

<style scoped>
.admins-page {
    --admin-blue: #185adb;
    --admin-blue-dark: #123b85;
    --admin-red: #df3b4b;
    --admin-ink: #18243a;
    --admin-muted: #748198;
    --admin-border: #e5ebf4;
    min-height: 100%;
    padding: 0 0 150px;
    color: var(--admin-ink);
    background: #f5f7fb;
}

.page-shell {
    width: 100%;
    max-width: 1180px;
    margin: 0 auto;
    padding: 20px 20px 28px;
}

.page-header {
    position: relative;
    overflow: hidden;
    padding: 22px 24px 28px;
    border: 1px solid rgba(220, 230, 246, .9);
    border-radius: 24px;
    background: linear-gradient(120deg, #fff 0%, #f8faff 68%, #edf3ff 100%);
    box-shadow: 0 12px 34px rgba(27, 57, 108, .06);
}

.page-header::after {
    position: absolute;
    right: -42px;
    bottom: -105px;
    width: 230px;
    height: 230px;
    border: 35px solid rgba(24, 90, 219, .055);
    border-radius: 50%;
    content: "";
    pointer-events: none;
}

.header-top {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 36px;
}

.header-icon,
.section-icon,
.action-hint-icon {
    display: inline-flex;
    flex: 0 0 auto;
    align-items: center;
    justify-content: center;
}

.header-icon {
    width: 46px;
    height: 46px;
    border: 1px solid #dce7ff;
    border-radius: 15px;
    color: var(--admin-blue);
    background: #edf3ff;
    font-size: 20px;
}

.heading-copy {
    position: relative;
    z-index: 1;
    margin-top: 20px;
}

.eyebrow {
    margin: 0 0 8px;
    color: var(--admin-blue);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .14em;
}

.heading-copy h1 {
    margin: 0;
    color: var(--admin-ink);
    font-size: clamp(26px, 4vw, 34px);
    font-weight: 800;
    letter-spacing: -.035em;
}

.page-description {
    margin: 9px 0 0;
    color: var(--admin-muted);
    font-size: 14px;
    line-height: 1.5;
}

.admins-content {
    margin-top: 22px;
    padding: 22px;
    border: 1px solid var(--admin-border);
    border-radius: 22px;
    background: #fff;
    box-shadow: 0 10px 28px rgba(27, 57, 108, .045);
}

.section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
    padding-bottom: 17px;
    border-bottom: 1px solid #edf0f6;
}

.section-heading h2 {
    margin: 0;
    font-size: 19px;
    font-weight: 800;
}

.section-heading p {
    margin: 5px 0 0;
    color: var(--admin-muted);
    font-size: 13px;
}

.section-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    color: var(--admin-red);
    background: #fff0f1;
    font-size: 17px;
}

.bottom-action {
    position: fixed;
    z-index: 1020;
    right: 0;
    bottom: 0;
    left: 0;
    padding: 14px 20px calc(14px + env(safe-area-inset-bottom));
    border-top: 1px solid rgba(225, 231, 241, .9);
    background: rgba(255, 255, 255, .94);
    box-shadow: 0 -10px 30px rgba(23, 42, 77, .07);
    backdrop-filter: blur(16px);
}

.bottom-action-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    width: 100%;
    max-width: 1180px;
    margin: 0 auto;
}

.action-hint {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.action-hint-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    color: var(--admin-blue);
    background: #edf3ff;
}

.action-hint strong,
.action-hint span:not(.action-hint-icon) {
    display: block;
}

.action-hint strong {
    color: var(--admin-ink);
    font-size: 13px;
    font-weight: 750;
}

.action-hint div > span {
    margin-top: 3px;
    color: var(--admin-muted);
    font-size: 12px;
}

.add-admin-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 11px;
    min-height: 50px;
    padding: 12px 20px;
    border: 0;
    border-radius: 15px;
    color: #fff;
    background: linear-gradient(135deg, var(--admin-blue) 0%, var(--admin-blue-dark) 100%);
    box-shadow: 0 7px 17px rgba(24, 90, 219, .22);
    font-size: 14px;
    font-weight: 750;
    transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
}

.add-admin-button:hover {
    transform: translateY(-2px);
    background: linear-gradient(135deg, #2469ef 0%, #16479b 100%);
    box-shadow: 0 10px 22px rgba(24, 90, 219, .28);
}

.add-admin-button:active {
    transform: translateY(0);
}

.add-admin-button:focus-visible {
    outline: 3px solid rgba(24, 90, 219, .28);
    outline-offset: 3px;
}

.button-arrow {
    margin-left: 3px;
    color: #ffdce0;
}

@media (max-width: 640px) {
    .page-shell {
        padding: 12px 12px 20px;
    }

    .page-header {
        padding: 16px 17px 22px;
        border-radius: 19px;
    }

    .heading-copy {
        margin-top: 16px;
    }

    .page-description {
        font-size: 13px;
    }

    .admins-content {
        margin-top: 14px;
        padding: 15px 12px;
        border-radius: 18px;
    }

    .bottom-action {
        padding: 10px 12px calc(10px + env(safe-area-inset-bottom));
    }

    .bottom-action-inner {
        display: block;
    }

    .action-hint {
        display: none;
    }

    .add-admin-button {
        width: 100%;
        min-height: 52px;
        padding: 13px 14px;
        font-size: 13px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .add-admin-button {
        transition: none;
    }
}
</style>
