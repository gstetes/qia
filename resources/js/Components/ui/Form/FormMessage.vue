<script setup lang="ts">
import { useFormField } from './useFormField';

const field = useFormField();
</script>

<template>
    <p
        v-if="field?.error.value"
        :id="field.messageId"
        role="alert"
        class="text-sm font-medium text-danger"
    >
        {{ field.error.value }}
    </p>
</template>
