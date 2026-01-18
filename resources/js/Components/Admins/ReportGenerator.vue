<script setup>
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
import AgentList from "@/Components/Agents/AgentList.vue";
</script>
<template>
    <form @submit.prevent="generateReport">

        <template v-if="tab==='main'">
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



            <div
                class="form-check form-switch mb-2">
                <input
                    class="form-check-input"
                    type="checkbox"
                    v-model="need_more_options"
                    :id="`need_more_options`"
                />
                <label class="form-check-label" :for="`need_more_options`">
                   Больше параметров отчета
                </label>
            </div>

            <template v-if="need_more_options">


                <button
                    @click="tab='suppliers'"
                    type="button"
                    class="btn btn-outline-light text-primary p-3 mb-2 w-100 ">
                <span v-if="(report.selected_suppliers||[]).length===0">
                    Выбрать поставщиков
                </span>
                    <span v-else>
                    Выбрано <span class="fw-bold">{{report.selected_suppliers.length}}</span> поставщиков
                </span>

                </button>

                <button
                    @click="tab='agents'"
                    type="button"
                    class="btn btn-outline-light text-primary p-3 mb-2 w-100 ">
                <span v-if="(report.selected_agents||[]).length===0">
                    Выбрать администраторов
                </span>
                    <span v-else>
                    Выбрано <span class="fw-bold">{{report.selected_agents.length}}</span> админов
                </span>

                </button>

                <div class="form-floating mb-2">
                    <select
                        v-model="report.result_type"
                        class="form-select" id="floatingSelect" aria-label="Floating label select example">

                        <option value="0">Классический отчет</option>
                        <option value="1">Расширенный отчет</option>
                    </select>
                    <label for="floatingSelect">Как вывести результат</label>
                </div>

            </template>



            <button
                :disabled="report.loading"
                type="submit" class="btn btn-primary p-3 w-100">
                Сформировать отчёт
            </button>
        </template>

        <template v-if="tab==='suppliers'">
            <SupplierList v-on:select="selectSuppliers"></SupplierList>

            <div class="btn-group w-100"
                 style="position: sticky;bottom: 20px;"
                 role="group" aria-label="Basic example">
                <button
                    @click="tab='main'"
                    class="btn btn-primary p-3">Выбрать
                    <span class="fw-bold"
                          v-if="(report.selected_suppliers||[]).length>0">
                        ({{report.selected_suppliers.length}})
                    </span>
                </button>
                <button type="button"
                        @click="resetSuppliers"
                        class="btn btn-light">Сбросить</button>
            </div>
        </template>

        <template v-if="tab==='agents'">
            <AgentList v-on:select="selectAgents"></AgentList>
            <div class="btn-group w-100"
                 style="position: sticky;bottom: 20px;"
                 role="group" aria-label="Basic example">
                <button
                    @click="tab='main'"
                    class="btn btn-primary p-3">Выбрать
                    <span class="fw-bold"
                          v-if="(report.selected_agents||[]).length>0">
                        ({{report.selected_agents.length}})
                    </span>
                </button>
                <button type="button"
                        @click="resetAgents"
                        class="btn btn-light">Сбросить</button>
            </div>
        </template>

    </form>
</template>

<script>


export default {
    name: 'ReportGenerator',
    components: {},

    props: ["type", "isSimple"],
    data() {
        return {
            need_more_options: false,
            tab: 'main',
            report: {
                result_type: 0,
                startDate: '',
                endDate: '',
                type: this.type || '',
                restriction: null,
                selected_suppliers: [],
                selected_agents: [],
            }
        }
    },
    watch:{
      'need_more_options':function (){
          if (!this.need_more_options){
              this.report.selected_suppliers = []
              this.report.selected_agents = []
          }
      }
    },
    methods: {
        selectSuppliers(ids) {
            this.report.selected_suppliers = ids
        },
        selectAgents(ids) {
            this.report.selected_agents = ids
        },
        resetSuppliers(){
          this.report.selected_suppliers = []
          this.tab = 'main'
        },
        resetAgents(){
            this.report.selected_agents = []
            this.tab = 'main'
        },
        generateReport() {
            const payload = {...this.report}
            this.$emit('generate-report', payload)
            // Закрыть модалку после отправки

        }
    }
}
</script>
