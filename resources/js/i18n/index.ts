import { createI18n, useI18n } from 'vue-i18n';
import ptBR from './locales/pt-BR.json';

/** Estrutura das mensagens, derivada do idioma padrão. Novos idiomas devem seguir as mesmas chaves. */
export type MessageSchema = typeof ptBR;

type Paths<T> = {
    [K in keyof T & string]: T[K] extends Record<string, unknown> ? `${K}.${Paths<T[K]>}` : K;
}[keyof T & string];

/** Todas as chaves de tradução válidas, ex.: "auth.login.title". */
export type TranslationKey = Paths<MessageSchema>;

export type Translate = (key: TranslationKey, named?: Record<string, unknown>) => string;

export const DEFAULT_LOCALE = 'pt-BR';

const messages = {
    'pt-BR': ptBR,
} satisfies Record<string, MessageSchema>;

export type Locale = keyof typeof messages;

const isSupportedLocale = (locale: string): locale is Locale => locale in messages;

export function setupI18n(locale: string) {
    return createI18n<[MessageSchema], Locale, false>({
        legacy: false,
        locale: isSupportedLocale(locale) ? locale : DEFAULT_LOCALE,
        fallbackLocale: DEFAULT_LOCALE,
        messages,
    });
}

/**
 * Tradução com chaves verificadas em tempo de compilação. Use no lugar do `useI18n` do vue-i18n,
 * cujo `t` aceita qualquer string e só exibiria a chave crua na tela em caso de erro de digitação.
 */
export function useTranslation(): { t: Translate } {
    const { t } = useI18n();

    return {
        t: (key, named) => (named ? t(key, named) : t(key)),
    };
}
