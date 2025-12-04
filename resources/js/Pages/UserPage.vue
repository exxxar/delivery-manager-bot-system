<script setup>
import UserList from "@/Components/Users/UserList.vue";
import BackBtn from "@/Components/BackBtn.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <BackBtn/>
        <h4 class="mb-3">Список пользователей</h4>
        <UserList></UserList>
    </div>
</template>
<script>
export default {}
</script>
