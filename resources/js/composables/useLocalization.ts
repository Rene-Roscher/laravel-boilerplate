import { SharedData } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { loadLanguageAsync } from 'laravel-vue-i18n';
import { ref } from 'vue';

export function useLocalization() {
    const page = usePage<SharedData>();
    const locale = ref(page.props.locale);
    const currentLocale = ref<string>(locale.value);

    async function changeLanguage(language: string) {
        currentLocale.value = language;
        const currentPath = window.location.pathname;
        const newPath = currentPath.replace(`/${locale.value}`, `/${language}`);

        // await loadLanguageAsync(language);

        location.replace(newPath)

        // router.visit(newPath, {
        //     replace: true,
        //     preserveScroll: false,
        //     preserveState: false,
        // });
    }

    return {
        currentLocale,
        changeLanguage,
    };
}
