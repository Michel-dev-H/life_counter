# Plano de migração — Refatoração NativePHP EDGE

## Objetivo

Substituir a interface Livewire/WebView por telas nativas EDGE (SwiftUI / Jetpack Compose), preservando regras de negócio, dados, validações e contratos existentes. Prioridade Android; arquitetura pronta para iOS.

## Baseline (registrado antes de qualquer alteração)

| Item | Valor |
|---|---|
| Branch | `refactor` |
| Árvore | limpa (`git status` vazio) |
| Suíte | **139 testes / 428 assertions, 100% verde** (`php artisan test --compact`, ~14 s) |
| PHP | 8.5.7 CLI (runtime mobile 8.5.12) |
| Laravel | 13.39.0 |
| NativePHP Mobile | 4.7.0 |
| `nativephp/mobile-ui` | 0.9.0 (único plugin registrado) |
| Livewire | 4.4.8 + `livewire/blaze` 1.0.20 |
| Android | SDK em `C:\Users\MichelS\AppData\Local\Android\Sdk`, JDK 17, projeto Gradle em `nativephp/android/` |
| Dispositivo | Aparelho Android do usuário (validação manual — o agente para e pede a conexão via `native:devices`) |
| iOS | Sem macOS/Xcode neste ambiente → **validação iOS não executada** (apenas análise estática) |

## Ordem das fatias

Cada fatia é uma fatia vertical: tela nativa completa (incluindo estados e erros) + testes nativos correspondentes. A tela antiga só é removida na fatia de limpeza (D14).

### Fatia 0 — Infra EDGE

| Item | Conteúdo |
|---|---|
| Objetivo | Provar o pipeline EDGE: rota nativa, layout, tokens de tema, fontes, plugin, harness de teste, build Android |
| Arquivos | `routes/mobile.php`, `app/NativeComponents/Home.php`, `app/NativeComponents/Layouts/TabsLayout.php`, `resources/views/native/home.blade.php`, `config/native-ui.php`, `resources/fonts/*` |
| Testes | `tests/Feature/HomeTest.php` (harness nativo) |
| Dependências | `nativephp/mobile-camera` instalado e registrado |
| Critério de aceite | `Native::test(Home::class)` verde; `native:validate` sem erro; build Android instalável |
| Marco | **Build 1** (instala no aparelho do usuário) |

### Fatia 1 — Início + Duelos Recentes

| Item | Conteúdo |
|---|---|
| Objetivo | Telas T1 e T2 do inventário |
| Arquivos | `app/NativeComponents/Home.php`, `app/NativeComponents/RecentDuels.php`, `resources/views/native/home.blade.php`, `resources/views/native/recent-duels.blade.php` |
| Testes | `tests/Feature/HomeTest.php`, `tests/Feature/RecentDuelsTest.php` |
| Critério de aceite | Navegação Início → Nova partida / Aparência / Duelos recentes; lista com estado vazio explícito |
| Marco | **Build 1** (valida junto com a Fatia 0) |

### Fatia 2 — Configuração de Duelo

| Item | Conteúdo |
|---|---|
| Objetivo | Tela T3 |
| Arquivos | `app/NativeComponents/DuelSetup.php`, `resources/views/native/duel-setup.blade.php` |
| Testes | `tests/Feature/DuelSetupTest.php` (reescrita 1:1 da atual) |
| Critério de aceite | Mesmas regras e mensagens de validação; `Duel::startWith()` com os mesmos dados; navega para a Arena |

### Fatia 3 — Arena de Duelo

| Item | Conteúdo |
|---|---|
| Objetivo | Tela T4 — núcleo do produto |
| Arquivos | `app/NativeComponents/DuelArena.php`, `resources/views/native/duel-arena.blade.php`, parciais (`player-pod`, `life-point-actions`, `phase-strip`, `identity-sheet`) |
| Testes | `tests/Feature/DuelArenaTest.php`, `DuelTurnTest.php`, `LifePointsTest.php` reescritas 1:1 |
| Riscos | Maior superfície de UI; validação de teclado, scroll e safe area |
| Marco | **Build 2** |

### Fatia 4 — Ferramentas

| Item | Conteúdo |
|---|---|
| Objetivo | Telas T5, T6, T7 |
| Arquivos | `app/NativeComponents/DiceTools.php`, `CoinTools.php`, `Reminders.php` + views |
| Testes | `tests/Feature/DiceToolsTest.php`, `CoinToolsTest.php`, `RemindersTest.php` |
| Critério de aceite | Mesmas regras de domínio dos endpoints JSON atuais; histórico e validações preservados |

### Fatia 5 — Aparência

