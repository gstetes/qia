<script setup lang="ts">
import { onBeforeUnmount, onMounted } from 'vue';
import { useFormField } from './useFormField';

const field = useFormField();

onMounted(() => field && (field.hasDescription.value = true));
onBeforeUnmount(() => field && (field.hasDescription.value = false));
</script>

<template>
    <p :id="field?.descriptionId" class="text-sm text-muted-foreground">
        <slot />
    </p>
</template>
