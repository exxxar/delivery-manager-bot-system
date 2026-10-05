<!-- @/Pages/SupplierRegister.vue -->

<script setup>
import { Head } from '@inertiajs/vue3'
import SupplierRegisterForm from '@/Components/Suppliers/SupplierRegisterForm.vue'
</script>

<template>
    <Head>
        <title>Регистрация поставщика</title>
    </Head>

    <SupplierRegisterForm :token="$page.props.token" />
</template>
