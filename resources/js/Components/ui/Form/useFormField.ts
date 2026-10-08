import { inject, type ComputedRef, type InjectionKey, type Ref } from 'vue';

export interface FormFieldContext {
    id: string;
    descriptionId: string;
    messageId: string;
    hasDescription: Ref<boolean>;
    error: ComputedRef<string | undefined>;
}

export const FormFieldKey: InjectionKey<FormFieldContext> = Symbol('FormField');

/**
 * Contexto do FormField mais próximo. Retorna null quando usado fora de um FormField,
 * permitindo que controles como o Input funcionem também de forma isolada.
 */
export function useFormField(): FormFieldContext | null {
    return inject(FormFieldKey, null);
}
