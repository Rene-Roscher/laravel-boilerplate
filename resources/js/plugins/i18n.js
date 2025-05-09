import { i18nVue, trans, transChoice } from 'laravel-vue-i18n';

export default {
    install(app) {
        app.config.globalProperties.__ = (key, replace = {}) => (key ? trans(key, replace) : '');
        app.config.globalProperties.__n = (key, number, replace = {}) => transChoice(key, number, replace);

        app.provide('__', app.config.globalProperties.__);
        app.provide('__n', app.config.globalProperties.__n);

        app.use(i18nVue, {
            resolve: async (lang) => {
                const langFiles = import.meta.glob('../../../lang/*.json', { eager: true });

                let modules;
                switch (lang) {
                    case 'en':
                        modules = import.meta.glob('../../../lang/json/en/**/*.json', { eager: true });
                        console.log(modules)
                        break;
                    case 'de':
                        modules = import.meta.glob('../../../lang/json/de/**/*.json', { eager: true });
                        break;
                }

                const messages = langFiles[`../../../lang/${lang}.json`] || {};

                for (const path in modules) {
                    const regex = new RegExp(`../lang/json/${lang}/(.+)\\.json$`);
                    const match = path.match(regex);
                    if (match) {
                        const prefix = match[1].replaceAll('/', '.');
                        const moduleMessages = modules[path].default;
                        for (const key in moduleMessages) {
                            messages.default[`${prefix}.${key}`] = moduleMessages[key];
                        }
                    }
                }

                return messages;
            },
        });
    },
};
