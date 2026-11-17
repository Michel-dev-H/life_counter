# Auditoria de limpeza — Refatoração NativePHP EDGE

Registro de remoções: arquivo/dependência, motivo, evidência de não utilização, validação executada.

**Estado atual: nenhuma remoção executada.** As remoções occurem na Fatia 6, depois que as telas nativas estiverem validadas no aparelho Android (decisão D14).

## Regras aplicadas

1. Nada é apagado antes de a substituição correspondente funcionar e estar validada.
2. Cada candidato é buscado globalmente (`rg`) antes da remoção: rotas, controllers, views, componentes Blade, includes, providers, autoload, assets/Vite, plugins NativePHP, eventos, listeners, jobs, comandos, traduções, e-mails, testes, factories, seeders, migrations e CI.
3. Ausência de referência textual isolada **não** prova que um item é morto — convenções do Laravel/NativePHP são verificadas antes.
4. Preservados sem discussão: `.env`, chaves, certificados, credenciais, `storage/`, dados reais, uploads, migrations históricas, `nativephp/` (plataforma), `nativephp.lock`, `native` (wrapper CLI), arquivos de instrução (`AGENTS.md`, `.ai/`, `.agents/`).

## Candidatos mapeados (pré-limpeza)

| Item | Categoria | Evidência de não uso | Decisão |
|---|---|---|---|
| `app/Livewire/*` (4 componentes) | Código | Substituído por `app/NativeComponents/*` após validação | Remover na Fatia 6 |
| `resources/views/livewire/**` (10 arquivos) | Views | Substituídas por `resources/views/native/**` | Remover na Fatia 6 |
| `resources/views/layouts/app.blade.php` | View | Layout Livewire (`#[Layout('layouts.app')]`) | Remover na Fatia 6 |
| `resources/views/layouts/primary-navigation.blade.php` | View | Usada apenas pelo layout Livewire | Remover na Fatia 6 |
| `resources/views/components/arena-shell.blade.php` | Componente Blade | Usado apenas pelas views Livewire | Remover na Fatia 6 |
| `resources/views/components/icon.blade.php` | Componente Blade | SVG inline; o EDGE usa `native:icon` (SF Symbols / Material) | Remover na Fatia 6 |
| `resources/views/layouts/app.blade.php.bak` | Cópia manual | Nenhuma referência (`grep` por `app.blade.php.bak`) | Remover (D16) |
| `resources/views/welcome.blade.php` | View do starter kit | Nenhuma rota nem `view('welcome')` no repositório | Remover (D16) |
| `resources/css/app.css` (279 linhas) | CSS | Consumido apenas por `@vite` no layout LiveView/WebView | Remover na Fatia 6 (se sem consumidor) |
| `resources/css/theme.css` | CSS | Tokens Tailwind do design system web | Remover na Fatia 6 (se sem consumidor) |
| `resources/js/app.js` (vazio) | JS | Sem conteúdo; só declarado no `@vite` | Remover na Fatia 6 |
| `vite.config.js`, `package.json` (Vite/Tailwind) | Build | Consumido apenas pelo pipeline web | Avaliar na Fatia 6 |
| `livewire/livewire` | Dependência Composer | Após a migração, nenhuma classe/vista Livewire é usada; usar `#[On]` (não `#[OnNative]`) | Remover na Fatia 6 |
| `livewire/blaze` | Dependência Composer | Otimizador de componentes Blade Livewire | Remover na Fatia 6 |
| `app/Http/Controllers/DuelToolsController.php` + 8 rotas JSON | Código | Regras absorvidas pelas telas nativas (D5) | Remover na Fatia 6 (com os testes correspondentes) |
| `routes/web.php` | Rotas | Substituído por `routes/mobile.php` (D9) | Remover na Fatia 6 |
| `all_tests.txt`, `test_before.txt`, `test_final.txt`, `test_orig.txt`, `test_output.txt`, `test_result.txt`, `test_result2.txt`, `test_run.txt`, `modify.py` | Scratch | Logs/scripts de execuções anteriores; nenhuma referência no código | Remover (D16) |
| `TEST_STATUS.md`, `IMPLEMENTATION_SUMMARY.md`, `CHANGES_BY_SECTOR.md` | Documentação | Histórico do projeto, não substituídos pelos artefatos desta refatoração | Mover para `docs/` (D16) |

