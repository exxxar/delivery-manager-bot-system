<script setup>
import { Head } from '@inertiajs/vue3'
import SupplierMyDeliveries from '@/Components/Suppliers/SupplierMyDeliveries.vue'
import BackBtn from "@/Components/BackBtn.vue";
</script>

<template>
    <Head>
        <title>Мои доставки</title>
    </Head>
    <div class="container-lg py-3">
        <BackBtn/>
        <SupplierMyDeliveries />
    </div>
</template>
