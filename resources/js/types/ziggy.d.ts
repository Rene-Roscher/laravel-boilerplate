import { Config, RouteParams } from 'ziggy-js';

declare global {
    function route(): Config;
    function route(name: string, params?: RouteParams<typeof name> | undefined, absolute?: boolean): string;
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        route: typeof route;
        __: (key: string, replace?: Record<string, string>) => string;
        __n: (key: string, number: number, replace?: Record<string, string>) => string;
    }
}
