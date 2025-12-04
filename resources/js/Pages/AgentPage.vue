<script setup>
import AgentList from "@/Components/Agents/AgentList.vue";
import BackBtn from "@/Components/BackBtn.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <BackBtn/>
        <h4 class="mb-3">Список торговых представителей</h4>

        <AgentList></AgentList>

        <nav class="navbar fixed-bottom p-3">
            <button
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#createCategoryModal"
                class="btn w-100 p-3 btn-primary"
            >
                Добавить торгового представителя
            </button>
        </nav>
    </div>
</template>
<script>
export default {

}
</script>
