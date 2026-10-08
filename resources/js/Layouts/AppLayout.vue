<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import { useTranslation } from '@/i18n';

const { t } = useTranslation();
</script>

<template>
    <div class="min-h-dvh bg-background text-foreground">
        <header class="border-b border-border bg-surface">
            <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link
                    :href="route('home')"
                    class="flex items-center gap-3 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                >
                    <img src="/assets/logo.webp" alt="" width="256" height="256" class="size-9" />
                    <span class="text-lg font-semibold tracking-tight">{{ $page.props.appName }}</span>
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="inline-flex h-11 cursor-pointer items-center gap-2 rounded-xl px-4 text-sm font-semibold text-muted-foreground transition-colors duration-200 hover:bg-muted hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring motion-reduce:transition-none"
                >
                    <LogOut class="size-5" aria-hidden="true" />
                    {{ t('layout.logout') }}
                </Link>
            </nav>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>
