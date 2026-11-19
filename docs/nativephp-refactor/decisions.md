# Decisões — Refatoração NativePHP EDGE

Todas as decisões abaixo foram tomadas em entrevista com o usuário (Skill `grilling`, instalada no ambiente como `grilling`). Datas: 2026-11-13.

## Versões detectadas na Etapa 1 (diagnóstico)

| Componente | Versão |
|---|---|
| PHP (CLI / runtime mobile) | 8.5.7 / 8.5.12 |
| Laravel | 13.39.0 |
| `nativephp/mobile` | 4.7.0 |
| `nativephp/mobile-ui` | 0.9.0 |
| `livewire/livewire` | 4.4.8 |
| `livewire/blaze` | 1.0.20 |
| Tailwind / Vite | v4 (`@tailwindcss/vite` 4.1.11) / `vite-plus` 1.4.0 |

Documentação de referência: <https://nativephp.com/docs/mobile/4> (mesma major da instalada). Referência de elementos: `vendor/nativephp/mobile-ui/resources/boost/skills/nativephp-mobile-ui/SKILL.md` (o próprio pacote instalado).

---

## D1 — Escopo: app nativo puro (device-only)

**Decisão.** Remover a UI Livewire/WebView e servir tudo por telas nativas. Consequências: `routes/web.php` removido, `bootstrap/app.php` sem o parâmetro `web:`, Livewire desinstalado.

**Justificativa.** O app é um companheiro de duelo ao vivo (uso em mesa); a versão web não tem uso real. Manter as duas UIs duplicaria telas e violaria a regra DRY do repositorio (`.ai/rules/app.md`).

**Alternativas descartadas.** Dual (web + nativo) — duplicação e custo de manutenção; WebView como fallback — contraria o objetivo native-first.

**Impacto.** 139 testes Livewire precisam ser reescritos (ver D11). Cuidado técnico: `#[OnNative]` estende `Livewire\Features\SupportEvents\BaseOn` e passa a ser inutilizável — usar `#[On]`.

**iOS.** Sem impacto específico; a decisão vale para as duas plataformas.

---

## D2 — Identidade visual: tokens de marca + fontes nativas

**Decisão.** Reproduzir a identidade "Holographic" por meio de tokens de tema do `mobile-ui` (`config/native-ui.php`) + `Theme::merge()` em runtime para o tema escolhido, e empacotar as fontes Orbitron e Rajdhani no app.

**Justificativa.** O EDGE não renderiza o CSS do app: o styling vem do `TailwindParser` (PHP) + tokens `bg-theme-*`. Cores, raios e tipografia são reproduzíveis; glow/sombra/gradiente têm suporte limitado no parser, então a leitura de "tabuleiro de duelo" precisa se sustentar em cor, raio e tipografia.

**Alternativas descartadas.** Design system nativo puro (perde a identidade do produto); híbrido (só cor de destaque).

**Limitação registrada.** Não há `rotate`/transform no `TailwindParser` nem no `mobile-ui` 0.9.0 (verificado no código instalado) — ver D3.

**Impacto.** APK maior (fontes empacotadas, ver D10). iOS: mesma mecânica de fonte (`resources/fonts/` + alias em `config/native-ui.php`).

---

## D3 — Rotação de 180° do pod do oponente: removida

**Decisão.** A feature `rotateOpponent` (girar o pod do oponente para leitura em duelo de mesa) **não é migrada**.

**Justificativa.** O `TailwindParser` do core e o `mobile-ui` 0.9.0 não implementam rotação ou transform; `grep` por `rotate|rotation|flip` no código nativo instalado não retorna renderer correspondente. Não há elemento EDGE equivalente.

**Alternativas descartadas.** (b) inverter o conteúdo do pod — impreciso e ilegível; (c) manter WebView só para o pod — contraria D1 e reintroduz WebView; (d) plugin próprio de UI com renderer Compose (Android) + SwiftUI (iOS) — custo alto para um efeito cosmético, e plugin Android-only foi descartado por D13/D7.

