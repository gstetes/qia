import { useForm, type InertiaForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import type { z } from 'zod/mini';

type FormFields = Record<string, string>;

/**
 * Formulário Inertia validado no cliente por um schema zod antes do envio.
 * O schema é avaliado por inteiro para suportar regras entre campos (ex.: confirmação de senha).
 *
 * Erros do cliente e do servidor compartilham `form.errors`, exibidos pelo mesmo FormMessage.
 * A validação do cliente só remove os erros que ela mesma criou: um erro do servidor (ex.: e-mail já
 * cadastrado) permanece até que o usuário altere o valor daquele campo.
 */
export function useValidatedForm<TForm extends FormFields>(schema: z.ZodMiniType<unknown, TForm>, data: TForm) {
    type Field = keyof TForm & string;

    // Os tipos condicionais do useForm não se resolvem para um TForm genérico. Internamente o formulário
    // é tratado como FormFields e exposto à página com o tipo concreto do schema.
    const form = useForm<FormFields>(data);
    const fields = Object.keys(data) as Field[];
    const clientErrors = new Set<Field>();

    const issuesByField = () => {
        const result = schema.safeParse(form.data());
        const issues: Partial<Record<Field, string>> = {};

        for (const issue of result.error?.issues ?? []) {
            issues[issue.path[0] as Field] ??= issue.message;
        }

        return issues;
    };

    const applyErrors = (targets: Field[], issues: Partial<Record<Field, string>>) => {
        for (const field of targets) {
            const message = issues[field];

            if (message) {
                form.setError(field, message);
                clientErrors.add(field);
            } else if (clientErrors.delete(field)) {
                form.clearErrors(field);
            }
        }

        return targets.every((field) => !issues[field]);
    };

    const validateField = (field: Field) => applyErrors([field], issuesByField());

    const validate = () => applyErrors(fields, issuesByField());

    // Revalida os campos alterados que tinham erro e os erros do cliente, que podem depender de outros campos.
    watch(
        () => fields.map((field) => form[field]),
        (values, previous) => {
            const changed = fields.filter((field, index) => values[index] !== previous[index] && form.errors[field]);

            for (const field of changed) {
                if (!clientErrors.has(field)) {
                    form.clearErrors(field);
                }
            }

            const targets = [...new Set([...changed, ...clientErrors])];

            if (targets.length) {
                applyErrors(targets, issuesByField());
            }
        },
    );

    return { form: form as unknown as InertiaForm<TForm>, validate, validateField };
}
