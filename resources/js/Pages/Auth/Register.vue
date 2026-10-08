<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, LockKeyhole, Mail, UserRound } from 'lucide-vue-next';
import { ref, type Ref } from 'vue';
import { useTranslation } from '@/i18n';
import { Button } from '@/Components/ui/Button';
import { CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/Card';
import { FormDescription, FormField, FormLabel, FormMessage } from '@/Components/ui/Form';
import { Input, InputGroup, InputIcon, PasswordInput, type Focusable } from '@/Components/ui/Input';
import { useValidatedForm } from '@/Composables/useValidatedForm';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { createRegisterSchema, type RegisterForm } from '@/Schemas/Auth/registerSchema';
import { PASSWORD_MIN_LENGTH } from '@/Schemas/fields';

const { t } = useTranslation();

const { form, validate, validateField } = useValidatedForm<RegisterForm>(createRegisterSchema(t), {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const inputs: Record<keyof RegisterForm, Ref<Focusable | null>> = {
    name: ref(null),
    email: ref(null),
    password: ref(null),
    password_confirmation: ref(null),
};

const focusFirstError = () => {
    const field = (Object.keys(inputs) as (keyof RegisterForm)[]).find((name) => form.errors[name]);

    if (field) {
        inputs[field].value?.focus();
    }
};

const submit = () => {
    if (!validate()) {
        focusFirstError();

        return;
    }

    form.post(route('register'), {
        onError: focusFirstError,
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="t('auth.register.title')" />

    <AuthLayout>
        <form novalidate @submit.prevent="submit">
            <CardHeader class="gap-2 sm:px-10">
                <CardTitle as="h1" class="text-3xl">{{ t('auth.register.heading') }}</CardTitle>
                <CardDescription>{{ t('auth.register.description') }}</CardDescription>
            </CardHeader>

            <CardContent class="flex flex-col gap-5 sm:px-10">
                <FormField :error="form.errors.name">
                    <FormLabel>{{ t('fields.name.label') }}</FormLabel>
                    <InputGroup>
                        <InputIcon><UserRound /></InputIcon>
                        <Input
                            :ref="inputs.name"
                            v-model="form.name"
                            name="name"
                            autocomplete="name"
                            :placeholder="t('fields.name.placeholder')"
                            class="pl-12"
                            required
                            autofocus
                            @blur="validateField('name')"
                        />
                    </InputGroup>
                    <FormMessage />
                </FormField>

                <FormField :error="form.errors.email">
                    <FormLabel>{{ t('fields.email.label') }}</FormLabel>
                    <InputGroup>
                        <InputIcon><Mail /></InputIcon>
                        <Input
                            :ref="inputs.email"
                            v-model="form.email"
                            type="email"
                            name="email"
                            autocomplete="email"
                            :placeholder="t('fields.email.placeholder')"
                            class="pl-12"
                            required
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
                            autocomplete="new-password"
                            :placeholder="t('fields.password.newPlaceholder')"
                            class="pl-12"
                            required
                            @blur="validateField('password')"
                        />
                    </InputGroup>
                    <FormDescription v-if="!form.errors.password">
                        {{ t('fields.password.hint', { min: PASSWORD_MIN_LENGTH }) }}
                    </FormDescription>
                    <FormMessage />
                </FormField>

                <FormField :error="form.errors.password_confirmation">
                    <FormLabel>{{ t('fields.passwordConfirmation.label') }}</FormLabel>
                    <InputGroup>
                        <InputIcon><LockKeyhole /></InputIcon>
                        <PasswordInput
                            :ref="inputs.password_confirmation"
                            v-model="form.password_confirmation"
                            name="password_confirmation"
                            autocomplete="new-password"
                            :placeholder="t('fields.passwordConfirmation.placeholder')"
                            class="pl-12"
                            required
                            @blur="validateField('password_confirmation')"
                        />
                    </InputGroup>
                    <FormMessage />
                </FormField>
            </CardContent>

            <CardFooter class="flex-col gap-5 pt-2 sm:px-10">
                <Button type="submit" class="w-full" :loading="form.processing">
                    {{ t('auth.register.submit') }}
                    <ArrowRight
                        v-if="!form.processing"
                        class="size-5 transition-transform duration-200 group-hover:translate-x-0.5 motion-reduce:transition-none"
                        aria-hidden="true"
                    />
                </Button>

                <p class="text-sm text-muted-foreground">
                    {{ t('auth.register.hasAccount') }}
                    <Link
                        :href="route('login')"
                        class="rounded font-semibold text-primary underline-offset-4 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                        {{ t('auth.register.loginLink') }}
                    </Link>
                </p>
            </CardFooter>
        </form>
    </AuthLayout>
</template>
