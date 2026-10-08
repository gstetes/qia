<script setup lang="ts">
const { as = 'h2' } = defineProps<{
    as?: 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6';
}>();
</script>

<template>
    <component :is="as" class="text-2xl font-semibold tracking-tight">
        <slot />
    </component>
</template>
