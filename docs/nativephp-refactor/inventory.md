# Inventário de telas e fluxos — Refatoração NativePHP EDGE

Escopo: refatoração da interface WebView (Livewire 4) para telas nativas EDGE (NativePHP Mobile 4.7 + `nativephp/mobile-ui` 0.9.0).

Convenção de status: `nao-iniciado` · `em-andamento` · `migrado` · `validado` · `bloqueado` · `fora-de-escopo`.

---

## T1 — Início

| Campo | Valor |
|---|---|
| Rota atual | `GET /` (`Route::livewire`, `App\Livewire\HomeDashboard`) |
| Rota nativa | `GET /` (`Route::native`, `App\NativeComponents\Home`) |
| Tecnologia atual | Livewire 4 + Blade + Tailwind (WebView) |
| Dependências | `resources/views/livewire/home-dashboard.blade.php`, `resources/views/components/arena-shell.blade.php`, `resources/css/app.css` |
| Regras a preservar | Nenhuma regra de negócio. A tela só exibe 2 ações (Iniciar Duelo, Aparência) |
| Estados | Carregando (nativo: quadro inicial), vazio (n/A), erro (n/A), offline (n/A), permissão negada (n/A) |
| Destino | **EDGE nativo** — componente composto com `native:button` |
| Critério de aceite | Abre no Android; os 2 botões navegam para `/duel/new` e `/appearance/settings`; a tab "Início" fica ativa |
| Testes | `Native::test(Home::class)`, `Native::visit('/')` |
| Status | `migrado` — tela nativa implementada e coberta por testes; pendente de validação no aparelho Android |

Observação: `HomeDashboard::render()` consulta 5 duelos recentes e **não renderiza o resultado** — query morta. A tela nativa não repete essa query (paridade). Os duelos recentes ganharam tela própria (T2).

---

## T2 — Duelos Recentes

| Campo | Valor |
|---|---|
| Rota atual | — (não existe; os dados eram consultados e descartados em `HomeDashboard`) |
| Rota nativa | `GET /duels` (`Route::native`, `App\NativeComponents\RecentDuels`) |
| Tecnologia atual | — |
| Dependências | `App\Models\Duel` (+ `duelPlayers`) |
| Regras a preservar | Leitura de duelos existentes, ordered por `created_at DESC, id DESC`; nenhuma escrita |
| Estados | Carregando, vazio (sem duelos → estado explícito), erro (falha de banco) |
| Destino | **EDGE nativo** — `native:list` + `native:list-item`; toque abre a Arena (`/duel/{duel}`) |
| Critério de aceite | Lista os 5 duelos mais recentes com nome dos jogadores e PV atual; item vazio mostra estado "sem duelos"; toque navega para a Arena |
| Testes | `Native::visit('/duels')`, `Native::test(RecentDuels::class)` |
| Status | `migrado` — tela nativa implementada e coberta por testes; pendente de validação no aparelho Android |

---

## T3 — Configuração de Duelo

| Campo | Valor |
|---|---|
| Rota atual | `GET /duel/new` (`Route::livewire`, `App\Livewire\DuelSetup`) |
| Rota nativa | `GET /duel/new` (`Route::native`, `App\NativeComponents\DuelSetup`) |
| Tecnologia atual | Livewire 4 + Blade + Tailwind (WebView) |
| Dependências | `App\Models\Duel::startWith()`, `Duel::PLAYER_ACCENTS`, `Duel::PLAYER_ICONS`, `DuelPlayer::MAX_LIFE_POINTS` |
| Regras a preservar | `MIN_PLAYERS=1`, `MAX_PLAYERS=8`; nomes `required`, `array`, `min:1`, `max:8`, cada um `nullable|string|max:40`; cores `required|array|size:N` e `Rule::in(PLAYER_ACCENTS)`; ícones `required|array|size:N` e `Rule::in(PLAYER_ICONS)`; PV inicial `required|integer|min:1|max:999999`; mensagens PT-BR existentes |
| Estados | Carregando, erro de validação (por campo), sucesso (navigate para Arena) |
| Destino | **EDGE nativo** — `native:outlined-text-input` (nome), `native:select`/`native:chip` (cor/ícone), `native:slider`/`text-input` (PV inicial) |
| Critério de aceite | Criar duelo com 1–8 jogadores; validações com as mesmas mensagens; `Duel::startWith()` chamado com os mesmos dados; navega para a Arena |
| Testes | `Native::test(DuelSetup::class)` — cenário completo de validação + criação |
| Status | `migrado` — tela nativa implementada e coberta por testes; pendente de validação no aparelho Android |