**Diferença deliberada.** Registrada em `inventory.md` (T4) e no relatório final.

---

## D4 — Módulo "Contadores & Campo": fora de escopo

**Decisão.** Não migrar. Registrar como pendência.

**Justificativa.** O modal atual (`resources/views/livewire/partials/counters-modal.blade.php`) é um mock estático com dados de exemplo ("Link-4", "4 SC", "MAGO DO CAOS DAS TREVAS"); os steppers chamam `updateCounter()`, **função que não existe em nenhum arquivo do repositório** (verificado por busca global), e o link "Contadores" do rodapé é `href="#"`. Não há modelo, controller nem regra de negócio para a feature.

**Alternativas descartadas.** Migrar o mock como está — entrega uma UI quebrada; implementar de verdade — é funcionalidade nova e mistura feature com refatoração.

---

## D5 — Ferramentas: telas nativas empilhadas

**Decisão.** Dados, moeda e lembretes viram telas nativas próprias (`/duel/{duel}/dice`, `/coin`, `/reminders`), empilhadas a partir da Arena.

**Justificativa.** Lista com histórico + formulário + teclado são exatamente o caso em que bottom-sheet struggle; telas empilhadas dão back do sistema, scroll e foco corretos, e eliminam o JavaScript de `fetch` + manipulação de DOM.

**Alternativas descartadas.** Bottom-sheet sobre a Arena; WebView isolada.

**Impacto.** As regras dos endpoints JSON atuais (`DuelToolsController`) são preservadas no domínio; o controlador HTTP será removido na limpeza (D14/D16) depois de confirmadas as telas nativas.

---

## D6 — Validação: aparelho Android do usuário

**Decisão.** A validação manual acontece no aparelho Android do usuário. O agente compila, instala e pede a conexão via `php artisan native:devices` nos marcos definidos em D13.

**Justificativa.** Requisito da Skill: validar no Android no ambiente possível. Existe AVD `Medium_Phone_API_37.0` como alternativa, mas o aparelho do usuário é o alvo real.

---

## D7 — Plugin: `nativephp/mobile-camera` (oficial)

**Decisão.** Instalar e registrar `nativephp/mobile-camera` para o seletor nativo da galeria (papel de parede).

**Justificativa.** Não existe `<input type="file">` no EDGE; o facade `Camera` existe no core, mas o pacote `nativephp/mobile` **não traz implementação nativa** (sem Kotlin/Swift no projeto Android gerado). O plugin oficial é a única via de preservar a feature.

| Item | Avaliação |
|---|---|
| Necessidade atendida | Escolher imagem do dispositivo para o papel de parede |
| API | `Camera::pickImages('images', false)` → evento `MediaSelected` (via `#[On]`) |
| Compatibilidade | `nativephp/mobile-camera` 1.1.0 — MIT — NativePHP Mobile `^3.0 || ^4.0` |
| Android | min SDK 26 (projeto usa `min_sdk` 33 → OK); gallery via **Photo Picker** do sistema |
| iOS | 18.2+; Photo Library |
| Permissões | **Nenhuma** para apenas escolher da galeria (sem `getPhoto`, sem permissão de câmera) |
| Manutenção | Plugin oficial da mesma equipe do core, atualizado recently |
| Alternativa sem plugin | Manter só os temas nesta etapa (perda de feature) ou WebView (contraria D1) |

**Risco.** Arquivo picker cai em storage privado do app (`{noBackupFilesDir}/Gallery/`); será copiado para o disco `public` e o original removido (mesma mecânica de `Appearance::storeWallpaper`).

**iOS.** Compatível; sem validação de runtime (sem Xcode no ambiente).

---

## D8 — Navegação: `TabsLayout` com 3 abas

