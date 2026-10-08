# Guia de estudos: PHP e Laravel avançado com o QIA

Este guia acompanha a construção do QIA. Cada etapa implementada no projeto vira uma seção com:

- **O que foi feito:** a mudança no projeto, com links para os arquivos.
- **Conceitos:** o que é preciso entender para reproduzir a etapa sozinho.
- **Por dentro do framework:** onde ler o código-fonte do Laravel (em `vendor/`) para ver o mecanismo funcionando.
- **Para praticar:** exercícios no próprio projeto.
- **Revisão:** perguntas para checar o entendimento.

As versões usadas são PHP 8.3, Laravel 11 e Inertia v3. Os caminhos citados em `vendor/` existem no projeto: abra-os no editor e leia junto com o texto. Ler o código do framework é a forma mais rápida de sair do nível intermediário.

## Índice

- [Etapa 0: fundação do projeto](#etapa-0-fundação-do-projeto)
- [Etapa 1: assets, Vite e o tema](#etapa-1-assets-vite-e-o-tema)
- [Etapa 2: login](#etapa-2-login)
- [Etapa 3: proteger a página inicial](#etapa-3-proteger-a-página-inicial)
- [Etapa 4: internacionalização no backend](#etapa-4-internacionalização-no-backend)
- [Etapa 5: cadastro com DDD](#etapa-5-cadastro-com-ddd)
- [Etapa 6: testes automatizados](#etapa-6-testes-automatizados)
- [Etapa 7: contrato entre backend e frontend tipado](#etapa-7-contrato-entre-backend-e-frontend-tipado)
- [Etapa 8: redirecionar para o login após o cadastro](#etapa-8-redirecionar-para-o-login-após-o-cadastro)
- [Etapa 9: favicons e a pasta public](#etapa-9-favicons-e-a-pasta-public)
- [Próximos tópicos de estudo](#próximos-tópicos-de-estudo)

---

## Etapa 0: fundação do projeto

### O que foi feito

- Projeto Laravel 11 com Inertia e Vue 3.
- [ADR 0001](adr/0001-arquitetura-backend-ddd-e-solid.md): backend organizado em DDD (Domain, Application, Infrastructure, Interface) seguindo SOLID.
- O model `User` saiu de `app/Models` e foi para [app/Domain/User/Models/User.php](../app/Domain/User/Models/User.php).

### Conceitos

**Ciclo de vida de uma requisição no Laravel 11.** Entender esse fluxo explica onde cada peça do projeto entra:

1. O servidor web aponta para `public/`. Tudo que não é arquivo estático cai em [public/index.php](../public/index.php).
2. `index.php` carrega o autoload do Composer e o [bootstrap/app.php](../bootstrap/app.php), que monta a aplicação com o `ApplicationBuilder` (`withRouting`, `withMiddleware`, `withExceptions`).
3. `$app->handleRequest(Request::capture())` entrega a requisição ao **HTTP Kernel**, que executa os *bootstrappers*: carrega o `.env`, a configuração e o tratamento de exceções, registra as facades, depois registra e inicializa os service providers.
4. A requisição passa pelo **middleware global** e chega ao **Router**, que encontra a rota e aplica o middleware dela (grupo `web` e os específicos, como `auth`).
5. O controller executa e devolve uma resposta, que volta pelo mesmo pipeline de middleware no sentido inverso.
6. A resposta é enviada e o Kernel executa o `terminate` dos middlewares que o implementam.

**Service providers.** São o ponto de configuração da aplicação. Os providers ficam listados em [bootstrap/providers.php](../bootstrap/providers.php) e têm duas fases:

- `register()`: só registra coisas no container. Não use outros serviços aqui, porque eles podem ainda não ter sido registrados.
- `boot()`: roda depois que **todos** os providers foram registrados. Aqui já é seguro usar qualquer serviço.

**Eloquent no domínio.** No [User.php](../app/Domain/User/Models/User.php):

- `$fillable` define quais atributos aceitam *mass assignment* (`User::create([...])`). Atributos fora da lista são ignorados, o que protege contra um usuário enviar campos que não deveria, como `is_admin`.
- `$hidden` remove atributos (como `password`) quando o model é convertido em array ou JSON.
- O cast `'password' => 'hashed'` aplica o hash automaticamente ao atribuir a senha. Por isso o caso de uso passa a senha em texto puro e ela chega ao banco já com hash.
- `SoftDeletes` faz o `delete()` preencher `deleted_at` em vez de apagar a linha, e adiciona um *global scope* que esconde os registros excluídos das consultas.
- O atributo `#[UseFactory(UserFactory::class)]` existe porque, fora de `App\Models`, o Laravel não encontra a factory pela convenção de nomes.

### Por dentro do framework

- `vendor/laravel/framework/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php`: o que cada `with*()` do `bootstrap/app.php` faz.
- `vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php`: a lista `$bootstrappers` e o método `sendRequestThroughRouter()`.
- `vendor/laravel/framework/src/Illuminate/Database/Eloquent/SoftDeletingScope.php`: como o global scope do SoftDeletes filtra as consultas.

### Para praticar

1. Coloque um `dd('register')` no `register()` e um `dd('boot')` no `boot()` do `AppServiceProvider` e observe qual executa primeiro. Remova depois.
2. No `php artisan tinker`, crie um usuário, exclua-o com `delete()` e compare `User::count()` com `User::withTrashed()->count()`.

### Revisão

- Por que não é seguro resolver serviços dentro do `register()` de um provider?
- O que acontece com `User::create(['is_admin' => true])` se `is_admin` não estiver em `$fillable`?

---

## Etapa 1: assets, Vite e o tema

### O que foi feito

- Paleta de cores no Tailwind com tokens semânticos (`primary`, `secondary`, `muted`...) em [tailwind.config.ts](../tailwind.config.ts) e [resources/css/app.css](../resources/css/app.css).

### Conceitos

Esta etapa é de frontend. A parte que interessa ao backend é **como o Laravel entrega os assets**:

- A diretiva `@vite([...])` no [app.blade.php](../resources/views/app.blade.php) gera as tags `<script>` e `<link>`.
- Com `npm run dev` ativo, existe o arquivo `public/hot`. O `@vite` lê esse arquivo e aponta para o servidor do Vite, que entrega os arquivos com recarregamento instantâneo.
- Sem `public/hot`, o `@vite` lê `public/build/manifest.json` (gerado por `npm run build`) e aponta para os arquivos finais com hash no nome, por exemplo `app-DY0flZup.css`. O hash muda quando o conteúdo muda, o que permite cache longo no navegador sem risco de servir uma versão antiga.

### Por dentro do framework

- `vendor/laravel/framework/src/Illuminate/Foundation/Vite.php`: veja os métodos `isRunningHot()` e `manifest()`.

### Para praticar

1. Rode `npm run build`, pare o `npm run dev` e veja o HTML gerado pela página: as URLs passam a apontar para `/build/assets/...`.

### Revisão

- Por que os arquivos de build têm hash no nome?

---

## Etapa 2: login

### O que foi feito

- Rotas em [routes/web.php](../routes/web.php), controller [AuthenticatedSessionController](../app/Http/Controllers/Auth/AuthenticatedSessionController.php) e Form Request [LoginRequest](../app/Http/Requests/Auth/LoginRequest.php).
- Limite de 5 tentativas por e-mail + IP, regeneração da sessão no login e invalidação no logout.

### Conceitos

**Rotas e middleware.** O arquivo `routes/web.php` recebe automaticamente o grupo de middleware `web`. Esse grupo inclui:

- `EncryptCookies`: criptografa os cookies;
- `StartSession`: inicia a sessão;
- `ShareErrorsFromSession`: compartilha os erros de validação com as views;
- `ValidateCsrfToken`: proteção contra CSRF;
- `SubstituteBindings`: resolve parâmetros de rota em models (route model binding).

No projeto usamos dois *aliases* de middleware:

- `guest` (`RedirectIfAuthenticated`): quem já está logado e tenta acessar `/login` é mandado para a rota `dashboard` ou `home`, a primeira que existir.
- `auth` (`Authenticate`): quem não está logado é mandado para a rota `login`. Isso está configurado no `ApplicationBuilder` com `redirectGuestsTo(fn () => route('login'))`.

Rotas com nome (`->name('login')`) permitem gerar URLs com `route('login')`, tanto no PHP quanto no frontend (pelo Ziggy). Se a URL mudar, nada quebra.

**Form Requests.** O `LoginRequest` é injetado no método do controller. Antes de o controller executar, o Laravel:

1. chama `prepareForValidation()`, onde normalizamos o e-mail (minúsculas e sem espaços);
2. chama `authorize()`, que não definimos, então a requisição é autorizada;
3. valida os dados com as `rules()`;
4. se a validação falha, lança uma `ValidationException`. O tratador de exceções redireciona de volta com os erros e os dados antigos na sessão, **exceto** `password` e `password_confirmation`, que nunca são guardados na sessão.

O controller só executa se os dados forem válidos. Por isso ele fica tão curto.

**Como o erro chega na tela (Inertia).** O middleware [HandleInertiaRequests](../app/Http/Middleware/HandleInertiaRequests.php) herda de `Inertia\Middleware`. No `share()`, ele lê os erros da sessão e os envia na prop `errors`, com a primeira mensagem de cada campo. No Vue, o `useForm` coloca esses erros em `form.errors`.

**O protocolo do Inertia.**

- Na primeira visita, o servidor devolve o HTML completo com os dados da página em JSON no atributo `data-page`.
- Nas navegações seguintes, o frontend faz requisições com o header `X-Inertia: true`, e o servidor responde **só o JSON** da página: `component`, `props`, `url` e `version`.
- Se a versão dos assets mudou (houve deploy), o servidor responde **409** com o header `X-Inertia-Location`, e o navegador recarrega a página inteira.
- Redirecionamentos 302 depois de PUT, PATCH ou DELETE viram 303, para o navegador repetir a requisição como GET e não com o mesmo método.

**Autenticação: guards e providers.** Em [config/auth.php](../config/auth.php):

- o **guard** `web` (`SessionGuard`) define *como* o usuário é lembrado entre requisições, no caso pela sessão;
- o **provider** `users` (`EloquentUserProvider`) define *de onde* o usuário vem, no caso o model `User`.

O que o `Auth::attempt(['email' => ..., 'password' => ...])` faz:

1. O provider busca o usuário pelos campos informados, exceto a senha.
2. O guard compara a senha com o hash usando `Hash::check`.
3. Se o algoritmo de hash ficou desatualizado, a senha é refeita com o algoritmo atual (rehash).
4. Faz o login e grava o id do usuário na sessão.
5. Tudo isso roda dentro de um `Timebox`: a função leva sempre o mesmo tempo mínimo, com e-mail existente ou não. Sem isso, um atacante poderia descobrir quais e-mails estão cadastrados medindo o tempo de resposta (*user enumeration*).

**Segurança da sessão.**

- `session()->regenerate()` após o login gera um novo id de sessão. Isso evita a *session fixation*, em que um atacante faz a vítima usar um id de sessão que ele já conhece.
- No logout, `invalidate()` descarta a sessão inteira e `regenerateToken()` gera um novo token CSRF.
- `redirect()->intended(...)`: se a pessoa tentou abrir uma página protegida antes do login, o `auth` guardou essa URL na sessão (`url.intended`), e após o login ela volta para lá.

**Rate limiting.** O `LoginRequest` usa o `RateLimiter`, que guarda contadores no cache:

- a chave combina e-mail e IP (`throttleKey()`), então um atacante não bloqueia a conta de outra pessoa a partir de outro IP;
- `hit()` incrementa o contador, com expiração padrão de 60 segundos; `tooManyAttempts()` verifica o limite; `clear()` zera o contador após um login bem-sucedido;
- o evento `Lockout` é disparado quando o limite é atingido, e pode ser usado para alertas.

**CSRF.** O middleware `ValidateCsrfToken` grava o cookie `XSRF-TOKEN`. O cliente HTTP do frontend lê esse cookie e o devolve no header `X-XSRF-TOKEN` em cada requisição que altera dados. Uma requisição forjada a partir de outro site não consegue ler esse cookie, então é recusada.

### Recursos de PHP usados

- `private const MAX_ATTEMPTS = 5;`: constante de classe com visibilidade (PHP 7.1+).
- `$this->string('email')`: devolve um `Stringable` do Laravel, que permite encadear operações como `->trim()->value()`.

### Por dentro do framework

- `vendor/laravel/framework/src/Illuminate/Auth/SessionGuard.php`: métodos `attempt()`, `login()` e `logout()`.
- `vendor/laravel/framework/src/Illuminate/Foundation/Http/FormRequest.php` e `vendor/laravel/framework/src/Illuminate/Validation/ValidatesWhenResolvedTrait.php`: a ordem `prepareForValidation` → `authorize` → validação.
- `vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/Handler.php`: procure `invalid(` para ver o redirecionamento com erros e a lista `$dontFlash`.
- `vendor/inertiajs/inertia-laravel/src/Middleware.php`: `handle()` (409 e 303) e `resolveValidationErrors()`.
- `vendor/laravel/framework/src/Illuminate/Cache/RateLimiter.php`.

### Para praticar

1. Erre a senha 5 vezes e veja a mensagem de bloqueio. Depois troque `MAX_ATTEMPTS` para 3 e rode os testes: qual quebra, e por quê?
2. Abra o DevTools na aba Network, navegue entre páginas e compare a primeira resposta (HTML) com as seguintes (JSON com `X-Inertia`).
3. Crie uma rota `GET /teste` sem o middleware `auth`, depois adicione o middleware e observe o redirecionamento.

### Revisão

- Por que regenerar a sessão no login, se o usuário acabou de digitar a senha certa?
- O que o `Timebox` protege, e por que o tempo de resposta pode revelar informação?
- Por que a chave do rate limit combina e-mail **e** IP?

---

## Etapa 3: proteger a página inicial

### O que foi feito

- A rota `/` passou para dentro do grupo `auth` em [routes/web.php](../routes/web.php).

### Conceitos

- **Grupos de rotas:** `Route::middleware('auth')->group(...)` aplica o mesmo middleware a várias rotas sem repetir código.
- **`Route::inertia()`:** atalho para rotas que só renderizam uma página, sem precisar de controller.
- **O fluxo de um visitante em `/`:** o `Authenticate` lança uma `AuthenticationException`, e o tratador de exceções chama `redirect()->guest(route('login'))`. O `guest()` guarda a URL original como `url.intended` antes de redirecionar.

### Por dentro do framework

- `vendor/laravel/framework/src/Illuminate/Auth/Middleware/Authenticate.php` e `RedirectIfAuthenticated.php`.
- `vendor/laravel/framework/src/Illuminate/Routing/Redirector.php`: métodos `guest()` e `intended()`.

### Revisão

- Qual a diferença entre o middleware `auth` e o `guest`?

---

## Etapa 4: internacionalização no backend

### O que foi feito

- Traduções em [lang/pt_BR/auth.php](../lang/pt_BR/auth.php) e [lang/pt_BR/validation.php](../lang/pt_BR/validation.php).
- O idioma é compartilhado com o frontend pelo `HandleInertiaRequests`.

### Conceitos

- **Arquivos de idioma:** `__('auth.failed')` procura a chave `failed` em `lang/{locale}/auth.php`. Placeholders usam `:nome`: `__('auth.throttle', ['seconds' => 30])`.
- **Fallback:** se a chave não existe no idioma atual, o Laravel usa o `fallback_locale`. O framework já traz os textos em inglês, dentro de `vendor/`. Por isso o `validation.php` do projeto só precisa conter as regras que usamos.
- **Ordem de resolução das mensagens de validação:**
  1. `messages()` do Form Request;
  2. `validation.custom.{campo}.{regra}`, usado para a mensagem "As senhas não conferem.";
  3. `validation.{regra}`; regras de tamanho têm subchaves por tipo, como `max.string` e `max.numeric`;
  4. o nome do campo em `:attribute` vem de `validation.attributes`.
- **Configuração:** `APP_LOCALE` no `.env` define o idioma, lido em [config/app.php](../config/app.php). Lembre que o `.env` sobrescreve o valor padrão do `config/app.php`.

### Por dentro do framework

- `vendor/laravel/framework/src/Illuminate/Validation/Concerns/FormatsMessages.php`: método `getMessage()`, com a ordem de resolução acima.
- `vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php`: todas as mensagens padrão, úteis como referência para traduzir.

### Para praticar

1. Adicione uma mensagem em `validation.custom.email.unique` e veja ela substituir a mensagem genérica no cadastro.

### Revisão

- Se uma chave existe em `en` mas não em `pt_BR`, o que aparece na tela com `APP_LOCALE=pt_BR`?

---

## Etapa 5: cadastro com DDD

### O que foi feito

| Camada | Arquivo | Papel |
|---|---|---|
| Interface | [RegisteredUserController](../app/Http/Controllers/Auth/RegisteredUserController.php), [RegisterRequest](../app/Http/Requests/Auth/RegisterRequest.php) | Recebe e valida a requisição e converte o resultado em resposta HTTP |
| Application | [RegisterUser](../app/Application/User/UseCases/RegisterUser.php), [RegisterUserData](../app/Application/User/DTOs/RegisterUserData.php) | Caso de uso: orquestra o domínio e dispara o evento |
| Domain | [UserRepository](../app/Domain/User/Contracts/UserRepository.php), [EmailAlreadyInUse](../app/Domain/User/Exceptions/EmailAlreadyInUse.php) | Contrato de persistência e regra de negócio expressa como exceção |
| Infrastructure | [EloquentUserRepository](../app/Infrastructure/User/Repositories/EloquentUserRepository.php) | Implementação do contrato com Eloquent |

A ligação entre contrato e implementação está no [AppServiceProvider](../app/Providers/AppServiceProvider.php).

### Conceitos

**Service container e injeção de dependência.** Repare que nenhum `new RegisterUser(...)` aparece no código. O que acontece:

1. O controller declara `RegisterUser $registerUser` como parâmetro do método. O Laravel resolve esse parâmetro pelo container (*method injection*).
2. Para criar o `RegisterUser`, o container lê o construtor por *reflection* e vê que ele precisa de um `UserRepository` e de um `Dispatcher` (*autowiring*).
3. `UserRepository` é uma interface e não pode ser instanciada. Por isso existe o `bind(UserRepository::class, EloquentUserRepository::class)`: ele diz ao container qual classe usar.
4. Diferença entre as formas de registro: `bind` cria uma instância nova a cada resolução; `singleton` cria uma só e a reutiliza; `scoped` reutiliza dentro da mesma requisição ou job.

Esse é o **D do SOLID** (inversão de dependência): o caso de uso depende da abstração, não do Eloquent. Nos testes, trocamos a implementação sem mexer no caso de uso (veja a Etapa 6).

**DTO.** O `RegisterUserData` transporta dados da camada HTTP para a camada de aplicação. Assim o caso de uso não conhece `Request` e pode ser chamado de um comando Artisan, de um job ou de um teste. Quem constrói o DTO é o próprio Form Request, no método `toData()`.

**Condição de corrida (TOCTOU, *time of check to time of use*).** A regra `unique:users` consulta o banco **antes** do insert. Se duas requisições com o mesmo e-mail chegarem juntas, as duas passam pela checagem e as duas tentam inserir. Quem garante a unicidade de verdade é o índice `unique` do banco:

- o segundo insert lança `UniqueConstraintViolationException`;
- o repositório converte essa exceção técnica em `EmailAlreadyInUse`, uma exceção de domínio;
- o controller converte a exceção de domínio em erro de validação.

Cada camada traduz o erro para a sua própria linguagem. A validação serve para dar uma boa mensagem ao usuário; a integridade dos dados quem garante é o banco.

Há também uma consequência do `SoftDeletes`: um usuário excluído continua ocupando o e-mail no índice `unique`. Por isso a regra `unique:users` conta também os registros excluídos.

**Eventos.** O caso de uso dispara `Illuminate\Auth\Events\Registered`. O Laravel 11 já registra o listener `SendEmailVerificationNotification` para esse evento, mas ele só age se o model implementar `MustVerifyEmail`. Eventos desacoplam o efeito colateral (enviar e-mail, registrar auditoria) da ação principal.

**Regras de senha.** `Password::defaults(fn () => Password::min(8)->max(255))` no `boot()` define a política uma única vez. Qualquer Form Request usa `Password::defaults()`, sem repetir a regra.

### Recursos de PHP usados

- `final readonly class` (PHP 8.2): todas as propriedades são somente leitura, e `final` impede herança. É ideal para DTOs imutáveis.
- *Constructor property promotion* (PHP 8.0): `public function __construct(public string $name)` declara e atribui a propriedade de uma vez.
- *Named arguments* (PHP 8.0): `new RegisterUserData(name: ..., email: ...)` deixa claro qual valor vai em qual parâmetro.
- `#[\SensitiveParameter]` (PHP 8.2): esconde o valor da senha em stack traces e logs de erro.
- *Named constructor*: `EmailAlreadyInUse::for($email)` é um método estático que cria a exceção com a mensagem pronta.
- *Non-capturing catch* (PHP 8.0): `catch (EmailAlreadyInUse)` sem variável, quando o objeto da exceção não é usado.

### Por dentro do framework

- `vendor/laravel/framework/src/Illuminate/Container/Container.php`: métodos `build()` (reflection e autowiring) e `resolveDependencies()`.
- `vendor/laravel/framework/src/Illuminate/Database/UniqueConstraintViolationException.php` e onde ela é lançada, em `vendor/laravel/framework/src/Illuminate/Database/Connection.php`.

### Para praticar

1. Troque o `bind` por `singleton` e reflita: neste caso faz diferença? Quando faria?
2. Crie um listener para o evento `Registered` que grava uma linha no log (`Log::info`). Use `php artisan make:listener` e confira se ele foi descoberto automaticamente com `php artisan event:list`.
3. Crie um comando Artisan `user:create` que reutiliza o caso de uso `RegisterUser`. Esse é o benefício prático do DTO.

### Revisão

- Por que a regra `unique` de validação não basta para garantir e-mails únicos?
- O que o container faz quando encontra uma interface no construtor e não há nenhum `bind` para ela?
- Por que o caso de uso recebe um DTO e não o `RegisterRequest`?

---

## Etapa 6: testes automatizados

### O que foi feito

- Testes de feature em [tests/Feature/Auth/LoginTest.php](../tests/Feature/Auth/LoginTest.php) e [RegisterTest.php](../tests/Feature/Auth/RegisterTest.php).

### Conceitos

- **Testes de feature:** simulam requisições HTTP completas (`$this->post(route('login'), [...])`), passando por rotas, middleware, validação e banco. Não há navegador; o Laravel processa a requisição em memória.
- **`RefreshDatabase`:** roda as migrations uma vez e envolve cada teste em uma transação desfeita no final, então cada teste começa com o banco limpo.
- **Banco nos testes:** no `phpunit.xml` do projeto as linhas de SQLite em memória estão comentadas, então os testes usariam o banco do `.env`, e o `RefreshDatabase` **apaga esse banco**. Rode sempre assim:

  ```bash
  DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test
  ```

- **Factories:** `User::factory()->create()` gera um usuário com dados falsos válidos. A senha padrão da factory é `password`.
- **Asserções usadas:**
  - `assertAuthenticatedAs($user)` e `assertGuest()`;
  - `assertSessionHasErrors(['email' => 'mensagem'])`;
  - `assertInertia(fn ($page) => $page->component('Auth/Login'))`;
  - `assertInertiaFlash('status', 'registered')`.
- **Fakes:** `Event::fake([Registered::class])` impede que os listeners executem e permite verificar o disparo com `Event::assertDispatched`.
- **Trocar uma implementação no container:** o teste de concorrência registra uma classe anônima que implementa `UserRepository` e sempre lança `EmailAlreadyInUse`. É o mesmo `bind` da Etapa 5, só que no teste, e simula uma situação difícil de reproduzir de verdade.

### Para praticar

1. Escreva um teste garantindo que um usuário excluído (soft delete) não consegue fazer login.
2. Use `$this->withoutExceptionHandling()` em um teste e veja a diferença na saída quando algo falha.

### Revisão

- Por que testar a condição de corrida trocando o repositório, em vez de disparar duas requisições ao mesmo tempo?

---

## Etapa 7: contrato entre backend e frontend tipado

### O que foi feito

- Frontend migrado para TypeScript ([ADR 0003](adr/0003-frontend-em-typescript.md)).
- As props enviadas por `HandleInertiaRequests::share()` estão declaradas em [resources/js/types/global.d.ts](../resources/js/types/global.d.ts).

### Conceitos

- Tudo que o `share()` devolve chega em **todas** as páginas. Por isso o `share()` deve conter só o necessário: cada prop aumenta o JSON de todas as respostas.
- O backend em PHP e o frontend em TypeScript não compartilham tipos automaticamente. Ao alterar o `share()`, atualize a interface `SharedData`. O mesmo vale para os dados de `Inertia::flash()` (interface `FlashData`).
- Limites de validação existem nos dois lados: por exemplo, a senha mínima de 8 caracteres está no `Password::defaults()` e em `resources/js/Schemas/fields.ts`. A validação do frontend melhora a experiência; a do backend é a que garante a segurança, porque requisições podem ser feitas sem passar pelo frontend.

### Revisão

- Por que nunca confiar apenas na validação do frontend?

---

## Etapa 8: redirecionar para o login após o cadastro

### O que foi feito

- O cadastro não faz mais login automático: redireciona para `/login` com `Inertia::flash('status', 'registered')`.
- A página Hello World foi substituída por um painel mínimo.

### Conceitos

- **Padrão PRG (Post/Redirect/Get):** depois de um POST bem-sucedido, responda com um redirecionamento, nunca com uma página. Se o usuário apertar F5, o navegador repete o GET, e não o POST, evitando criar a conta duas vezes.
- **Dados flash:** são gravados na sessão e existem só na **próxima** requisição. O `Inertia::flash()` usa esse mecanismo, com uma diferença em relação a uma prop comum: os dados não ficam salvos no histórico do navegador. Por isso a mensagem não reaparece ao voltar ou recarregar a página.
- **Enviar código, não texto:** o servidor envia `status: 'registered'` e o frontend traduz. Assim a mensagem não depende do idioma configurado no backend.

### Por dentro do framework

- `vendor/inertiajs/inertia-laravel/src/ResponseFactory.php`: método `flash()`.
- `vendor/laravel/framework/src/Illuminate/Session/Store.php`: métodos `flash()` e `ageFlashData()`, que mostram como o dado expira.

### Revisão

- O que aconteceria ao apertar F5 depois de um POST que devolve uma página em vez de redirecionar?

---

## Etapa 9: favicons e a pasta public

### O que foi feito

- Favicons, ícones para iOS/Android e `site.webmanifest` em `public/`, referenciados no [app.blade.php](../resources/views/app.blade.php).

### Conceitos

- `public/` é a única pasta exposta pelo servidor web. Arquivos que existem nela (`/favicon.ico`, `/assets/logo.webp`) são entregues diretamente pelo servidor, **sem passar pelo Laravel**: nenhum middleware, nenhuma sessão.
- Só o que não é arquivo existente vai para o `public/index.php`. Em produção, isso é configurado no Nginx ou Apache. No servidor embutido do PHP, quem faz esse papel é o `vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`.
- Nunca coloque arquivos sensíveis em `public/`. O `.env`, o código e o `vendor/` ficam fora dela justamente para não serem acessíveis pela URL.
- Diferença para os assets do Vite: arquivos em `public/` mantêm o nome (precisam de cuidado com cache); arquivos de `resources/` processados pelo Vite ganham hash no nome.

### Revisão

- Por que um arquivo em `public/` não passa pelo middleware de autenticação?

---

## Próximos tópicos de estudo

Tópicos que devem aparecer conforme o produto crescer, em ordem sugerida:

1. **Eloquent avançado:** relacionamentos, *eager loading* e o problema N+1, *scopes*, *casts* personalizados e *value objects*.
2. **Autorização:** *gates* e *policies*, com regras como "só o dono da loja pode editar".
3. **Multi-tenancy:** isolar os dados de cada loja (global scopes, `tenant_id`, middleware de identificação do tenant).
4. **Filas e jobs:** processar tarefas lentas fora da requisição (`ShouldQueue`, *retries*, *backoff*, *idempotência*).
5. **Eventos, listeners e observers:** quando usar cada um.
6. **Cache:** estratégias de cache e invalidação, *cache stampede*, *atomic locks*.
7. **APIs:** API Resources, Sanctum, versionamento e paginação.
8. **Container avançado:** *contextual binding*, *tagging*, *pipelines*, *macros*.
9. **Qualidade:** análise estática com Larastan (PHPStan), testes com Pest, CI.
10. **Desempenho e operação:** Octane, Horizon, Pulse, Telescope e o *scheduler*.
