# Relatório final — Refatoração NativePHP para EDGE

**Data:** 2026-11-17
**Branch:** `refactor` · **Árvore:** alterações **não commitadas** (conforme decisão do usuário)

## 1. Versões e dependências detectadas

| Item | Versão |
|---|---|
| PHP (CLI) | 8.5.7 |
| PHP (runtime do device) | 8.5.12 |
| Laravel | 13.42.0 (patch de segurança aplicado em D25) |
| NativePHP Mobile | 4.7.0 |
| `nativephp/mobile-ui` | 0.9.0 |
| `nativephp/mobile-camera` | 1.1.0 (plugin oficial, Android + iOS) |
| Livewire / Blaze | **removidos** (eram 4.4.8 / 1.0.20) |
| Pest / PHPStan / Pint | 5.3.x / larastan 3.13 + phpstan 2.x / 1.x |
| Android | SDK em `%LOCALAPPDATA%\Android\Sdk`, JDK 17, projeto Gradle em `nativephp/android/` |
| Dispositivo de validação | `moto_g56_5G` (`ZF525FLTVF`, pacote `com.personal.lifecounter`) |
| iOS | sem macOS/Xcode neste ambiente |

## 2. Arquitetura adotada

- **EDGE nativo puro** (`native:button`, `native:scroll-view`, `native:list`, `native:modal`, `native:text`, `native:outlined-text-input`, `native:chip`, `native:pressable`, `native:slider`, `native:select`), sem WebView.
- Componentes em `app/NativeComponents/`: `Home`, `RecentDuels`, `DuelSetup`, `DuelArena`, `DiceTools`, `CoinTools`, `Reminders`, `AppearanceSettings`.
- Layouts em `app/Layouts/`: `TabsLayout` (3 abas: Início/Partida/Aparência), `StackLayout` (telas empilhadas) e `WallpaperLayout` (camada de fundo com scrim).
- Rotas em `routes/mobile.php` (`Route::native`/`nativeGroup`), carregadas apenas no device, nos testes, em sessão Jump e nos comandos `native:*`.
- Tema da marca preservado em `config/native-ui.php` (`Theme::merge()` no boot nativo) + fontes Orbitron/Rajdhani empacotadas.
- Regras de negócio permanecem no domínio (`Duel`, `DuelPlayer`, `Appearance`, `Setting`), reutilizadas pelas telas; nenhum controller/endpoint intermediário.
- Cronômetro vivo via `#[Poll]` de 1 s, usando a API de domínio já existente (D12).

## 3. Telas migradas e WebViews mantidas

| Tela | Rota nativa | Situação |
|---|---|---|
| Início | `/` | migrada |
| Duelos recentes | `/duels` | migrada (substitui a query morta do `HomeDashboard`) |
| Configuração de duelo | `/duel/new` | migrada |
| Arena | `/duel/{duel}` | migrada |
| Dados | `/duel/{duel}/dice` | migrada |
| Moeda | `/duel/{duel}/coin` | migrada |
| Lembretes | `/duel/{duel}/reminders` | migrada |
| Aparência | `/appearance/settings` | migrada |

**WebViews mantidas: nenhuma.** O app é device-only (D1).

Diferenças deliberadas: sem rotação 180° do pod do oponente (D3); "expandir painel" não migrado (D20); faixa de fases em lista vertical (a grade web não existe no pipeline nativo); eventos Livewire `life-points-changed` substituídos por vibração + "última alteração" visível. O módulo "Contadores & Campo" ficou fora de escopo (D4).

## 4. Plugins adotados, rejeitados ou criados

| Plugin | Decisão | Motivo |
|---|---|---|
| `nativephp/mobile-ui` 0.9.0 | adotado (já instalado) | componentes EDGE + tema + `BackgroundLayer`; Android e iOS |
| `nativephp/mobile-camera` 1.1.0 | **adotado** (D7) | única capacidade ausente: escolher wallpaper da galeria; oficial e cross-platform (Android 26+/iOS 18.2+; `min_sdk` 33) |
| Plugins comunitários/próprios | rejeitados (D18) | a hierarquia core → EDGE → componente composto → plugin oficial cobre o necessário |
| Plugins Android-only | rejeitados (D18) | sem estratégia iOS viável |

## 5. Funcionalidades preservadas

- Domínio intacto: PV (0..999.999), operações +/−/definir/dobrar/metade/restaurar, undo/redo por evento, turnos/rodadas/fases, dados, moeda, lembretes, duelos recentes, temas, wallpaper e scrim.
- Todas as mensagens de validação preservadas (nomes, cores, ícones, PV, anos, operação, direção, wallpaper).
- Banco, migrations históricas, dados, autorização/validações e contratos existentes não foram alterados.
- Cronômetro passou a **contar** (era API sem UI no legado) — ativação de feature existente, registrada em D12.
- Papel de parede agora em `public/wallpapers/` (D19), com os mesmos limites e mensagens (JPEG/PNG/WebP, ≤2 MB, ≤4096×4096).

## 6. Arquivos e dependências removidos (com evidência)