**Decisão.** Abas **Início | Partida | Aparência**. Arena, Ferramentas e Duelos Recentes são telas empilhadas (fora do grupo de layout), o que troca o chrome de tabs para stack nativamente.

**Justificativa.** Preserva o padrão mental do app atual (rodapé), dá abas reais ao rodapé — o "Contadores" atual é `href="#"` — e mantém o `back` do sistema na Arena.

**Alternativas descartadas.** Stack puro (perde o padrão de navegação atual); Arena como aba (não existe uma "Arena" genérica — a Arena é por duelo).

---

## D9 — Rotas web removidas

**Decisão.** `routes/web.php` é removido e `bootstrap/app.php` deixa de declarar `web:` (mantendo `health: '/up'`). As rotas nativas ficam em `routes/mobile.php`, carregado pelo core apenas no device, nos testes e nos comandos `native:*`.

**Justificativa.** `Route::native` registra uma rota real do Laravel; carregá-la no servidor web faria a requisição entrar no runloop nativo. O core já resolve isso: `routes/mobile.php` só é carregado quando `config('nativephp-internal.running')`, nos testes, em sessão Jump ou em comandos `native:*`.

**Impacto nos testes.** Com `routes/mobile.php` presente, os testes carregam as rotas nativas e `Route::native` responde 200 com um marcador. Os testes HTTP que dependiam de Livewire serão migrados para o harness nativo (D11).

---

## D10 — Fontes empacotadas: Orbitron (600/700/800) + Rajdhani (500/700)

**Decisão.** Empacotar as duas famílias com `php artisan native:font`, com aliases em `config/native-ui.php` (`display`, `headline`, `body`).

**Justificativa.** Preserva a identidade (D2) sem inflar excessivamente o APK.

**Risco.** O download exige internet no momento da preparação. Fallback acordado: peso 700 de cada família.

**iOS.** Mesmos arquivos; alias de fonte é do `mobile-ui`, compartilhado entre plataformas.

---

## D11 — "Duelos recentes": rota `/duels` + tela nativa

**Decisão.** A Home fica em paridade (2 botões) e ganha um botão "Duelos recentes" que abre a rota `/duels`, uma tela nativa com `native:list`.

**Justificativa.** `HomeDashboard` consulta 5 duelos e descarta o resultado (query morta). Rather than repetir a query na Home, a lista vira uma tela reaproveitável, deep-linkável e testável, sem apertar o `TabsLayout` (que fica com 3 abas, D8).

**Alternativas descartadas.** Bottom-sheet (não deep-linkável, lista limitada); 4ª aba (contraria D8).

---

## D12 — Cronômetro: ativado no nativo

**Decisão.** A Arena nativa ganha iniciar/pausar/reset e atualização por `#[Poll]` de 1 s, usando a API de domínio já existente (`startTimer`, `pauseTimer`, `resetTimer`, `timerRemainingSeconds`).

**Justificativa.** A API existe no domínio (`Duel`) e está coberta por regras de negócio, mas nenhuma view Livewire expõe controle nem `wire:poll` — o valor é renderizado uma vez e o cronômetro nunca conta. Deixar o cronômetro congelado na tela migrada entregaria um defeito conhecido; no nativo, `#[Poll]` custa uma linha.

**Natureza.** Ativação de feature existente no domínio, não criação de regra nova. Registrada como tal em `inventory.md` (T4).

---

## D13 — Marcos de build: 3

**Decisão.** Build 1 (Fatias 0–1), Build 2 (Fatia 3 — Arena), Build 3 (Fatias 4–6).

**Justificativa.** Cada build Gradle + instalação leva minutos e exige a conexão do aparelho do usuário; 3 marcos dão feedback precoce sem excessivas interrupções.

---

## D14 — Remoção do legado só na fatia de limpeza

**Decisão.** Livewire, views web, CSS do WebView e as dependências `livewire/*` permanecem durante as fatias 0–5 e são removidos na Fatia 6, após as telas nativas validadas no aparelho.

