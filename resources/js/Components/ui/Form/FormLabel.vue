<script setup lang="ts">
import { useFormField } from './useFormField';

const field = useFormField();
</script>

<template>
    <label :for="field?.id" class="text-sm font-medium text-foreground">
        <slot />
    </label>
</template>
