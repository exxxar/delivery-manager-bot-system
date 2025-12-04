<script setup>
import AdminList from "@/Components/Admins/AdminList.vue";
import BackBtn from "@/Components/BackBtn.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <BackBtn/>
        <h4 class="mb-3">Список администраторов</h4>
        <AdminList></AdminList>


        <nav class="navbar fixed-bottom p-3">
            <button
                type="button"
                @click="goTo('UserPage')"
                class="btn btn-primary p-3 w-100">Добавить администратора
            </button>
        </nav>
    </div>
</template>
<script>
export default {
    methods: {
        goTo(name) {
            this.$router.push({name: name})

        },
    }
}
</script>