**Justificativa.** A Skill exige manter a implementação antiga até que a substituta esteja funcional e validada; o código legado serve de comparação de comportamento durante o desenvolvimento.

**Consequência.** As rotas nativas fazem shadow das rotas web durante os testes (D9); os testes HTTP legados são ajustados ao longo da migração e removidos junto com o código legado.

---

## D15 — Runtime: testar `persistent`

**Decisão.** Testar `NATIVEPHP_RUNTIME_MODE=persistent` na Fatia 0 e manter `classic` em caso de instabilidade.

**Justificativa.** O `.env` está em `classic` porque o modo `persistent` falhava **com Livewire no WebView** (registrado em `.ai/rules/nativephp-docs.md`). Sem WebView e sem Livewire, o modo `persistent` (default do core) passa a ser viável: ~5–30 ms por requisição em vez de ~200–300 ms.

**Rollback.** Uma linha de `.env`.

**Resultado.** `persistent` foi mantido e o Build 2 foi empacotado com `runtime_mode=persistent` (registrado em `bundle_meta.json`), instalado e iniciado no aparelho com sucesso. A validação funcional completa (checklist do usuário) continua pendente; se algo falhar, o rollback é `NATIVEPHP_RUNTIME_MODE=classic`.

---

## D16 — Limpeza: o que remove

**Decisão.**
- **Remover:** `all_tests.txt`, `test_before.txt`, `test_final.txt`, `test_orig.txt`, `test_output.txt`, `test_result.txt`, `test_result2.txt`, `test_run.txt`, `modify.py` (logs/scratch de execuções anteriores), `resources/views/welcome.blade.php` (view do starter kit, sem rota), `resources/views/layouts/app.blade.php.bak` (cópia sem referência).
- **Mover** para `docs/`: `TEST_STATUS.md`, `IMPLEMENTATION_SUMMARY.md`, `CHANGES_BY_SECTOR.md` (histórico do projeto).
- **Preservar:** `.env`, chaves, certificados, `storage/`, dados, migrations, arquivos de plataforma (`nativephp/`), `nativephp.lock`, `native` (wrapper CLI), `AGENTS.md` e demais arquivos de instrução.

**Justificativa.** Os arquivos removidos não têm qualquer referência no código; a árvore git está limpa, portanto não há trabalho não commitado do usuário em risco.

**Regra de execução.** Cada remoção é registrada em `cleanup-audit.md` com evidência de não uso (busca global por referências diretas e indiretas: rotas, views, providers, autoload, assets, testes, CI).

---

## D17 — Testes: reescrita 1:1

**Decisão.** Os 139 testes atuais são reescritos com o harness nativo (`Native::test()`, `Native::visit()`), preservando cenários, dados e mensagens.

**Justificativa.** O harness nativo oferece `assertSee`, `assertSet`, `tap`, `call`, `assertHasErrors` via mensagem renderizada, `assertNavigatedTo` — dá para manter a intenção de cada teste. Reduzir a cobertura de UI cortaria a rede de segurança na fase de maior risco.

---

## D18 — Nenhum plugin Android-only; nenhum plugin de terceiros além do oficial

**Decisão.** Nenhum plugin Android-only é aceito como dependência estrutural. Nenhum plugin comunitário ou próprio é adotado. O único plugin novo é o oficial `nativephp/mobile-camera` (D7), cross-platform.

**Justificativa.** A hierarquia de escolha da Skill (core → EDGE → componente composto → plugin oficial → plugin comunitário → plugin próprio → WebView) é inteiramente coberta por `nativephp/mobile` + `nativephp/mobile-ui` 0.9.0. As únicas capacidades ausentes (galeria) têm plugin oficial cross-platform.

**iOS.** Todo componente e plugin adotado tem implementação Android **e** iOS no pacote instalado (`resources/android/` e `resources/ios/` do `mobile-ui`).

