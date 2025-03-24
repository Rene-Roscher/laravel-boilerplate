import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from '@vue/server-renderer';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, h } from 'vue';
import { route as ziggyRoute } from 'ziggy-js';
import {trans, transChoice} from "laravel-vue-i18n";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => `${title} - ${appName}`,
        resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
        setup({ App, props, plugin }) {
            const app = createSSRApp({ render: () => h(App, props) });

            // Configure Ziggy for SSR...
            const ziggyConfig = {
                ...page.props.ziggy,
                location: new URL(page.props.ziggy.location),
            };

            // Create route function...
            const route = (name: string, params?: any, absolute?: boolean) => ziggyRoute(name, params, absolute, ziggyConfig);
            const __ = (key: string, replace = {}) => (key ? trans(key, replace) : '');
            const __n = (key: string, number: number, replace = {}) => transChoice(key, number, replace);

            // Make route function available globally...
            app.config.globalProperties.route = route;
            app.config.globalProperties.__ = __;
            app.config.globalProperties.__n = __n;

            // Make route function available globally for SSR...
            if (typeof window === 'undefined') {
                global.route = route;
                global.__ = __;
                global.__n = __n;
            }

            app.use(plugin);

            return app;
        },
    }),
);
