import { z } from 'zod/mini';
import type { Translate } from '@/i18n';
import { emailField, PASSWORD_MIN_LENGTH, stringField } from '../fields';

export function createRegisterSchema(t: Translate) {
    return z
        .object({
            name: stringField(t, 'name', { trim: true }),
            email: emailField(t),
            password: stringField(t, 'password', { min: PASSWORD_MIN_LENGTH }),
            password_confirmation: stringField(t, 'passwordConfirmation'),
        })
        .check(
            z.refine((data) => data.password === data.password_confirmation, {
                message: t('validation.passwordMismatch'),
                path: ['password_confirmation'],
            }),
        );
}

export type RegisterForm = z.input<ReturnType<typeof createRegisterSchema>>;
