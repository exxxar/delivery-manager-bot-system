<template>

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-5">

                <div class="card shadow-sm">

                    <div class="card-body p-4">

                        <h3 class="mb-4 text-center">
                            Вход
                        </h3>

                        <form @submit.prevent="submit">

                            <div class="mb-3">

                                <label class="form-label">
                                    Логин
                                </label>

                                <input
                                    v-model="form.login"
                                    class="form-control"
                                    type="text"
                                >
                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Пароль
                                </label>

                                <input
                                    v-model="form.password"
                                    class="form-control"
                                    type="password"
                                >
                            </div>

                            <button
                                class="btn btn-primary w-100"
                                :disabled="loading"
                            >
                                Войти
                            </button>

                        </form>

                        <hr>

                        <div id="telegram-login"></div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</template>

<script>

import { useUsersStore } from '@/stores/users'

export default {

    data()
    {
        return {

            form: {
                login: '',
                password: ''
            }
        }
    },

    computed: {

        loading()
        {
            return this.auth.loading;
        }
    },

    methods: {

        async submit()
        {
            try
            {
                await this.auth.login(this.form);

                window.location.href = "/pwa"
            }
            catch(e)
            {
                alert('Ошибка авторизации');
            }
        },

        telegramCallback(user)
        {
            this.auth.loginTelegram(user);
        },

        loadTelegram()
        {
            window.onTelegramAuth = this.telegramCallback;

            const script = document.createElement('script');

            script.src =
                'https://telegram.org/js/telegram-widget.js?22';

            script.async = true;

            script.setAttribute(
                'data-telegram-login',
                'YOUR_BOT_NAME'
            );

            script.setAttribute(
                'data-size',
                'large'
            );

            script.setAttribute(
                'data-userpic',
                'false'
            );

            script.setAttribute(
                'data-onauth',
                'onTelegramAuth(user)'
            );

            document
                .getElementById('telegram-login')
                .appendChild(script);
        }
    },

    mounted()
    {
        this.loadTelegram();
    },

    created()
    {
        this.auth = useUsersStore();
    }
}

</script>
