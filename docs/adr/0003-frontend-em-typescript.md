# ADR 0003: Frontend escrito em TypeScript

- **Status:** Aceito
- **Data:** 2026-10-07

## Contexto

O frontend do QIA (Vue 3, Inertia e Vite) começou em JavaScript. Nesse ponto já havia componentes compostos, formulários validados com zod, internacionalização com vue-i18n e props compartilhadas pelo backend via Inertia.

Em JavaScript, vários erros só aparecem em tempo de execução, e alguns nem chegam a gerar erro visível:

- uma chave de tradução digitada errado aparece como texto cru na tela (ex.: `auth.login.titel`);
- uma prop compartilhada renomeada no `HandleInertiaRequests` quebra páginas sem nenhum aviso no build;
- campos de formulário, erros e contextos de `provide`/`inject` não têm contrato explícito entre quem fornece e quem consome.

## Decisão

Todo código do frontend é escrito em **TypeScript**, com o modo `strict` ativo. Isso vale para módulos (`.ts`), componentes Vue (`<script setup lang="ts">`) e arquivos de configuração do Vite e do Tailwind.

### Ferramentas e configuração

| Item | Escolha |
|---|---|
| Compilador | `typescript` fixado em `~6.0`. O TypeScript 7 (reescrito em Go) ainda não é suportado pelo `vue-tsc`. Reavaliar quando o `vue-tsc` declarar suporte. |
| Checagem de `.vue` | `vue-tsc --build` (`npm run typecheck`). |
| Build | `npm run build` executa a checagem de tipos antes do `vite build`. Erro de tipo bloqueia o build. |
| Base de configuração | `@vue/tsconfig`, com `strict`, `verbatimModuleSyntax` e `moduleResolution: bundler`. |
| Projetos | `tsconfig.json` referencia `tsconfig.app.json` (código em `resources/js`, ambiente DOM) e `tsconfig.node.json` (`vite.config.ts` e `tailwind.config.ts`, ambiente Node). |
| Alias | `@/*` aponta para `resources/js/*`. |

O Vite transpila TypeScript nativamente. Ele não verifica tipos, por isso o `vue-tsc` é obrigatório no build e deve rodar no CI.

### Tipos globais

Ficam em `resources/js/types`:

- `global.d.ts`: o helper `route()` do Ziggy, tanto no script quanto no template, e as props compartilhadas por todas as páginas (`SharedData`), registradas no `InertiaConfig` do Inertia. Ao adicionar uma prop no `HandleInertiaRequests::share()`, atualize `SharedData`.
- `env.d.ts`: variáveis `VITE_*` usadas em `import.meta.env`.

### Componentes

- Props são declaradas com `defineProps<{ ... }>()` e padrões pela desestruturação reativa (`const { variant = 'primary' } = defineProps<...>()`). Não usar a sintaxe de objeto com `type: String`.
- Variações usam tipos literais (`'primary' | 'secondary' | 'ghost'`) em vez de `string`, e mapas de classes usam `Record<Variante, string>`.
- `v-model` usa `defineModel<T>()`, referências de template usam `useTemplateRef<T>()` e o que o componente expõe é tipado com `defineExpose<T>()` (ex.: `Focusable`).
- Contextos de `provide`/`inject` têm uma interface e uma `InjectionKey<T>` (ex.: `FormFieldContext` e `FormFieldKey`).
- Componentes só de template, sem lógica, não precisam de bloco `<script>`.

### Formulários e validação

- O tipo dos dados de um formulário vem do schema zod, sem declaração duplicada: `export type LoginForm = z.input<ReturnType<typeof createLoginSchema>>`.
- A página informa esse tipo ao `useValidatedForm<LoginForm>(...)`, e assim `form.email`, `form.errors.email` e `form.post` ficam tipados. Acessar um campo inexistente é erro de compilação.

### Internacionalização

- A estrutura das mensagens (`MessageSchema`) é derivada do `pt-BR.json`. Todo novo idioma precisa satisfazer esse tipo.
- Componentes e schemas traduzem com `useTranslation()` de `@/i18n`, e não com o `useI18n()` do vue-i18n. O `t` do vue-i18n aceita qualquer string; o de `useTranslation()` aceita apenas chaves existentes (`TranslationKey`), no script e no template.

### Regras gerais

- Proibido `any`. Para valores desconhecidos use `unknown` e faça o estreitamento.
- Asserções (`as`) só na fronteira com bibliotecas cujos tipos não se resolvem, sempre com comentário explicando o motivo (ex.: o retorno do `useForm` em `useValidatedForm`).
- Tipos usados apenas como tipo são importados com `import type` ou `type` no import (exigido por `verbatimModuleSyntax`).

## Consequências

### Positivas

- Chaves de tradução, props compartilhadas, campos de formulário e contratos entre componentes passam a ser verificados no build.
- Autocomplete e refatorações seguras no editor, inclusive dentro dos templates Vue.
- O tipo dos formulários é derivado dos schemas zod, mantendo validação e tipagem em uma única fonte.

### Negativas

- O build fica mais lento por causa da checagem de tipos.
- Tipos genéricos de bibliotecas (como o `useForm` do Inertia) às vezes exigem asserções na fronteira.
- A versão do TypeScript fica presa à compatibilidade com o `vue-tsc`.
- `SharedData` precisa ser mantido em sincronia manual com o `HandleInertiaRequests`.