## Itens preservados por segurança

| Item | Motivo |
|---|---|
| `database/migrations/**` | Migrations históricas — nunca remover |
| `database/database.sqlite` e dados | Dados do usuário |
| `storage/**` | Sessões, cache, logs, arquivos persistidos |
| `public/**` (ícones, splash, build) | Assets do app e artefatos de build |
| `nativephp/**`, `nativephp.lock`, `native` | Arquivos de plataforma e wrapper CLI do NativePHP |
| `.env`, `.env.example` | Configuração e credenciais |
| `AGENTS.md`, `.ai/rules/**`, `.agents/**` | Instruções do repositório |

## Registro de remoções

Cada entrada traz o item, o motivo, a evidência de não uso e a validação executada depois da remoção.

### R1 — `app/Http/Controllers/DuelToolsController.php` (removido)

| Campo | Conteúdo |
|---|---|
| Motivo | API JSON que existia apenas para alimentar os modais HTML/JS do WebView (`resources/views/livewire/partials/tools-modals.blade.php`), telas substituídas pelas telas nativas de ferramentas |
| Evidência de não uso | `rg "DuelToolsController"` → só `routes/web.php` e o arquivo de teste; `rg "duel.coin-flip\|duel.dice-roll\|duel.reminders\."` → só o controller, o teste e o `fetch()` do Blade legado (inalcançável: a rota nativa `/duel/{duel}/reminders` sombreia a rota JSON) |
| Substituição | `App\Models\Duel::rollDice()`, `flipCoin()`, `coinFlipCounts()`, `highlightedReminderIds()` + as telas nativas `DiceTools`, `CoinTools`, `Reminders` |
| Cobertura | `tests/Feature/DuelToolsTest.php` → migrado 1:1 para `DuelToolsDomainTest.php` (regras) e `DuelToolsScreenTest.php` (telas) |
| Validação | Suíte completa: 192 testes verdes |

### R2 — 8 rotas JSON de ferramentas em `routes/web.php` (removidas)

| Campo | Conteúdo |
|---|---|
| Motivo | Consumiam o controller removido; a URI `GET /duel/{duel}/reminders` colidia com a tela nativa |
| Evidência | `routes/web.php` após a remoção contém apenas as 4 rotas Livewire (legado, D14) |
| Validação | `php artisan route:list` + suíte completa verde |

### R3 — `tests/Feature/DuelToolsTest.php` (removido)

| Campo | Conteúdo |
|---|---|
| Motivo | Testava os endpoints HTTP removidos (R1) |
| Evidência de cobertura preservada | 8 cenários migrados para `DuelToolsDomainTest.php` + 11 para `DuelToolsScreenTest.php` (dados, moeda, lembretes, destaque por turno/fase, criar/concluir/excluir) |
| Validação | Suíte completa verde |

### R4 — `tests/Feature/AppearanceTest.php` (removido)

| Campo | Conteúdo |
|---|---|
| Motivo | Testava a tela de aparência Livewire e a marcação do layout web (`data-theme`, `wallpaper__scrim`), que não existe mais: a tela nativa usa o *theme store* e a camada de fundo nativa |
| Evidência de cobertura preservada | 8 cenários migrados para `AppearanceDomainTest.php` (tema, persistência, wallpaper do dispositivo, substituição, recusa de arquivo inválido, remoção, scrim) e 7 para `AppearanceScreenTest.php` (tela nativa: temas, seleção, scrim, remoção) |
| Validação | Suíte completa verde |

### R5 — `use InvalidArgumentException;` redundante em arquivo de teste (corrigido)

| Campo | Conteúdo |
|---|---|
| Motivo | Declaração sem efeito em namespace global → PHP warning "The use statement with non-compound name has no effect", reportado pelo PHPUnit |
| Validação | `vendor/bin/pest --log-events-text` → nenhum "PHP Warning" na execução |

### R6 — Tipagem genérica dos models (corrigido, não removido)

