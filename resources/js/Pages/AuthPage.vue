<template>
    <main class="auth-page">
        <div class="auth-background" aria-hidden="true">
            <span class="auth-orb auth-orb--blue"></span>
            <span class="auth-orb auth-orb--red"></span>
            <span class="auth-grid"></span>
        </div>

        <section class="auth-shell">
            <header class="auth-heading">
                <div class="auth-mark" aria-hidden="true">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <p class="auth-eyebrow">Добро пожаловать</p>
                <h1>Вход в систему</h1>
                <p class="auth-description">Авторизуйтесь, чтобы продолжить работу</p>
            </header>

            <div class="auth-card">
                <AuthForm />
            </div>

            <p class="auth-footer">
                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                Защищённое подключение
            </p>
        </section>
    </main>
</template>

<script setup>
import AuthForm from "@/Components/AuthForm.vue";
</script>

<style scoped>
.auth-page {
    --auth-navy: #172b4d;
    --auth-blue: #1769e0;
    --auth-red: #e04452;
    position: relative;
    isolation: isolate;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    min-height: 100dvh;
    overflow: hidden;
    padding: 36px 20px;
    background: linear-gradient(145deg, #f7faff 0%, #eef4ff 55%, #fff7f7 100%);
    color: var(--auth-navy);
}

.auth-background,
.auth-grid {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.auth-background { z-index: -1; overflow: hidden; }
.auth-grid {
    opacity: .28;
    background-image: linear-gradient(rgba(23, 105, 224, .07) 1px, transparent 1px),
    linear-gradient(90deg, rgba(23, 105, 224, .07) 1px, transparent 1px);
    background-size: 36px 36px;
    mask-image: linear-gradient(to bottom, black, transparent 85%);
}

.auth-orb {
    position: absolute;
    display: block;
    width: 340px;
    height: 340px;
    border-radius: 50%;
    filter: blur(2px);
    opacity: .14;
}
.auth-orb--blue { top: -150px; left: -100px; background: var(--auth-blue); }
.auth-orb--red { right: -130px; bottom: -170px; background: var(--auth-red); }

.auth-shell {
    width: 100%;
    max-width: 500px;
    margin: auto;
    animation: auth-rise .45s ease both;
}

.auth-heading { margin-bottom: 26px; text-align: center; }
.auth-mark {
    display: grid;
    place-items: center;
    width: 64px;
    height: 64px;
    margin: 0 auto 18px;
    border: 1px solid rgba(23, 105, 224, .12);
    border-radius: 20px;
    background: #fff;
    color: var(--auth-blue);
    box-shadow: 0 12px 30px rgba(23, 55, 100, .1);
    font-size: 27px;
}
.auth-eyebrow {
    margin: 0 0 7px;
    color: var(--auth-blue);
    font-size: .76rem;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
}
.auth-heading h1 {
    margin: 0;
    color: var(--auth-navy);
    font-size: clamp(1.8rem, 5vw, 2.35rem);
    font-weight: 800;
    letter-spacing: -.04em;
}
.auth-description { margin: 10px 0 0; color: #71809a; font-size: .96rem; }

.auth-card {
    padding: clamp(22px, 5vw, 36px);
    border: 1px solid rgba(205, 217, 235, .8);
    border-radius: 24px;
    background: rgba(255, 255, 255, .96);
    box-shadow: 0 24px 70px rgba(28, 52, 91, .12);
}

/* Согласуем поля формы с оформлением страницы, не вмешиваясь в её логику. */
.auth-card :deep(.form-control),
.auth-card :deep(.form-select) {
    min-height: 48px;
    border-color: #dbe4f0;
    border-radius: 12px;
    box-shadow: none;
}
.auth-card :deep(.form-control:focus),
.auth-card :deep(.form-select:focus) {
    border-color: var(--auth-blue);
    box-shadow: 0 0 0 3px rgba(23, 105, 224, .12);
}
.auth-card :deep(.btn-primary) {
    min-height: 48px;
    border: 0;
    border-radius: 12px;
    background: linear-gradient(110deg, #1769e0, #2855c5);
    box-shadow: 0 8px 18px rgba(23, 105, 224, .2);
    font-weight: 700;
    transition: transform .2s ease, box-shadow .2s ease;
}
.auth-card :deep(.btn-primary:hover) {
    transform: translateY(-1px);
    box-shadow: 0 11px 22px rgba(23, 105, 224, .27);
}
.auth-card :deep(a) { color: var(--auth-blue); }

.auth-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin: 22px 0 0;
    color: #8290a5;
    font-size: .8rem;
}
.auth-footer i { color: #29966b; }

@keyframes auth-rise {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 480px) {
    .auth-page { padding: 24px 14px; }
    .auth-heading { margin-bottom: 20px; }
    .auth-mark { width: 56px; height: 56px; border-radius: 17px; font-size: 24px; }
    .auth-card { border-radius: 20px; }
}

@media (prefers-reduced-motion: reduce) {
    .auth-shell { animation: none; }
    .auth-card :deep(.btn-primary) { transition: none; }
}
</style>
