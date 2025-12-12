<script setup>
import BackBtn from "@/Components/BackBtn.vue";
import BirthdayList from "@/Components/Birthdays/BirthdayList.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <BackBtn/>
        <BirthdayList></BirthdayList>

        <button
            type="button"
            :disabled="jobStore.loading"
            class="btn btn-outline-light text-primary w-100 p-3"
            @click="exportBirthdaysList()">
            Выгрузить список в эксель
        </button>
    </div>
</template>

<script>
import {useBaseExports} from "@/stores/baseExports";

export default {
    name: 'BirthdayPage',
    data(){
      return {
          jobStore: useBaseExports(),
      }
    },
    methods:{
        exportBirthdaysList() {
            this.jobStore.exportBirthdaysList()
        },
    }
}
</script>
