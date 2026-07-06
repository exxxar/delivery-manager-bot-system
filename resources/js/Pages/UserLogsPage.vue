<template>
    <div class="container-fluid py-0 px-0">
        <BackBtn/>

        <h4 class="mb-3">История действий</h4>

        <UserLogList v-if="!loading"></UserLogList>
    </div>
</template>

<script>
import UserLogList from "@/Components/UserLogs/UserLogList.vue";
import BackBtn from "@/Components/BackBtn.vue";

export default {
    name: 'UserLogsPage',
    components: { UserLogList, BackBtn },
    data() {
        return {
            loading: false,
        }
    }
}
</script>
