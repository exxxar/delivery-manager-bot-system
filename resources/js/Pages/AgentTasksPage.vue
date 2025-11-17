<script setup>
import SaleList from "@/Components/Sales/SaleList.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <SaleList
            v-if="user"
            :agent-id="user.id"></SaleList>
    </div>
</template>
<script>
export default {
    name: 'TaskView',
    computed:{
        user(){
            return window.botUser
        },
    }
}
</script>