---

## D19 — Papel de parede passa a morar em `public/wallpapers`

**Decisão.** O wallpaper deixa o disco `public` (`storage/app/public`) e passa a ser gravado em `public/wallpapers/`, dentro do diretório `public/` do app.

**Justificativa.** O renderer nativo resolve um caminho de imagem relativo contra o `public/` do app (`Native\Mobile\Edge\ImageSource::forDevice()`: "a relative path means *an asset I shipped in public/*"). O disco `public` do Laravel exigiria o symlink `public/storage`, que não existe no bundle do device (e não existia neste repositório). Gravando em `public/wallpapers`, o mesmo arquivo funciona no device, sob Jump (o `ImageSource` converte para URL `asset()`) e na versão web.

**Efeito colateral.** Papéis de parede gravados pela versão anterior (disco `public`) deixam de ser encontrados. O app ainda não foi publicado; nada em produção é afetado.

**Limites preservados.** JPEG/PNG/WebP, ≤2 MB, ≤4096×4096 px, com as mesmas mensagens de erro — agora validados sobre o arquivo escolhido no dispositivo (`Appearance::assertValidWallpaper()`).

**Impacto no `Appearance`.** `wallpaperPath()` passou a devolver o caminho relativo a `public/` e a ser cacheado por instância (o app é ligado como singleton): a camada de fundo do layout pede o wallpaper a cada publicação e a Arena faz *poll* a cada segundo — sem o cache, seria uma query por segundo.

---

## D20 — "Expandir painel do jogador" não é migrado

**Decisão.** A ação `togglePlayerExpansion` (que, na versão web, ocupava a linha inteira do painel do jogador numa grade) não existe na Arena nativa.

**Justificativa.** A arena nativa é uma lista vertical: cada participante já ocupa a largura inteira da tela, e não há "coluna inteira" a ganhar. A feature era um afordâncio do layout em grade (2 colunas / 8 jogadores), não uma capacidade. Nenhum dado é alterado por ela.

**Alternativas descartadas.** (a) Reproduzir com `col-span-full` — não existe no pipeline nativo; (b) inventar um equivalente com o histórico de pontos de vida — seria funcionalidade nova, fora do escopo da refatoração.

**Diferença deliberada**, registrada em `inventory.md` (T4), junto com a rotação do oponente (D3).

---

## D21 — Regras das ferramentas movidas para o domínio; controller JSON removido

**Decisão.** As regras de dados, moeda e lembretes passaram a viver no modelo `Duel` (`rollDice()`, `flipCoin()`, `coinFlipCounts()`, `highlightedReminderIds()`), e o `DuelToolsController` (8 endpoints JSON) foi removido junto com as rotas correspondentes e o arquivo de testes HTTP.

**Justificativa.** Os endpoints JSON existiam apenas para alimentar os modais HTML/JS do WebView (`resources/views/livewire/partials/tools-modals.blade.php`), telas que deixaram de existir. As telas nativas chamam o domínio diretamente. Manter o controller manteria uma API metade sombreada pelas rotas nativas (`GET /duel/{duel}/reminders` respondia a tela nativa, não JSON) e sem consumidor.

**Cobertura preservada.** Os cenários do arquivo `tests/Feature/DuelToolsTest.php` foram migrados 1:1 para `tests/Feature/DuelToolsDomainTest.php` (regras) e `tests/Feature/DuelToolsScreenTest.php` (telas nativas).

**Regra do repositório respeitada.** "Regra de três: duas cópias com o mesmo motivo de mudança podem ficar" — a duplicação temporária entre o controller legado e o domínio não chegou a existir: as regras foram movidas, não duplicadas.

---

## D22 — Dependências Symfony atualizadas (patch de segurança)

