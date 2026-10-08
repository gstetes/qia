<script setup lang="ts">
import { LoaderCircle } from 'lucide-vue-next';
import type { ButtonHTMLAttributes } from 'vue';

type ButtonVariant = 'primary' | 'secondary' | 'ghost';

const {
    type = 'button',
    variant = 'primary',
    loading = false,
    disabled = false,
} = defineProps<{
    type?: ButtonHTMLAttributes['type'];
    variant?: ButtonVariant;
    loading?: boolean;
    disabled?: boolean;
}>();

const variants: Record<ButtonVariant, string> = {
    primary: 'bg-primary text-primary-foreground shadow-lg shadow-primary-600/25 hover:bg-primary-700 hover:shadow-primary-700/30',
    secondary: 'bg-secondary text-secondary-foreground hover:bg-neutral-800',
    ghost: 'text-foreground hover:bg-muted',
};
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :aria-busy="loading || undefined"
        :class="variants[variant]"
        class="group inline-flex h-12 cursor-pointer items-center justify-center gap-2 rounded-xl px-5 text-base font-semibold transition duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-surface active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50 motion-reduce:transition-none motion-reduce:active:scale-100"
    >
        <LoaderCircle v-if="loading" class="size-5 animate-spin motion-reduce:animate-none" aria-hidden="true" />
        <slot />
    </button>
</template>