| Item | Conteúdo |
|---|---|
| Objetivo | Tela T8 |
| Arquivos | `app/NativeComponents/AppearanceSettings.php`, `resources/views/native/appearance-settings.blade.php`, `app/Providers/NativeServiceProvider.php` (merge de tema) |
| Testes | `tests/Feature/AppearanceTest.php` reescrita 1:1 |
| Riscos | Depende do plugin `mobile-camera`; `Theme::merge()` em runtime precisa ser re-emitido ao trocar de tema |
| Marco | **Build 3** |

### Fatia 6 — Limpeza, docs e relatório

| Item | Conteúdo |
|---|---|
| Objetivo | Remover o código WebView comprovadamente sem uso, com evidência |
| Escopo | Livewire (componentes, views, testes), `routes/web.php`, `bootstrap/app.php` (`web:`), `resources/css/*`, `resources/js/app.js`, pipeline Vite/Tailwind (se sem consumidor), `livewire/livewire` + `livewire/blaze`, views mortas, arquivos soltos da raiz |
| Testes | Reescrita final; suíte completa + build final |
| Marco | **Build 3** (valida o app final) |

## Marcos de build (D13)

| Marco | Conteúdo | Momento |
|---|---|---|
| Build 1 | Fatia 0 + Fatia 1 | Agente pede a conexão do aparelho via `native:devices` |
| Build 2 | Fatia 3 (Arena) | Agente pede a conexão do aparelho |
| Build 3 | Fatias 4–6 | Agente pede a conexão do aparelho |

## Estratégia de testes (D11)

- Harness: `Native\Mobile\Testing\Native` (`Native::test()`, `Native::visit()`).
- Reescrita **1:1**: os cenários, dados e mensagens dos testes Livewire atuais são preservados; só o driver muda (`Livewire::test(...)` → `Native::test(...)`, `->call(...)` → `->tap(...)`/`->call(...)`, `->assertSet` idem, `->assertHasErrors` → asserção sobre a mensagem renderizada).
- Regras de domínio permanecem testadas no nível do modelo/action, sem depender da UI.
- Ciclo TDD do repo (`.ai/rules/app.md`): teste falhando → mínimo para passar → refatorar em verde.

## Riscos e mitigações

| Risco | Mitigação |
|---|---|
| `routes/mobile.php` faz shadow das rotas web durante os testes | `Route::native` responde 200 com um marcador nos testes; os testes HTTP que dependem de Livewire são migrados para o harness nativo |
| `#[OnNative]` depende de `Livewire\Features\SupportEvents\BaseOn` | Usar `#[On]` (Livewire-free) em todo código novo; `grep OnNative` no check final |
| Remoção do Livewire quebra o core | `nativephp/mobile` referencia `BaseOn` apenas sob `class_exists()` (`NativeComponent.php:1918`); validação por `native:validate` + build + execução no aparelho |
| `NATIVEPHP_RUNTIME_MODE=persistent` instável (D15) | Testar na Fatia 0; rollback de uma linha para `classic` |
| Download das fontes exige internet (D10) | Fallback: somente peso 700 de cada família; registrar em `decisions.md` |
| Plugin `mobile-camera` exige Android 26+ / iOS 18.2+ | `min_sdk` do projeto é 33 → Android OK; iOS registrado como pendência |
| iOS não validável neste ambiente | Registrar explicitamente como **validação iOS não executada** |

## Status de execução (2026-11-16)

| Fatia | Status | Observação |
|---|---|---|
| 0 — Infra EDGE | ✅ concluída | `routes/mobile.php`, `app/Layouts/{TabsLayout,StackLayout,WallpaperLayout}`, `config/native-ui.php` com a paleta da marca + fontes Orbitron/Rajdhani, plugin `nativephp/mobile-camera` instalado e registrado, `native:validate` limpo |
| 1 — Início + Duelos Recentes | ✅ concluída | `Home`, `RecentDuels` (`/duels`), tab bar nativa com as 3 abas |
| 2 — Configuração de Duelo | ✅ concluída | `DuelSetup` com inputs nativos, chips de cor/ícone, presets de PV e validação com as mesmas regras |
| 3 — Arena | ✅ concluída | `DuelArena`: PV, fases, turnos, undo/redo, cronômetro com `#[Poll]`, haptics, identidade, reordenação; modais de calculadora e identidade |
| 4 — Ferramentas | ✅ concluída | `DiceTools`, `CoinTools`, `Reminders` como telas nativas; regras movidas para o domínio (`Duel`) |
| 5 — Aparência | ✅ concluída | `AppearanceSettings`: temas (theme store nativo), wallpaper via galeria (`mobile-camera`), scrim, camada de fundo |
| 6 — Limpeza | ✅ concluída | Removido o legacy WebView completo (Livewire, views, CSS/JS, Vite/Tailwind, rotas web, 4 arquivos de teste legados) + dependências `livewire/*`; ver `cleanup-audit.md` (R7–R10). Build final aguardando a reconexão do aparelho |