**Decisão.** `symfony/http-foundation`, `symfony/http-kernel`, `symfony/yaml` e demais `symfony/*` foram atualizados de v8.1.10 para v8.1.11 (e v3.7.x → v3.8.1 em alguns componentes), dentro do mesmo major.

**Justificativa.** O `composer.lock` do repositório carregava versões com CVEs publicados (CVE-2026-118514, PKSA-pr6q-2w5s-kzzf, PKSA-nxjs-s6hn-vb6m, PKSA-b2gg-vk1g-6d5q, PKSA-y8c5-1b7f-r1gj). O Composer 2.9 bloqueia qualquer operação de dependência com pacotes afetados (`audit.block-insecure`), o que impedia instalar o plugin aprovado em D7. O patch aplicado é exatamente o que as advisories exigem.

**Impacto.** Nenhuma mudança de major em Laravel, PHP, NativePHP ou Livewire. `composer audit` → "No security vulnerability advisories found".

**Registro.** `composer.lock` alterado; `composer.json` alterado apenas pela entrada de `nativephp/mobile-camera` (D7).

---

## D23 — Tipagem genérica nos models (corrige o CI vermelho)

**Decisão.** Os 7 models (`CoinFlip`, `DiceRoll`, `Duel`, `DuelPlayer`, `LifePointEvent`, `Player`, `Reminder`) passaram a declarar os genéricos do `HasFactory` e das relações (`@use HasFactory<XFactory>`, `BelongsTo<Duel, CoinFlip>`, `HasMany<…>`, `HasOne<…>`), com `@property \Illuminate\Support\Carbon|null $timer_started_at` no `Duel`. Foram criadas as 3 factories que faltavam (`CoinFlipFactory`, `DiceRollFactory`, `ReminderFactory`). Os 3 erros restantes (legado Livewire) foram corrigidos: `TemporaryUploadedFile` no componente de aparência, `@return array{remaining: int, running: bool}` no `syncTimer()` e remoção de `array_values()` redundante no setup.

**Justificativa.** `composer test` (e portanto `ci:check`, que chama `@test`) inclui `types:check` — o CI estava **vermelho** desde antes da refatoração por causa desses 25 erros. Não é escopo novo: é a dívida que a refatoração ia deixar para trás e que o usuário decidiu assumir nesta entrega. O `@property` do `timer_started_at` também é a forma idiomática do Laravel (o model `User` já usava `@property` para `email_verified_at`).

**Efeito colateral.** A tipagem do `BelongsTo` do `DuelPlayer` resolve automaticamente o erro "Call to an undefined method Model::duelPlayers()" — o `$this->duel` agora é `Duel`, não `Model`.

**Validação.** `vendor/bin/phpstan analyse` → 0 erros; `composer test` → verde; 252 testes / 1320 assertions.

---

## D24 — Erros de validação da Arena nativa que não eram exibidos

**Decisão.** A view nativa da Arena passou a renderizar os erros de `operation` (calculadora) e `layout` (reordenação), além dos que já eram exibidos (`amount`, `hapticFeedback`, `phase`, `input`, `editingPlayer*`).

**Justificativa.** Bug real encontrado na migração dos testes 1:1: o `ValidationException` levantado pelo `ValidatesInput` nativo é convertido em erros no *error bag*, mas a view não tinha `@error(...)` para `operation` nem `layout` — o usuário tocava em "Aplicar" com operação inválida e **não acontecia nada na tela**. O mesmo vale para a reordenação com direção inválida.

**Correção relacionada.** `DuelSetup::__syncProperty()` agora re-sincroniza cor/ícone quando o número de assentos muda: antes, trocar a capacidade do duelo deixava os arrays de identidade menores que a lista de jogadores e a view quebrava com "Undefined array key" (tela branca). A view também passou a usar `?? null` nos índices de identidade.

**Validação.** `DuelSetupScreenTest` (16 testes) e `DuelArenaScreenTest` (30 testes) cobrem os três casos.

