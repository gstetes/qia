import type { route as routeFn } from 'ziggy-js';

declare global {
    /** Helper de rotas do Ziggy, disponibilizado globalmente pela diretiva @routes. */
    const route: typeof routeFn;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof routeFn;
    }
}

declare module '@inertiajs/core' {
    interface InertiaConfig {
        sharedPageProps: SharedData;
        flashDataType: FlashData;
    }
}

/** Dados exibidos uma única vez após um redirecionamento (Inertia::flash no backend). */
export interface FlashData {
    status?: 'registered';
}

/** Props compartilhadas pelo HandleInertiaRequests em todas as páginas. */
export interface SharedData {
    [key: string]: unknown;
    appName: string;
    locale: string;
}
