<script setup lang="ts">
import { computed, useTemplateRef } from 'vue';
import { useFormField } from '../Form';
import type { Focusable } from './types';

const model = defineModel<string>({ default: '' });

const field = useFormField();
const input = useTemplateRef<HTMLInputElement>('input');

const invalid = computed(() => Boolean(field?.error.value));

const describedBy = computed(
    () =>
        [field?.hasDescription.value && field.descriptionId, invalid.value && field?.messageId]
            .filter(Boolean)
            .join(' ') || undefined,
);

defineExpose<Focusable>({
    focus: () => input.value?.focus(),
});
</script>

<template>
    <input
        ref="input"
        v-model="model"
        :id="field?.id"
        :aria-invalid="invalid || undefined"
        :aria-describedby="describedBy"
        class="h-12 w-full rounded-xl border border-input bg-surface px-4 text-base shadow-sm text-foreground transition-colors duration-200 placeholder:text-muted-foreground focus-visible:border-ring focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/30 disabled:cursor-not-allowed disabled:opacity-50 motion-reduce:transition-none aria-[invalid=true]:border-danger aria-[invalid=true]:focus-visible:ring-danger/30"
    />
</template>