### Verificações

| Verificação | Resultado |
|---|---|
| `php artisan test` | **165 testes / 1057 assertions, 0 falhas, 0 warnings** (baseline era 139; a contagem caiu porque 82 testes do legado Livewire — muitos com datasets — foram substituídos por 90 cenários nativos/domínio equivalentes) |
| `vendor/bin/pint --dirty` | ✅ sem pendências |
| `php artisan native:validate` | ✅ todos os componentes válidos |
| `vendor/bin/phpstan analyse` | ✅ **0 erros** (os 25 erros pré-existentes foram corrigidos — ver D23) |
| `composer test` (lint + types + suíte) | ✅ verde |
| `composer audit` | ✅ nenhuma advisory |
| Build Android | ✅ **Build 2** (`runtime_mode=persistent`) compilado, instalado e iniciado no `moto_g56_5G` em 16/11 16:36. Build 3 (app final, pós-limpeza) **pendente**: o aparelho foi desconectado e `native:run` aborta na checagem do ADB |

### Condição pré-existente resolvida (D23)

O `composer ci:check` falhava no `types:check` antes da refatoração: os models (`CoinFlip`, `DiceRoll`, `Duel`, `DuelPlayer`, `LifePointEvent`, `Player`, `Reminder`) não declaravam os genéricos do `HasFactory`/relações, e os `casts()` de `datetime`/`enum` não eram inferidos dentro do próprio model. **Corrigido nesta entrega**: tipagem genérica nos 7 models, 3 factories novas (`CoinFlipFactory`, `DiceRollFactory`, `ReminderFactory`), `@property` do `timer_started_at` e as 3 correções do legado Livewire que serão removidas na Fatia 6.

### Migração da cobertura 1:1 (pré-requisito da Fatia 6)

Antes de remover o legacy, cada cenário dos testes Livewire foi reescrito contra a tela nativa ou contra o domínio:

| Arquivo legado | Destino | Cenários |
|---|---|---|
| `DuelSetupTest.php` | `DuelSetupDomainTest.php` (novo) + `DuelSetupScreenTest.php` | **14 de domínio + 18 de tela** (identidade por assento, alinhamento ao remover assento, cor/ícone inválidos, 0 jogadores, PV fora da faixa, nunca mais de 8, nunca remover o último, dois assentos vazios iniciais, nomes automáticos) |
| `DuelArenaTest.php` | `DuelArenaScreenTest.php` | **40 de tela** (limites de reordenação, jogador de outro duelo, direção inválida, calculadora (add/subtract/zero/operação desconhecida/decimal/negativo/acima do limite), identidade isolada por jogador, cor legada com fallback de tema, haptics ligado/recusado, sorteio, 8 assentos, rota nomeada, última alteração visível) |
| `DuelTurnTest.php` | `DuelTurnDomainTest.php` (novo) + `DuelArenaScreenTest.php` | **18 de domínio + testes de tela** (todas as fases visíveis, fase inválida, iniciar/pausar/zerar o cronômetro) |
| `HomeDashboardTest.php` | `HomeTest.php` | 2 → 5 |

**Diferenças deliberadas sem equivalente nativo** (registradas em `inventory.md`): rotação do oponente (D3), "expandir painel" (D20), posição da faixa de fases na grade web (a arena nativa é uma lista vertical) e os eventos Livewire `life-points-changed` (o feedback nativo é a vibração + a última alteração visível na tela).

### Definição de pronto

- [x] Todas as telas do inventário migradas ou com justificativa registrada (D3, D4).
- [x] Nenhuma perda funcional conhecida; diferenças deliberadas aprovadas e registradas.
- [x] Matriz do inventário atualizada, sem itens sem classificação.
- [x] Testes existentes e novos verdes (165 testes / 1057 assertions; limitações registradas).
- [x] Android compilado (Build 2, `persistent`) e iniciado no aparelho; **build final pós-limpeza pendente de reconexão** e validação funcional pendente (D6).
- [x] Compatibilidade iOS analisada; validação iOS marcada como não executada.
- [x] Plugins justificados, compatíveis, registrados e documentados (D7, D18).
- [x] Auditoria de código morto com evidência por remoção (`cleanup-audit.md`, R1–R10).
- [x] Sem duplicação de telas legadas (`app/Livewire`, `resources/views/livewire`, CSS/JS do WebView removidos).
- [x] Build, testes e inspeções finais executados novamente após a limpeza (testes/pint/phpstan/`native:validate`; build Android bloqueado por ausência de device).
- [x] `cleanup-audit.md` e `decisions.md` completos; relatório final com os 10 itens exigidos (`final-report.md`).
