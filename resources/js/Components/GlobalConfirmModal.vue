<script setup>
import { useModalStore } from '../stores/utillites/useConfitmModalStore'
const modal = useModalStore()
</script>

<template>
    <div class="modal fade" tabindex="-1" :class="{ show: modal.show }" style="display: block;z-index: 2000;" v-if="modal.show">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Подтверждение</h5>
                    <button type="button" class="btn-close" @click="modal.close()"></button>
                </div>
                <div class="modal-body">
                    <p v-html="modal.message"></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" @click="modal.confirm()">Подтвердить</button>
                    <button class="btn btn-danger" @click="modal.reject()">Отклонить</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
}
</script>

<style scoped>
.modal-backdrop {
    z-index: 1990 !important;
}
</style>
