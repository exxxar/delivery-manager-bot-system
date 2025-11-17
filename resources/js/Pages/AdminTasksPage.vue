<script setup>
import SaleList from "@/Components/Sales/SaleList.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <SaleList
            v-if="user"
            :admin-id="user.id"></SaleList>
    </div>
</template>
<script>
import {useUsersStore} from "@/stores/users";


export default {
    name: 'TaskView',
    data(){
        return {
            userStore: useUsersStore()
        }
    },
    computed:{
        user(){
            return this.userStore.self || null
        },
    }
}
</script>
