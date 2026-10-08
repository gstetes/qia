<script setup lang="ts">
import { computed, provide, ref, useId } from 'vue';
import { FormFieldKey } from './useFormField';

const props = defineProps<{
    error?: string;
}>();

const id = useId();
const hasDescription = ref(false);

provide(FormFieldKey, {
    id,
    descriptionId: `${id}-description`,
    messageId: `${id}-message`,
    hasDescription,
    error: computed(() => props.error),
});
</script>

<template>
    <div class="flex flex-col gap-2">
        <slot />
    </div>
</template>
