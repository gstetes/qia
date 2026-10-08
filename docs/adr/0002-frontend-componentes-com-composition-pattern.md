# ADR 0002: Componentes do frontend construídos com Composition Pattern

- **Status:** Aceito
- **Data:** 2026-10-07

## Contexto

O frontend do QIA é construído com Vue 3, Inertia e Tailwind CSS. A primeira tela (login) inaugura a biblioteca de componentes de interface, e a forma como esses componentes forem escritos agora será replicada em todo o produto.

Sem um padrão definido, componentes de interface tendem a crescer por configuração: cada nova necessidade vira mais uma prop (`title`, `subtitle`, `showFooter`, `footerAlign`, `hasIcon`...). Com o tempo isso gera:

- componentes "faz-tudo" com dezenas de props e condicionais no template;
- dificuldade de atender casos não previstos sem alterar o componente base e arriscar regressões;
- acessibilidade (ids, `aria-*`, associação entre label, campo e mensagem de erro) reimplementada à mão em cada tela.

## Decisão

Todo componente de interface do frontend deve seguir o **Composition Pattern**: componentes pequenos, com uma única responsabilidade, combinados pelo consumidor por meio de **slots**, em vez de um único componente configurado por props.

### Componentes compostos

Um componente complexo é dividido em partes que o consumidor monta no template. Cada parte cuida apenas da sua estrutura e estilo.

```vue
<!-- Evitar: estrutura controlada por props -->
<Card title="Entrar" description="Informe seu e-mail..." :show-footer="true" footer-align="end" />

<!-- Adotar: estrutura composta pelo consumidor -->
<Card>
    <CardHeader>
        <CardTitle as="h1">Entrar</CardTitle>
        <CardDescription>Informe seu e-mail e senha para acessar sua conta.</CardDescription>
    </CardHeader>
    <CardContent>...</CardContent>
    <CardFooter>...</CardFooter>
</Card>
```

### Estado compartilhado com `provide`/`inject`

Quando as partes precisam compartilhar estado, o componente raiz expõe um contexto com `provide` e as partes o consomem por um composable. Isso evita repassar props manualmente entre as partes.

O `FormField` é a referência: ele gera o `id` do campo e expõe o erro. `FormLabel`, `Input` e `FormMessage` leem esse contexto e fazem a associação de acessibilidade (`for`, `aria-invalid`, `aria-describedby`, `role="alert"`) automaticamente.

```vue
<FormField :error="form.errors.email">
    <FormLabel>E-mail</FormLabel>
    <Input v-model="form.email" type="email" autocomplete="username" />
    <FormMessage />
</FormField>
```

Regras para contextos:

- A chave de injeção é um `InjectionKey` tipado, exportado junto do composable e da interface do contexto (ex.: `FormFieldKey` e `FormFieldContext` em `useFormField.ts`).
- O composable retorna `null` fora do contexto, para que partes reutilizáveis (como o `Input`) também funcionem isoladas.
- Valores reativos do contexto são `computed` ou `ref`, nunca cópias estáticas de props.

### Props, slots e atributos

- **Slots** definem conteúdo e estrutura.
- **Props** ficam restritas a variações de comportamento ou aparência com valores finitos (`variant`, `loading`, `as`), declaradas com tipos literais (ver ADR 0003).
- **Atributos** não declarados (`class`, `type`, `autocomplete`, `aria-*`) chegam ao elemento raiz pelo fallthrough do Vue. Componentes que envolvem outro elemento usam `inheritAttrs: false` e repassam `$attrs` ao elemento correto, como no `PasswordInput`.
- `v-model` usa `defineModel`.
- Quando o pai precisar controlar o elemento (ex.: foco após erro), o componente expõe apenas o necessário com `defineExpose`.

### Composição em vez de herança ou duplicação

Variações são criadas compondo componentes existentes. O `PasswordInput` reutiliza o `Input` e acrescenta apenas o botão de mostrar/ocultar senha. Lógica sem template compartilhada entre componentes vai para composables (`use*`).

### Estrutura de pastas

```
resources/js/
├── Components/
│   └── ui/                 # Componentes de interface, sem regra de negócio
│       ├── Button/
│       ├── Card/
│       │   ├── Card.vue
│       │   ├── CardHeader.vue
│       │   ├── ...
│       │   └── index.ts    # Ponto único de importação das partes
│       ├── Form/
│       │   ├── FormField.vue
│       │   ├── useFormField.ts
│       │   └── index.ts
│       └── Input/
├── Layouts/                # Estruturas de página (ex.: AuthLayout), compostas por componentes ui
└── Pages/                  # Páginas Inertia: compõem layouts e componentes, contêm o estado da tela
```

- Cada componente composto tem sua pasta e um `index.ts` que exporta todas as partes, importadas com o alias `@` (`import { Card, CardHeader } from '@/Components/ui/Card'`).
- Componentes em `Components/ui` não fazem requisições nem conhecem rotas ou regras de negócio. Isso fica nas páginas.
- Estilos usam exclusivamente os tokens semânticos do tema (`bg-primary`, `text-muted-foreground`, `border-border`), nunca cores hexadecimais nos componentes.

### Acessibilidade como responsabilidade do componente

Componentes base já entregam o comportamento acessível: foco visível com `ring`, alvo de toque mínimo de 44px, estados `disabled` e `aria-busy`, e respeito a `prefers-reduced-motion`. As páginas não devem precisar reimplementar isso.

### Pragmatismo

Seguem valendo KISS e YAGNI:

- Não criar partes, props ou variantes sem uso real. Um componente sem estado compartilhado não precisa de `provide`/`inject`.
- Uma estrutura usada uma única vez pode ficar na própria página. Ela vira componente quando passar a se repetir.

## Consequências

### Positivas

- Componentes base estáveis: novos casos são atendidos compondo partes, sem alterar o componente original.
- Templates legíveis, em que a estrutura visual aparece na própria página.
- Acessibilidade de formulários garantida por padrão pelo contexto do `FormField`.
- Biblioteca de componentes consistente com o tema e fácil de estender.

### Negativas

- Mais arquivos por componente e mais importações em cada página.
- O consumidor escreve mais marcação do que com um componente configurado por props.
- `provide`/`inject` torna a dependência entre as partes implícita. Por isso cada contexto deve ter um composable documentado e as partes devem ser usadas dentro do componente raiz correspondente.