| Campo | Conteúdo |
|---|---|
| Motivo | 19 erros de phpstan nos models (genéricos do `HasFactory`/relações, `timer_started_at` como string) deixavam o `composer test`/`ci:check` vermelho |
| Evidência | `vendor/bin/phpstan analyse` → 25 erros antes da correção |
| Substituição | `@use HasFactory<XFactory>` + genéricos das relações + `@property` do `timer_started_at`; 3 factories novas (`CoinFlipFactory`, `DiceRollFactory`, `ReminderFactory`) |
| Validação | phpstan → 0 erros; `composer test` verde (252 testes / 1320 assertions) |

*(as remoções do legacy WebView — componentes Livewire, views web, CSS do WebView, pipeline Vite/Tailwind, dependências `livewire/*` — acontecem na Fatia 6, após a validação no aparelho Android, conforme D14)*

### R7 — Legacy WebView (componentes e views) removido

| Campo | Conteúdo |
|---|---|
| Removido | `app/Livewire/` (4 componentes), `resources/views/livewire/` (7 views + `partials/`), `resources/views/layouts/` (`app.blade.php` + `.bak`), `resources/views/components/` (`arena-shell`, `icon`, `primary-navigation`), `resources/views/welcome.blade.php` |
| Evidência de não uso | busca global por `App\Livewire`, `Livewire\`, `livewire.` e `<x-` fora de `vendor/`/`node_modules/` → **0 referências no app/testes**; as rotas nativas (`routes/mobile.php`) são as únicas registradas; `php artisan route:list` não lista mais `/`, `/duel/new`, `/duel/{duel}`, `/appearance/settings` como rotas web |
| Substituição | `app/NativeComponents/*`, `resources/views/native/*`, `app/Layouts/*`, `routes/mobile.php` |
| Cobertura preservada | `DuelArenaTest` → 40 testes de tela; `DuelTurnTest` → 18 de domínio + tela; `DuelSetupTest` → 14 de domínio + 18 de tela; `HomeDashboardTest` → `HomeTest` (5) |
| Validação | `php artisan test` → 165 testes / 1057 assertions, 0 falhas; `php artisan native:validate` → todos válidos |

### R8 — `routes/web.php` e o bloco `web:` do `bootstrap/app.php` removidos

| Campo | Conteúdo |
|---|---|
| Motivo | App device-only (D1/D9); `Route::native` já registra as rotas reais e só é carregado no device/testes/comandos `native:*` |
| Evidência | `route:list` passa a mostrar somente `_boost/*`, `_native/*`, `storage/*` e `up`; nenhum teste usa `$this->get()`/`$this->post()` |
| Validação | suíte completa verde; navegação nativa coberta por `HomeTest`, `RecentDuelsTest`, `DuelSetupScreenTest` |

### R9 — Pipeline Vite/Tailwind/JS removido

| Campo | Conteúdo |
|---|---|
| Removido | `resources/css/app.css`, `resources/css/theme.css`, `resources/js/app.js` (0 bytes), `vite.config.js`, `package.json`, `package-lock.json`, `public/build/` (gerado, não rastreado) |
| Evidência | nenhuma view nativa usa `@vite`/`@extends` (grep → 0); `nativephp/mobile` só usa Vite em `native:jump` (WebView dev), fora do fluxo device-only; `composer setup`/`dev` deixaram de chamar `npm` |
| Validação | `composer test` verde sem `node_modules`; bundle Android é montado pelo Composer, não pelo npm |

### R10 — Dependências `livewire/livewire` e `livewire/blaze` removidas

| Campo | Conteúdo |
|---|---|
| Comando | `composer remove livewire/blaze livewire/livewire --no-interaction` (após patch do D25) |
| Evidência | `composer show livewire/livewire` → não instalado; `composer.json`/`composer.lock` sem entradas `livewire/*`; `bootstrap/cache` regenerado por `php artisan package:discover` |
| Validação | suíte verde com o Livewire desinstalado; `composer audit` limpo |

*(build Android final pós-limpeza: pendente de reconexão do aparelho — `native:run` aborta na checagem do ADB)*
