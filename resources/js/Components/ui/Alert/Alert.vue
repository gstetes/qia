<script setup lang="ts">
type AlertVariant = 'success' | 'danger';

const { variant = 'success' } = defineProps<{
    variant?: AlertVariant;
}>();

const variants: Record<AlertVariant, string> = {
    success: 'border-success/30 bg-success/10 [&>svg]:text-success',
    danger: 'border-danger/30 bg-danger/10 [&>svg]:text-danger',
};
</script>

<template>
    <div
        :role="variant === 'danger' ? 'alert' : 'status'"
        :class="variants[variant]"
        class="flex items-start gap-3 rounded-xl border px-4 py-3 text-sm text-foreground [&>svg]:size-5 [&>svg]:shrink-0"
    >
        <slot />
    </div>
</template>
