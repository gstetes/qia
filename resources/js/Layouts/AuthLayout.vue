<script setup lang="ts">
import { useTranslation } from '@/i18n';
import { Card } from '@/Components/ui/Card';

const { t } = useTranslation();

const year = new Date().getFullYear();
</script>

<template>
    <div class="relative isolate flex min-h-dvh items-center justify-center overflow-hidden bg-background px-4 py-10 sm:px-6">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10">
            <div
                class="absolute inset-0 bg-[linear-gradient(to_right,theme(colors.neutral.200)_1px,transparent_1px),linear-gradient(to_bottom,theme(colors.neutral.200)_1px,transparent_1px)] bg-[size:48px_48px] [mask-image:radial-gradient(ellipse_at_center,black_10%,transparent_70%)]"
            />
            <div class="absolute -top-48 left-1/2 size-[40rem] -translate-x-1/2 rounded-full bg-primary-300/30 blur-3xl" />
            <div class="absolute -bottom-56 -right-40 size-[32rem] rounded-full bg-primary-200/40 blur-3xl" />
        </div>

        <Card class="grid w-full max-w-5xl gap-2 p-2 md:grid-cols-[1.1fr_1fr]">
            <aside
                class="relative isolate flex flex-col justify-between overflow-hidden rounded-2xl bg-neutral-950 p-5 text-neutral-50 md:min-h-[560px] md:p-10"
            >
                <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10">
                    <div class="absolute -left-24 -top-24 size-80 rounded-full bg-primary-500/40 blur-3xl" />
                    <div class="absolute -bottom-24 -right-16 size-72 rounded-full bg-primary-300/20 blur-3xl" />
                    <div
                        class="absolute inset-0 bg-[linear-gradient(to_right,rgb(255_255_255/0.04)_1px,transparent_1px),linear-gradient(to_bottom,rgb(255_255_255/0.04)_1px,transparent_1px)] bg-[size:32px_32px] [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_75%)]"
                    />
                </div>

                <div class="flex items-center gap-3">
                    <img src="/assets/logo.webp" alt="" width="256" height="256" class="size-9" />
                    <span class="text-lg font-semibold tracking-tight">{{ $page.props.appName }}</span>
                </div>

                <div aria-hidden="true" class="relative hidden items-center justify-center py-10 md:flex">
                    <div class="absolute size-[26rem] rounded-full border border-white/5" />
                    <div class="absolute size-[18rem] rounded-full border border-white/10" />
                    <div class="absolute size-[11rem] rounded-full border border-primary-300/20 bg-primary-500/10" />
                    <img
                        src="/assets/logo.webp"
                        alt=""
                        width="256"
                        height="256"
                        class="relative size-28 drop-shadow-[0_0_48px_rgb(51_153_137/0.6)]"
                    />
                </div>

                <p class="hidden text-sm text-neutral-400 md:block">
                    {{ t('layout.copyright', { year, appName: $page.props.appName }) }}
                </p>
            </aside>

            <div class="flex flex-col justify-center py-4 md:py-10">
                <slot />
            </div>
        </Card>
    </div>
</template>
