# ADR 0001: Backend construído em DDD seguindo os princípios SOLID

- **Status:** Aceito
- **Data:** 2026-10-07

## Contexto

O QIA é um produto SaaS para e-commerce, desenvolvido em Laravel 11 (PHP 8.3) com Inertia e Vue 3. O projeto está no início e a única estrutura de dados existente é a tabela `users`.

Sem uma arquitetura definida, aplicações Laravel tendem a concentrar regra de negócio em controllers e models. Com o crescimento do produto isso gera:

- regras de negócio duplicadas e espalhadas entre controllers, jobs, comandos e listeners;
- código difícil de testar sem subir HTTP e banco de dados;
- acoplamento entre domínio, framework e integrações externas, o que encarece mudanças.

Definir a arquitetura agora, antes de existir código de negócio, evita uma migração cara no futuro.

## Decisão

Todo o backend deve ser construído seguindo **Domain-Driven Design (DDD)** e os princípios **SOLID**.

### Camadas

O código fica organizado em quatro camadas. A dependência aponta sempre para dentro: Interface → Application → Domain, e Infrastructure → Domain.

| Camada | Local | Responsabilidade | Pode depender de |
|---|---|---|---|
| **Domain** | `app/Domain/<Contexto>` | Regras de negócio: models (agregados e entidades), value objects, enums, eventos de domínio, exceções de domínio e contratos (interfaces) de repositórios e serviços. | Nada fora do próprio domínio, exceto o Eloquent nos models (ver abaixo). |
| **Application** | `app/Application/<Contexto>` | Casos de uso: orquestram o domínio para executar uma ação do sistema (ex.: `CreateUser`). Recebem e devolvem DTOs. | Domain. |
| **Infrastructure** | `app/Infrastructure/<Contexto>` | Implementações técnicas dos contratos do domínio: repositórios Eloquent, clientes de APIs externas, filas, storage. | Domain e framework. |
| **Interface** | `app/Http`, `app/Console` | Entrada do sistema: controllers, form requests, resources e comandos. Validam a entrada, chamam um caso de uso e formatam a resposta. Não contêm regra de negócio. | Application. |

Estrutura de referência:

```
app/
├── Domain/
│   └── User/
│       ├── Models/User.php
│       ├── ValueObjects/
│       ├── Events/
│       ├── Exceptions/
│       └── Contracts/UserRepository.php
├── Application/
│   └── User/
│       ├── UseCases/CreateUser.php
│       └── DTOs/CreateUserData.php
├── Infrastructure/
│   └── User/
│       └── Repositories/EloquentUserRepository.php
├── Http/
│   ├── Controllers/
│   └── Requests/
└── Providers/
```

### Contextos delimitados (bounded contexts)

- Cada contexto de negócio (ex.: `User`, `Catalog`, `Order`) tem sua pasta em cada camada que usar.
- Um contexto não acessa models, tabelas ou repositórios de outro diretamente. A comunicação entre contextos acontece por casos de uso, contratos ou eventos de domínio.
- Nomes de classes, métodos e pastas usam a linguagem do negócio (linguagem ubíqua).

### Uso do Eloquent no domínio

Os models Eloquent ficam em `Domain/<Contexto>/Models` e representam os agregados e entidades. Não criamos uma entidade "pura" separada do model.

Essa é uma concessão deliberada: separar entidade e model duplicaria cada classe e exigiria mapeamento manual, um custo que não se justifica neste produto. Para que a concessão não vire acoplamento:

- O model contém regras e invariantes da própria entidade. Consultas complexas ficam nos repositórios.
- Casos de uso dependem das interfaces em `Contracts`, nunca das implementações em `Infrastructure`.
- Os bindings entre contrato e implementação são registrados em service providers.

### Princípios SOLID

- **S (responsabilidade única):** cada classe tem um único motivo para mudar. Um caso de uso executa uma única ação; controllers não acumulam regras.
- **O (aberto/fechado):** novos comportamentos entram por novas implementações de um contrato, e não por condicionais adicionadas em classes existentes.
- **L (substituição de Liskov):** qualquer implementação de um contrato deve poder substituir outra sem quebrar quem a usa.
- **I (segregação de interfaces):** contratos pequenos e específicos, em vez de interfaces genéricas com métodos que nem todos usam.
- **D (inversão de dependência):** camadas de alto nível dependem de abstrações. Dependências são recebidas pelo construtor e resolvidas pelo container do Laravel, nunca instanciadas com `new` dentro das regras.

### Pragmatismo

DDD e SOLID servem para organizar o código, não para gerar camadas vazias. Seguem valendo KISS e YAGNI:

- Não criar contratos, eventos ou value objects sem uso real.
- Um caso de uso simples pode ser uma única classe com um método `handle`.
- Recursos oficiais do Laravel (form requests, policies, eventos, filas, container) são usados sempre que atenderem, dentro da camada correta.

## Consequências

### Positivas

- Regras de negócio ficam centralizadas e fáceis de localizar.
- Casos de uso podem ser testados de forma isolada, com repositórios substituídos por implementações em memória ou mocks.
- Troca de integrações externas ou de persistência afeta só a camada de Infrastructure.
- Fronteiras claras entre contextos facilitam o trabalho em paralelo e a escalabilidade do time.

### Negativas

- Mais arquivos e mais indireção do que um Laravel tradicional, inclusive em funcionalidades simples.
- Exige que o time conheça DDD e SOLID. Revisões de código devem verificar se cada classe está na camada correta.
- Geradores do Artisan (`make:model`, `make:factory` etc.) criam arquivos nos caminhos padrão do Laravel, que precisam ser movidos ou gerados com o namespace completo.

### Ações decorrentes

- ~~O model `App\Models\User` deve ser movido para `App\Domain\User\Models\User`, atualizando a referência em `config/auth.php` e o `$model` da `UserFactory`.~~ Concluído em 2026-10-07. O model aponta para a factory pelo atributo `#[UseFactory]`, porque fora de `App\Models` o Laravel não encontra a factory pela convenção de nomes.
