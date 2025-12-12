<template>
    <form @submit.prevent="generateReport">


        <div class="form-floating mb-2">
            <input
                type="date"
                class="form-control"
                id="startDate"
                v-model="report.startDate"
                required
            />
            <label for="startDate">Дата начала периода</label>
        </div>

        <!-- Дата окончания -->
        <div class="form-floating mb-2">
            <input
                type="date"
                class="form-control"
                id="endDate"
                v-model="report.endDate"
                required
            />
            <label for="endDate">Дата окончания периода</label>
        </div>

        <div class="form-floating mb-2">
            <select
                v-model="report.result_type"
                class="form-select" id="floatingSelect" aria-label="Floating label select example">

                <option value="0">Классический отчет</option>
                <option value="1">Расширенный отчет</option>
            </select>
            <label for="floatingSelect">Как вывести результат</label>
        </div>

        <button
            :disabled="adminsStore.loading"
            type="submit" class="btn btn-primary p-3 w-100">
            Сформировать отчёт
        </button>

    </form>
</template>

<script>


import {useAdminsStore} from "@/stores/admins";

export default {
    name: 'ReportGenerator',
    props: ["type", "isSimple"],
    data() {
        return {
            need_more_options: false,
            adminsStore: useAdminsStore(),
            report: {
                result_type:0,
                startDate: '',
                endDate: '',
                type: this.type || '',
                restriction: null,
            }
        }
    },
    methods: {
        generateReport() {
            const payload = {...this.report}
            this.$emit('generate-report', payload)
            // Закрыть модалку после отправки

        }
    }
}
</script>
