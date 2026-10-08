import { z } from 'zod/mini';
import type { MessageSchema, Translate } from '@/i18n';

// Mantenha estes limites alinhados com as regras do backend (Form Requests e Password::defaults()).
export const MAX_STRING_LENGTH = 255;
export const PASSWORD_MIN_LENGTH = 8;

/** Chave de um campo em `fields.*` das traduções. */
export type FieldKey = keyof MessageSchema['fields'];

interface StringFieldOptions {
    trim?: boolean;
    min?: number;
    max?: number;
}

const attribute = (t: Translate, field: FieldKey) => t(`fields.${field}.attribute`);

/**
 * Campo de texto obrigatório.
 */
export function stringField(t: Translate, field: FieldKey, { trim = false, min, max = MAX_STRING_LENGTH }: StringFieldOptions = {}) {
    const name = attribute(t, field);

    return z.string().check(
        ...(trim ? [z.trim()] : []),
        z.minLength(1, t('validation.required', { attribute: name })),
        ...(min ? [z.minLength(min, t('validation.min', { attribute: name, min }))] : []),
        z.maxLength(max, t('validation.max', { attribute: name, max })),
    );
}

export function emailField(t: Translate) {
    return z.pipe(
        stringField(t, 'email', { trim: true }),
        z.email(t('validation.email', { attribute: attribute(t, 'email') })),
    );
}