---

## D25 — Segunda rodada de patches de segurança (Laravel 13.42)

**Decisão.** `laravel/framework` subiu de 13.39.0 para 13.42.0 (e as `illuminate/*`/`symfony/*`/`guzzle`/`monolog` junto), dentro do mesmo major `^13.17`.

**Justificativa.** O Composer 2.9 passou a bloquear qualquer operação de dependência por causa de **CVE-2026-128083** (Task Output Injection — Concurrency Process, severidade baixa, `laravel/framework >=13.0.0,<13.40.0`). Sem o patch, `composer remove livewire/*` era abortado. É o mesmo procedimento do D22: patch dentro do major, sem mudança de API pública.

**Validação.** `composer audit` → nenhuma advisory; suíte completa verde (165 testes); phpstan 0; pint limpo.

---

## D26 — Limpeza final executada (Fatia 6)

**Decisão.** Removido o legado WebView e suas dependências, com evidência em `cleanup-audit.md` (R7–R10):

- `app/Livewire/*` (4), `resources/views/livewire/*` (7 + partials), `resources/views/layouts/*` (2), `resources/views/components/*` (3), `resources/views/welcome.blade.php`;
- `resources/css/*` (2), `resources/js/app.js`, `vite.config.js`, `package.json`, `package-lock.json` (o app não tem mais pipeline JS/Vite/Tailwind);
- `routes/web.php` e a entrada `web:` do `bootstrap/app.php` (D9);
- `livewire/livewire` + `livewire/blaze` (`composer remove`);
- 4 arquivos de teste legados (`DuelArenaTest`, `DuelTurnTest`, `DuelSetupTest`, `HomeDashboardTest`) e os scratch da raiz (`test_*.txt`, `all_tests.txt`, `modify.py`); `TEST_STATUS.md`, `IMPLEMENTATION_SUMMARY.md` e `CHANGES_BY_SECTOR.md` movidos para `docs/`.

**Justificativa.** As telas nativas que os substituem estão implementadas e cobertas por testes (D17); a cobertura 1:1 foi migrada antes da remoção e não perdeu cenário relevante. `composer test`, `native:validate` e phpstan rodam verdes sem Livewire instalado.

**Pendente.** Build Android final (3) após a reconexão do aparelho — `native:run` aborta na checagem do ADB sem device. O Build 2 (pré-limpeza, `persistent`) permanece instalado para comparação.

---

## D27 — Boot do tema antes das migrations (crash na tela de loading)

**Decisão.** `App\Appearance::applyNativeTheme()` passou a capturar `Throwable` e manter a paleta publicada em `config/native-ui.php` quando o banco ainda não tem a tabela `settings`. O `NativeServiceProvider::boot()` continua empurrando o tema no boot nativo; só o acesso ao banco ficou tolerante. Um guard com `runningInConsole()` foi descartado: o próprio vendor avisa que ele lê `PHP_SAPI` (`vendor/nativephp/mobile/src/NativeServiceProvider.php:741`), e o runtime embarcado não se reporta como web — o guard poderia desligar o tema no device.

**Causa raiz.** O provider passou a consultar `settings` em todo boot. No device o runtime define `NATIVEPHP_RUNNING=true` e roda `optimize:clear`, `migrate --force`, `config:cache`, `event:cache` e `view:cache` antes de qualquer requisição (`LaravelEnvironment.kt:210-215` → `777-809`). Em banco vazio o primeiro boot já lança `no such table: settings`, o `migrate` morre no mesmo boot e o banco nunca é criado — deadlock que se repete em todo cold start. O app fechava porque, ao renderizar a exceção, o Collision explodia com `Undefined constant "NunoMaduro\Collision\STDOUT"` (fatal). Não é específico do `persistent`: o defeito é o boot acessar o banco, e ocorre igual no `classic`.