---

## T4 — Arena de Duelo

| Campo | Valor |
|---|---|
| Rota atual | `GET /duel/{duel}` (`Route::livewire`, `App\Livewire\DuelArena`) |
| Rota nativa | `GET /duel/{duel}` (`Route::native`, `App\NativeComponents\DuelArena`) |
| Tecnologia atual | Livewire 4 + Blade + Tailwind + modais HTML/JS com `fetch` |
| Dependências | `Duel`, `DuelPlayer`, `LifePointEvent`, `Setting`, `Native\Mobile\Facades\Device` (haptics) |
| Regras a preservar | `subtract/add` com amount; `halveLifePoints`, `doubleLifePoints`, `restoreStartingLifePoints`, `setLifePointsTo`; faixa `1..999.999`; `InvalidArgumentException` → mensagem de erro; `undoLastLifePointChange`/`redoLastLifePointChange`; fases `DP, SP, M1, BP, M2, EP` (`DuelPhase`); `advanceTurn` (round/active_seat); sorteio do primeiro jogador (`random()` sobre `duelPlayers`); edição de identidade (nome ≤40, cor, ícone); ordem dos jogadores persistida em `Setting` (`duel.layout.order.{id}`); haptics com fallback para `Setting('duel.feedback.haptics')` e desativação automática quando `Device::vibrate()` falha; cronômetro (`MATCH_TIME_SECONDS=2400`, `startTimer/pauseTimer/resetTimer`) |
| Estados | Carregando, erro (mensagem de `InvalidArgumentException` exibida sem quebrar a tela), vazio (arena sem jogadores — impossível pelo modelo), sucesso (feedback de PV alterado) |
| Destino | **EDGE nativo** — pod do jogador composto, `native:button` para as ações de PV, `native:button-group`/chips para fases, `native:modal` para o cálculo de PV e a edição de identidade, `#[Poll]` 1 s para o cronômetro |
| Critério de aceite | Todas as ações de PV, fases, turno, undo/redo, cronômetro e haptics funcionam como na versão Livewire; `Device::vibrate()` continua sendo o único gatilho de vibração |
| Testes | `Native::test(DuelArena::class)` reescrevendo 1:1 os cenários de `tests/Feature/DuelArenaTest.php`, `DuelTurnTest.php`, `LifePointsTest.php` |
| Status | `migrado` — tela nativa implementada e coberta por testes; pendente de validação no aparelho Android |

### Diferenças deliberadas

| Item | Origem | Decisão |
|---|---|---|
| Rotação 180° do pod do oponente (`rotateOpponent`) | `resources/views/livewire/arena-two-player.blade.php` | **Removida** — nem o `TailwindParser` do core nem o `mobile-ui` 0.9.0 suportam rotação/transform. Ver `decisions.md` D3 |
| "Expandir painel" do jogador (`togglePlayerExpansion`) | `resources/views/livewire/arena-multi-duelists.blade.php` | **Removida** — afordâncio do layout em grade; na lista nativa cada participante já ocupa a largura inteira. Ver `decisions.md` D20 |
| Link "Contadores" do rodapé (`href="#"`) | `arena-shell.blade.php`, `arena-two-player.blade.php` | **Removido** — módulo fora de escopo (D4) |

### Funcionalidade ativada (não é port; ver `decisions.md` D12)

