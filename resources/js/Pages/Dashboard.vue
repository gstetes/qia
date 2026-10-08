<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useTranslation } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';

const { t } = useTranslation();
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <AppLayout>
        <h1 class="text-2xl font-semibold tracking-tight">{{ t('dashboard.title') }}</h1>
    </AppLayout>
</template>
