import type { trans, transChoice } from 'laravel-vue-i18n';
import { route as routeFn } from 'ziggy-js';

declare global {
    const route: typeof routeFn;
    const __: typeof trans;
    const __n: typeof transChoice;

    function __(key: string, replace?: Record<string, string>): string;
    function __n(key: string, number: number, replace?: Record<string, string>): string;
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        __: (key: string, replace?: Record<string, string>) => string;
        __n: (key: string, number: number, replace?: Record<string, string>) => string;
    }
}