| Removido | Evidência |
|---|---|
| `app/Livewire/*` (4), `resources/views/livewire/*` (7 + partials), `resources/views/layouts/*` (2), `resources/views/components/*` (3), `welcome.blade.php` | busca global por `App\Livewire`/`Livewire\`/`livewire.`/`<x-` → 0 referências; `route:list` sem rotas web (R7) |
| `resources/css/*`, `resources/js/app.js`, `vite.config.js`, `package.json`, `package-lock.json`, `public/build/` | nenhuma view usa `@vite`/`@extends`; app device-only sem pipeline JS (R9) |
| `routes/web.php` + `web:` do `bootstrap/app.php` | app device-only; `route:list` limpo (R8) |
| `livewire/livewire`, `livewire/blaze` | `composer show livewire/livewire` → não instalado; `composer.json`/`lock` limpos (R10) |
| 4 testes legados (`DuelArenaTest`, `DuelTurnTest`, `DuelSetupTest`, `HomeDashboardTest`) | cobertura migrada 1:1 antes da remoção (R7) |
| Scratch da raiz (`test_*.txt`, `all_tests.txt`, `modify.py`) | sem referência; `TEST_STATUS.md`, `IMPLEMENTATION_SUMMARY.md`, `CHANGES_BY_SECTOR.md` movidos para `docs/` (D16) |
| `DuelToolsController` + 8 rotas JSON + `DuelToolsTest` | substituídos por domínio + telas nativas (D21, R1) |

Detalhamento completo: `docs/nativephp-refactor/cleanup-audit.md` (R1–R10).

## 7. Testes e builds executados

| Verificação | Resultado |
|---|---|
| `composer test` (config:clear + pint + phpstan + Pest) | ✅ **verde** |
| `php artisan test --compact` | ✅ **165 testes / 1057 assertions**, 0 falhas, 0 warnings |
| `vendor/bin/pint --dirty` | ✅ sem pendências |
| `vendor/bin/phpstan analyse` | ✅ **0 erros** (25 pré-existentes corrigidos — D23) |
| `php artisan native:validate` | ✅ todos os componentes válidos |
| `composer audit` | ✅ nenhuma advisory |
| Build Android (Build 2, pré-limpeza) | ✅ bundle `runtime_mode=persistent` → APK → instalado → app iniciado (`nativephp/android-build.log`, 16/11 16:36) |
| Build Android (Build 3, pós-limpeza) | ⏳ **bloqueado**: sem aparelho conectado, `native:run` aborta após a checagem do ADB |

Cobertura 1:1 migrada antes da remoção do legado: `DuelSetupDomainTest` (14) + `DuelSetupScreenTest` (18), `DuelArenaScreenTest` (40), `DuelTurnDomainTest` (18), `HomeTest` (5). Novos cenários fechando lacunas: assentos vazios iniciais, nomes automáticos, identidade isolada por jogador sem tocar no histórico, cor legada com fallback de tema, haptics ligado/recusado pelo device, valor acima do limite.

## 8. Validação Android

- **Concluído:** build Gradle, instalação via ADB e *launch* do app no `moto_g56_5G`, com `persistent` (D15).
- **Pendente:** checklist funcional no aparelho (Início → Nova partida → Arena PV/fases/turno/undo-redo/cronômetro/vibração/identidade/reordenar → Dados/Moeda/Lembretes → Aparência tema/wallpaper/scrim → voltar do sistema/teclado/safe area) e o **Build 3** com o app pós-limpeza. Para isso, basta reconectar o aparelho e rodar `php artisan native:run android`.

## 9. Compatibilidade/pendências iOS

- Análise estática: todos os componentes EDGE e plugins usados têm implementação iOS no pacote instalado (`mobile-ui` 0.9.0 e `mobile-camera` 1.1.0). Ícones têm nome SF Symbol e Material por plataforma.
- `mobile-camera` exige iOS 18.2+; registrar no build iOS.
- **Validação iOS não executada** (sem macOS/Xcode). Nenhuma afirmação de teste em dispositivo iOS.

## 10. Riscos e pendências restantes

| Item | Risco | Ação |
|---|---|---|
| Build 3 / validação funcional | app final pós-limpeza ainda não rodou no aparelho | reconectar o device e rodar `php artisan native:run android`; validar o checklist |
| `persistent` | modo novo no app; falha se houver estado residual entre requisições | rollback de uma linha: `NATIVEPHP_RUNTIME_MODE=classic` |
| iOS | não validado em runtime | build no Xcode quando houver macOS/dispositivo |
| `composer.json` `name`/`description` | ainda dizem "blank-livewire-starter-kit" | metadado inofensivo; renomear se desejado |
| Skills de agente Livewire/Blaze/Tailwind | permanecem em `.agents/skills` e `.github/skills` | não são código do app; remover só com aprovação |
| Contadores & Campo | módulo nunca implementado (mock quebrado) | fora de escopo (D4), registrado como pendência de produto |

**Commit/push:** não executados, conforme instrução do usuário.