| Item | Situação Livewire | Decisão nativa |
|---|---|---|
| Cronômetro | API de domínio existe (`startTimer/pauseTimer/resetTimer`), mas **nenhuma view** tem controle nem `wire:poll` — o valor é renderizado uma vez e nunca conta | Ativado: botões iniciar/pausar/zerar + `#[Poll(1000)]`, usando a API de domínio |

### Regras migradas

| Regra | Onde está agora |
|---|---|
| Reordenar participantes (`movePlayer`) | `App\NativeComponents\DuelArena::movePlayer()` + ordem persistida em `Setting('duel.layout.order.{id}')` — idêntico ao legado |

---

## T5 — Ferramentas: dados

| Campo | Valor |
|---|---|
| Rota atual | `POST /duel/{duel}/dice-roll`, `GET /duel/{duel}/dice-rolls` (JSON, `DuelToolsController`) |
| Rota nativa | `GET /duel/{duel}/dice` (`Route::native`, `App\NativeComponents\DiceTools`) |
| Regras a preservar | Quantidade clampada `1..10`; resultado `random_int(1,6)` por dado; total; persistência em `DiceRoll` (`dice_count`, `results`, `total`); histórico dos 10 últimos com hora `H:i:s` |
| Estados | Carregando, erro (duelo inexistente), vazio (sem histórico), sucesso (resultado + total + histórico) |
| Destino | **EDGE nativo** — tela empilhada com `native:outlined-text-input` (quantidade) e `native:list` (histórico) |
| Critério de aceite | Lançar 1–10 dados mostra valores e total; histórico persiste e é listado |
| Testes | `Native::test(DiceTools::class)` |
| Status | `migrado` — tela nativa implementada e coberta por testes; pendente de validação no aparelho Android |

---

## T6 — Ferramentas: moeda

| Campo | Valor |
|---|---|
| Rota atual | `POST /duel/{duel}/coin-flip`, `GET /duel/{duel}/coin-flips` (JSON) |
| Rota nativa | `GET /duel/{duel}/coin` (`Route::native`, `App\NativeComponents\CoinTools`) |
| Regras a preservar | `random_int(0,1)` → `heads`/`tails`; persistência em `CoinFlip`; contadores de cara/coroa; total; histórico dos 20 últimos com hora `H:i:s` |
| Estados | Carregando, erro, vazio (sem histórico), sucesso |
| Destino | **EDGE nativo** — tela empilhada |
| Critério de aceite | Lançar mostra cara/coroa, contadores e histórico |
| Testes | `Native::test(CoinTools::class)` |
| Status | `migrado` — tela nativa implementada e coberta por testes; pendente de validação no aparelho Android |

---

## T7 — Ferramentas: lembretes

| Campo | Valor |
|---|---|
| Rota atual | `GET/POST/DELETE /duel/{duel}/reminders[/{reminder}]` + `POST .../toggle` (JSON) |
| Rota nativa | `GET /duel/{duel}/reminders` (`Route::native`, `App\NativeComponents\Reminders`) |
| Regras a preservar | `name` `required|string|max:120`; `description` `nullable|string`; `trigger_turn` `nullable|integer`; `trigger_phase` `nullable|string`; destaque quando o gatilho casa turno **e/ou** fase atuais; ordem `completed ASC, created_at ASC`; alternar concluído; excluir |
| Estados | Carregando, erro de validação, vazio (sem lembretes), sucesso, destaque de lembrete ativo |
| Destino | **EDGE nativo** — tela empilhada com `native:list` + `native:list-item` (swipe/trailing button) e `native:modal` para criar/editar |
| Critério de aceite | Criar, alternar e excluir lembretes com as mesmas validações; lembrete com gatilho correspondente fica destacado |
| Testes | `Native::test(Reminders::class)` |
| Status | `migrado` — tela nativa implementada e coberta por testes; pendente de validação no aparelho Android |

---

## T8 — Aparência

