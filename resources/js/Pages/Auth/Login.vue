<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, CircleCheck, LockKeyhole, Mail } from 'lucide-vue-next';
import { computed, ref, type Ref } from 'vue';
import { Alert } from '@/Components/ui/Alert';
import { useTranslation } from '@/i18n';
import { Button } from '@/Components/ui/Button';
import { CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/Card';
import { FormField, FormLabel, FormMessage } from '@/Components/ui/Form';
import { Input, InputGroup, InputIcon, PasswordInput, type Focusable } from '@/Components/ui/Input';
import { useValidatedForm } from '@/Composables/useValidatedForm';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { createLoginSchema, type LoginForm } from '@/Schemas/Auth/loginSchema';

const { t } = useTranslation();

const page = usePage();
const registered = computed(() => page.flash.status === 'registered');

const { form, validate, validateField } = useValidatedForm<LoginForm>(createLoginSchema(t), {
    email: '',
    password: '',
});

const inputs: Record<keyof LoginForm, Ref<Focusable | null>> = {
    email: ref(null),
    password: ref(null),
};

const focusFirstError = () => {
    const field = (Object.keys(inputs) as (keyof LoginForm)[]).find((name) => form.errors[name]);

    if (field) {
        inputs[field].value?.focus();
    }
};

const submit = () => {
    if (!validate()) {
        focusFirstError();

        return;
    }

    form.post(route('login'), {
        onError: focusFirstError,
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head :title="t('auth.login.title')" />

    <AuthLayout>
        <form novalidate @submit.prevent="submit">
            <CardHeader class="gap-2 sm:px-10">
                <CardTitle as="h1" class="text-3xl">{{ t('auth.login.heading') }}</CardTitle>
                <CardDescription>{{ t('auth.login.description') }}</CardDescription>
            </CardHeader>

            <CardContent class="flex flex-col gap-5 sm:px-10">
                <Alert v-if="registered" variant="success">
                    <CircleCheck aria-hidden="true" />
                    <p>{{ t('auth.login.registered') }}</p>
                </Alert>

                <FormField :error="form.errors.email">
                    <FormLabel>{{ t('fields.email.label') }}</FormLabel>
                    <InputGroup>
                        <InputIcon><Mail /></InputIcon>
                        <Input
                            :ref="inputs.email"
                            v-model="form.email"
                            type="email"
                            name="email"
                            autocomplete="username"
                            :placeholder="t('fields.email.placeholder')"
                            class="pl-12"
                            required
                            autofocus
                            @blur="validateField('email')"
                        />
                    </InputGroup>
                    <FormMessage />
                </FormField>

                <FormField :error="form.errors.password">
                    <FormLabel>{{ t('fields.password.label') }}</FormLabel>
                    <InputGroup>
                        <InputIcon><LockKeyhole /></InputIcon>
                        <PasswordInput
                            :ref="inputs.password"
                            v-model="form.password"
                            name="password"
                            autocomplete="current-password"
                            :placeholder="t('fields.password.placeholder')"
                            class="pl-12"
                            required
                            @blur="validateField('password')"
                        />
                    </InputGroup>
                    <FormMessage />
                </FormField>
            </CardContent>

            <CardFooter class="flex-col gap-5 pt-2 sm:px-10">
                <Button type="submit" class="w-full" :loading="form.processing">
                    {{ t('auth.login.submit') }}
                    <ArrowRight
                        v-if="!form.processing"
                        class="size-5 transition-transform duration-200 group-hover:translate-x-0.5 motion-reduce:transition-none"
                        aria-hidden="true"
                    />
                </Button>

                <p class="text-sm text-muted-foreground">
                    {{ t('auth.login.noAccount') }}
                    <Link
                        :href="route('register')"
                        class="rounded font-semibold text-primary underline-offset-4 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                        {{ t('auth.login.registerLink') }}
                    </Link>
                </p>
            </CardFooter>
        </form>
    </AuthLayout>
</template>