**Evidência (moto_g56_5G, 2026-11-17).** `app_storage/persisted_data/database/database.sqlite` com **0 bytes**; `laravel.log` do device com **63 ocorrências** de `SQLSTATE[HY000]: no such table: settings`, com a pilha em `Runtime::boot()` → `Application::bootProvider(App\Providers\NativeServiceProvider)` → `App\Appearance::theme()`; em seguida o fatal do Collision. O bundle instalado era o Build 2 (pré-limpeza, ainda com `app/Livewire`, `routes/web.php` e `public/build` dentro do zip).

**Validação.** Reprodução local com SQLite vazio e `NATIVEPHP_RUNNING=true`: antes o `php artisan about` saía 1 no boot; depois `migrate --force` sai 0 e cria `settings`. Teste de regressão `keeps the boot alive when the settings table does not exist yet` em `AppearanceDomainTest` (falhou antes com o mesmo erro do device). Gate: 166 testes / 1058 assertions, phpstan 0, pint limpo, `native:validate` limpo (167/1059 depois da guarda de D28).

**Pendente.** Build 3 (pós-limpeza + D27) e validação funcional no `moto_g56_5G`. Neste ambiente o `native:run` morre durante a cópia do bundle (antes de instalar), inclusive destacado do shell — o build precisa ser rodado em terminal próprio (`php artisan native:run android` ou `gradlew assembleDebug` + `adb install`). O banco de 0 bytes permanece no device e é exatamente o caminho que a correção desbloqueia no primeiro launch.

## D28 — `native:list` nunca dentro de `native:scroll-view`

**Contexto.** Na validação no aparelho (2026-11-17), quatro telas derrubavam o app ao abrir: Duelos Recentes, Lembretes, Moeda e Dados. O logcat registrava `java.lang.IllegalStateException: Vertically scrollable component was measured with an infinity maximum height constraints`: o Compose não aceita uma lista rolável dentro de um container com altura infinita.

**Decisão.** Uma lista nunca fica dentro de `scroll-view`. A tela é uma `native:column class="w-full h-full"` com o conteúdo fixo e `<native:list class="w-full flex-1">` como filho direto — o `flex-1` fornece a altura limitada que o Compose exige. Conteúdo rolável que não é lista continua em `scroll-view`.

**Motivo de virar regra.** O erro só aparece em runtime, no device; nem teste de unidade nem `native:validate` o detectam, e um `scroll-view` em volta parece inofensivo em revisão.

**Guarda.** `tests/Feature/NativeListLayoutTest.php` varre as views e falha se `native:list` aparecer dentro de `native:scroll-view`. Gate após a correção: 167 testes / 1059 assertions.

## D29 — `docs/pendencias/` como fonte de continuidade

**Contexto.** O backlog original (`docs/tarefas/001–034`) descreve a stack Laravel/Livewire + WebView, encerrada em D1. Depois da refatoração não havia um lugar por tela que dissesse o que existe, o que falta e o que já foi validado no aparelho.

**Decisão.** Um arquivo por tela em `docs/pendencias/`, mais `chrome-e-modais.md` para o que é compartilhado e `README.md` como índice. Cada arquivo traz: contexto, estado atual com `arquivo:linha`, tabela de pendências com origem e critério de aceite, checklist de validação no aparelho, regras/skills por referência curta, arquivos a inspecionar e registro de execução. Status só em `Pendente`, `Implementado — não validado`, `Validado no aparelho` ou `Fora de escopo`; sem linha no registro de execução, a pendência continua pendente.

**Série antiga.** `docs/tarefas/001–034` fica preservada como histórico, com banner no `README.md`, sem ser atualizada nem apagada. As decisões deste arquivo prevalecem sobre qualquer anotação antiga.

**Regras.** `.ai/rules/nativephp-docs.md` foi reescrito para o EDGE atual e mapeado no `.ai/rules/index.md`, incluindo a tabela de "histórico superado — não seguir".