| Campo | Valor |
|---|---|
| Rota atual | `GET /appearance/settings` (`Route::livewire`, `App\Livewire\AppearanceSettings`) |
| Rota nativa | `GET /appearance/settings` (`Route::native`, `App\NativeComponents\AppearanceSettings`) |
| Tecnologia atual | Livewire 4 + `WithFileUploads` + CSS custom properties no `<body>` |
| Dependências | `App\Appearance` (fonte única de tema/wallpaper/scrim), `App\Enums\Theme`, `Setting`, `Native\Mobile\UI\Theme`, plugin `nativephp/mobile-camera` |
| Regras a preservar | 3 temas (`holographic`, `shadow-realm`, `minimal-arena`) com label/description; wallpaper JPEG/PNG/WebP, ≤2 MB, ≤4096×4096, erro quando a imagem não pode ser lida; opacidade do scrim clampada `0.0..0.8`; remoção do wallpaper apaga o arquivo |
| Estados | Carregando, erro (tipo/tamanho/dimensões inválidos), vazio (sem wallpaper), sucesso (tema aplicado, wallpaper salvo) |
| Destino | **EDGE nativo** — `native:select`/chips para tema, `Camera::pickImages('images')` (plugin `mobile-ui` + `mobile-camera`) para o wallpaper, `native:slider` para o scrim, `native-ui` `BackgroundLayer` para o wallpaper de fundo |
| Critério de aceite | Trocar de tema altera as cores do chrome nativo; escolher imagem do dispositivo salva e aplica como fundo; remover wallpaper limpa o fundo; scrim respeita 0–80% |
| Testes | `Native::test(AppearanceSettings::class)` |
| Status | `migrado` — tela nativa implementada e coberta por testes; pendente de validação no aparelho Android |

**Nota (D19).** O wallpaper passa a ser gravado em `public/wallpapers/` (em vez do disco `public` do Laravel): o renderer nativo resolve caminhos relativos de imagem contra o `public/` do app, e o symlink `public/storage` não existe no bundle do device.

---

## Fora de escopo

| Item | Origem | Motivo | Decisão |
|---|---|---|---|
| Módulo "Contadores & Campo" | `resources/views/livewire/partials/counters-modal.blade.php` | Mock estático com dados de exemplo; os steppers chamam `updateCounter()`, **função inexistente no repositório**; o link "Contadores" do rodapé é `href="#"`; não há modelo, controller nem regra | **Fora de escopo** (D4) — registrado como pendência |
| `resources/views/welcome.blade.php` | Laravel starter kit | Sem rota e sem referência | **Limpeza** (D16) |
| `resources/views/layouts/app.blade.php.bak` | Cópia manual | Sem referência | **Limpeza** (D16) |
| Rotas web (`routes/web.php`) | Livewire | App passa a ser device-only | **Remoção** (D9), na fatia de limpeza |

## Ativos de produto ausentes do código (registro, sem implementação nesta refatoração)

| Item | Situação |
|---|---|
| Papel de parede / scrim / temas | Implementados no Livewire; migrados em T8 |
| Papel de parede por galeria | Depende do plugin `nativephp/mobile-camera` (D7) |
| "Duelos recentes" | Consulta morta no Livewire; vira tela T2 |
| Cronômetro | API de domínio sem UI; ativado em T4 (D12) |
| Contadores de campo | Fora de escopo (D4) |

---

## Fechamento (2026-11-17)

| Item | Situação |
|---|---|
| Telas migradas | T1–T8 em EDGE nativo, com layouts `TabsLayout`/`StackLayout`/`WallpaperLayout` |
| WebViews mantidas | nenhuma — o app é device-only (D1) |
| Legacy removido | `app/Livewire`, views web, CSS/JS, Vite/Tailwind, `routes/web.php`, dependências `livewire/*` (D26, R7–R10) |
| Testes | 165 testes / 1057 assertions, 0 falhas, 0 warnings |
| Validação Android | Build 2 (`persistent`) instalado e iniciado no aparelho; checklist funcional e build final pós-limpeza pendentes de reconexão |
| Validação iOS | não executada (sem macOS/Xcode); arquitetura e plugins compatíveis por análise estática |
| Diferenças deliberadas | rotação do oponente (D3), "expandir painel" (D20), faixa de fases em lista vertical, eventos Livewire substituídos por vibração + última alteração visível |
