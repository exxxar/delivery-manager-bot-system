<template>
    <ul class="list-group" v-if="percentages.length>0">
        <li
            v-for="item in percentages" :key="item.id"
            v-bind:class="{'bg-danger':item.deleted_at}"
            class="list-group-item d-flex justify-content-between align-items-center">
            <div  style="word-break: break-all;" class="small">
                <span
                    v-bind:class="{'bg-success':item.is_active, 'bg-danger': !item.is_active}"
                    class="badge mx-1">   {{ item.percentage || 0 }}%</span>
                {{ item.user?.name || '-' }}
            </div>

            <!-- Dropdown кнопка -->
            <div class="dropdown" v-if="forSelect">
                <button class="btn btn-sm" type="button"
                        data-bs-toggle="dropdown">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">


                        <li><a class="dropdown-item" href="#" @click.prevent="openEdit(item)">Редактировать</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="javascript:void(0)"
                               @click.prevent="confirmDelete(item)">Удалить</a></li>

                </ul>
            </div>
        </li>
    </ul>
    <p class="alert alert-light mb-0" v-else>
        Проценты еще не добавлены
    </p>


</template>
<script>


import {useAgentsStore} from "@/stores/agents";
import {useUsersStore} from "@/stores/users";
import {useModalStore} from "@/stores/utillites/useConfitmModalStore.ts";


export default {
    name: 'PercentageList',

    props: ["forSelect", "agentId"],
    data() {
        return {
            percentages: [],
            agentStore: useAgentsStore(),
            userStore: useUsersStore(),
            modalStore: useModalStore(),
            selected: null,
        }
    },

    computed: {
        user() {
            return this.userStore.self || null
        },
    },
    created() {
        this.fetchPercentage()

    },
    methods: {
        openEdit(item) {
            this.$emit("select", item)

        },
        async fetchPercentage() {
            await this.agentStore.fetchPercentage({
                agent_id: this.agentId
            }).then(resp => {
                this.percentages = resp
            })
        },


        confirmDelete(percentage) {

           this.selected = null
            this.$nextTick(()=>{
                this.selected = percentage
                this.modalStore.open(
                    `Вы уверены, что хотите удалить ${this.selected?.percentage}?`,
                    () => {
                        percentage.deleted_at = new Date()

                        this.agentStore.removePercentage({
                            agent_id: this.agentId,
                            id: this.selected.id
                        }).then(() => {
                            this.fetchPercentage()
                        })
                    },
                    () => this.modalStore.close()
                )
            })
        },


    }
}
</script>
