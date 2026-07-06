<template>
    <div class="container-fluid py-0 px-0">
        <BackBtn/>

        <h4 class="mb-3">Мои отчеты</h4>

        <ReportList v-if="!loading"></ReportList>
    </div>
</template>

<script>
import ReportList from "@/Components/Reports/ReportList.vue";
import BackBtn from "@/Components/BackBtn.vue";

export default {
    name: 'ReportsPage',
    components: { ReportList, BackBtn },
    data() {
        return {
            loading: false,
        }
    },
    mounted() {
    },
    methods: {
    }
}
</script>
