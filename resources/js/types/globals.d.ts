import type { route as routeFn } from 'ziggy-js';

declare global {
    const route: typeof routeFn;
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        __: (key: string, replace?: Record<string, string>) => string;
        __n: (key: string, number: number, replace?: Record<string, string>) => string;
    }
}
