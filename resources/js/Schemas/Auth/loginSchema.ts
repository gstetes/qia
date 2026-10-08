import { z } from 'zod/mini';
import type { Translate } from '@/i18n';
import { emailField, stringField } from '../fields';

export function createLoginSchema(t: Translate) {
    return z.object({
        email: emailField(t),
        password: stringField(t, 'password'),
    });
}

export type LoginForm = z.input<ReturnType<typeof createLoginSchema>>;
