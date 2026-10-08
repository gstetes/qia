<script setup lang="ts">
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref, useTemplateRef } from 'vue';
import { useTranslation } from '@/i18n';
import Input from './Input.vue';
import type { Focusable } from './types';

defineOptions({ inheritAttrs: false });

const model = defineModel<string>({ default: '' });

const { t } = useTranslation();

const visible = ref(false);
const input = useTemplateRef<Focusable>('input');

defineExpose<Focusable>({
    focus: () => input.value?.focus(),
});
</script>

<template>
    <div class="relative">
        <Input
            ref="input"
            v-model="model"
            v-bind="$attrs"
            :type="visible ? 'text' : 'password'"
            class="pr-12"
        />

        <button
            type="button"
            :aria-label="visible ? t('fields.password.hide') : t('fields.password.show')"
            :aria-pressed="visible"
            class="absolute inset-y-0 right-0 flex w-12 cursor-pointer items-center justify-center rounded-r-xl text-muted-foreground transition-colors duration-200 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-ring motion-reduce:transition-none"
            @click="visible = !visible"
        >
            <EyeOff v-if="visible" class="size-5" aria-hidden="true" />
            <Eye v-else class="size-5" aria-hidden="true" />
        </button>
    </div>
</template>
