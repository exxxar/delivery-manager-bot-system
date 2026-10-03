<script setup>

import AdminRegisterForm from '@/Components/Admins/AdminRegisterForm.vue'


</script>
<template>



            <div class="container py-4" style="max-width: 600px;">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-user-shield fs-1 text-primary mb-2"></i>
                    <h3 class="fw-bold">Регистрация администратора</h3>
                    <p class="text-muted">Заполните форму для создания учётной записи</p>
                </div>

                <AdminRegisterForm :token="$page.props.token"/>
            </div>




</template>

<script>


export default {
    data() {

    },
    created() {

    },
    computed: {

    },

    mounted() {

    },
    methods: {

    }

}
</script>


