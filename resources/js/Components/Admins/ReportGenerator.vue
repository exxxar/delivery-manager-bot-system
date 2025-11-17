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


        <template v-if="!isSimple">

            <!-- Тип отчёта -->
            <div class="form-floating mb-2">
                <select
                    class="form-select"
                    id="reportType"
                    v-model="report.type"
                    required
                >
                    <option value="">Выберите тип отчёта</option>
                    <option value="admins">Администраторы</option>
                    <option value="sales">Продажи</option>
                    <option value="users">Пользователи</option>
                    <option value="suppliers">Поставщики</option>
                    <option value="agents">Агенты</option>
                </select>
                <label for="reportType">Тип отчёта</label>
            </div>

            <!-- Тип отчёта -->
            <div class="form-floating mb-2">
                <select
                    class="form-select"
                    id="reportType"
                    v-model="report.restriction"
                    required
                >
                    <option :value="null">Выберите тип ограничения</option>
                    <option value="without-agent">Без агента</option>
                    <option value="without-supplier">Без поставщика</option>
                    <option value="without-admin">Без администратора</option>
                    <option value="without-product">Без товара</option>
                </select>
                <label for="reportType">Ограничения</label>
            </div>

        </template>

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
            adminsStore: useAdminsStore(),
            report: {

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
