<script setup>
import { Head } from '@inertiajs/vue3'
import AdminRegisterForm from '@/Components/Admins/AdminRegisterForm.vue'
</script>

<template>
    <Head>
        <title>Регистрация администратора</title>
    </Head>

    <div class="register-page-wrapper position-relative min-vh-100 d-flex align-items-center py-5">
        <!-- 🔹 Декоративные круги на фоне (теперь на всю страницу) -->
        <div class="decoration-circle circle-1"></div>
        <div class="decoration-circle circle-2"></div>
        <div class="decoration-circle circle-3"></div>

        <div class="container position-relative" style="z-index: 10;">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8 col-xl-7 col-xxl-6">

                    <!-- 🔹 Главный заголовок страницы -->
                    <div class="text-center mb-4">
                        <div class="brand-icon-large d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <h2 class="fw-bold mb-1">Регистрация администратора</h2>
                        <p class="text-muted">Заполните форму для создания учётной записи</p>
                    </div>

                    <!-- 🔹 Сама форма -->
                    <AdminRegisterForm :token="$page.props.token" />

                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* 🔹 Фон и декоративные элементы */
.register-page-wrapper {
    overflow: hidden;
    background-color: #f8f9fa;
}

.decoration-circle {
    position: fixed;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.15;
    z-index: 0;
    pointer-events: none;
}

.circle-1 {
    width: 400px;
    height: 400px;
    background: #0d6efd;
    top: -100px;
    left: -100px;
    animation: float1 20s ease-in-out infinite;
}

.circle-2 {
    width: 300px;
    height: 300px;
    background: #6610f2;
    bottom: -50px;
    right: -50px;
    animation: float2 25s ease-in-out infinite;
}

.circle-3 {
    width: 200px;
    height: 200px;
    background: #20c997;
    top: 50%;
    right: 20%;
    animation: float3 18s ease-in-out infinite;
}

@keyframes float1 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(50px, 50px) scale(1.1); }
}

@keyframes float2 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(-40px, -40px) scale(0.9); }
}

@keyframes float3 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(30px, -30px) scale(1.15); }
}

/* 🔹 Большая иконка в заголовке страницы */
.brand-icon-large {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-radius: 1.25rem;
    color: white;
    font-size: 2.5rem;
    box-shadow: 0 15px 35px rgba(13, 110, 253, 0.3);
    position: relative;
    overflow: hidden;
}

.brand-icon-large::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}
</style>
