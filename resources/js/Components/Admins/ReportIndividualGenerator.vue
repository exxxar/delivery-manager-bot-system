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


        <button
            :disabled="report.loading"
            type="submit" class="btn btn-primary p-3 w-100">
            Сформировать отчёт
        </button>

    </form>
</template>

<script>


import {useBaseExports} from "@/stores/baseExports";

export default {
    name: 'ReportIndividualGenerator',
    props:["agentId"],
    data() {
        return {
            reportStore: useBaseExports(),
            report: {

                startDate: '',
                endDate: '',

            }
        }
    },
    methods: {
        generateReport() {
            const payload = {
                ...this.report, ...{
                    id: this.agentId
                }
            }

            this.reportStore.exportIndividual(payload)
            this.$emit('generate-report', payload)


        }
    }
}
</script>
