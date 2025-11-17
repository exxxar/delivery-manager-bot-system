import './bootstrap';
import '../css/app.css';

import {createApp, h} from 'vue';
import {createPinia} from 'pinia'
import {createInertiaApp} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy';
import router from './router'
import {useAlertStore} from './stores/utillites/useAlertStore'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({el, App, props, plugin}) {

        const app = createApp({render: () => h(App, props)})

        app.use(createPinia())
        app.config.globalProperties.$notify = useAlertStore()

        return app
            .use(plugin)
            .use(ZiggyVue)
            .use(router)

            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
