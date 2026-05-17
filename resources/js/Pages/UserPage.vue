<script setup>
import UserList from "@/Components/Users/UserList.vue";
import BackBtn from "@/Components/BackBtn.vue";
</script>
<template>
    <div class="container-fluid py-0 px-0">
        <BackBtn/>
        <h4 class="mb-3">Список пользователей</h4>
        <UserList></UserList>
    </div>
</template>
<script>
export default {}
</script>
