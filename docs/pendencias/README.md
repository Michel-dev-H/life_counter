# Pendências por tela

Este diretório é a **fonte de continuidade** do trabalho por tela: um arquivo por tela do app, com o que já existe, o que falta implementar e o que falta validar no aparelho.

Os arquivos de `docs/tarefas/001–034` ficam **intocados** e continuam válidos como histórico. As decisões de arquitetura vivem em `docs/nativephp-refactor/decisions.md` (D1–D27) e prevalecem sobre qualquer anotação antiga.

## Como usar

1. Abra o arquivo da tela **antes** de qualquer mudança nela.
2. Leia o §2 (estado atual) e o §3 (pendências). Não trate status como prova: só é validado o que tem registro no §8.
3. Build compilado não valida comportamento. "Validado" significa reproduzido no `moto_g56_5G`, com data e evidência.
4. Ao terminar, atualize o próprio arquivo: status, arquivos tocados, comandos, resultados (inclusive falhas) e skills realmente usadas.
5. Mudança que afeta mais de uma tela entra no arquivo da tela de origem **e** em [`chrome-e-modais.md`](./chrome-e-modais.md), com link cruzado.

## Status

| Status | Significado |
|---|---|
| `Pendente` | sem implementação, ou implementação que ainda não cobre o critério |
| `Implementado — não validado` | código existe, falta exercitar no aparelho |
| `Validado no aparelho` | reproduzido no device, com data e evidência no §8 |
| `Fora de escopo` | decisão registrada (D#); não implementar sem nova decisão |

## Telas

| Tela | Arquivo | Rota | Componente | Design Stitch | Tarefas antigas |
|---|---|---|---|---|---|
| Início | [inicio.md](./inicio.md) | `/` | `App\NativeComponents\Home` | — | 020, 029, 030, 031 |
| Configuração de Duelo | [configuracao-duelo.md](./configuracao-duelo.md) | `/duel/new` | `DuelSetup` | `multi_duelists.html` | 005, 006, 010 |
| Arena | [arena.md](./arena.md) | `/duel/{duel}` | `DuelArena` | `arena.html`, `multi_duelists.html` | 007–011, 014, 018–021, 028, 029 |
| Duelos Recentes | [duelos-recentes.md](./duelos-recentes.md) | `/duels` | `RecentDuels` | — | 020, 021, 030 |
| Dados | [dados.md](./dados.md) | `/duel/{duel}/dice` | `DiceTools` | — | 012, 019 |
| Moeda | [moeda.md](./moeda.md) | `/duel/{duel}/coin` | `CoinTools` | — | 013, 019 |
| Lembretes | [lembretes.md](./lembretes.md) | `/duel/{duel}/reminders` | `Reminders` | — | 017, 019 |
| Aparência | [aparencia.md](./aparencia.md) | `/appearance/settings` | `AppearanceSettings` | `personalize.html` | 022–027, 029, 031 |
| Chrome e modais | [chrome-e-modais.md](./chrome-e-modais.md) | — | `TabsLayout`, `StackLayout`, `WallpaperLayout` | — | 008, 029, 030, 031 |

`docs/design/counters.html` (Contadores & Campo) **não** tem tela: fora de escopo por D4.

## Pendências globais (fora de tela)

| ID | Pendência | Estado |
|---|---|---|
| G-1 | Build de validação: `php artisan native:run android` precisa rodar em terminal próprio (o harness morre na cópia do bundle). Só depois disso as telas saem de "não validado". | Pendente |
| G-2 | Empacotamento: `native:run` debug embute **33,5 MB de 44,6 MB** em dependências de dev (`phpstan`, `faker`, `phpunit`, `pest`). Build `--build=profileable`/release usa `--no-dev`. Ver auditoria de 2026-11-17. | Pendente |
| G-3 | Empacotamento: excluir do bundle `docs/`, `.agents/`, `.ai/`, factories/seeders, `vendor/nativephp/mobile-ui/resources`, `resources/fonts` (duplicado em `assets/fonts`) e dev configs; apagar `storage/app/private/livewire-tmp/` e `public/wallpapers/teste.jpg`. | Pendente |
| G-4 | `APP_ENV=local` e `APP_DEBUG=true` no `.env`: build de distribuição precisa `APP_ENV=production APP_DEBUG=false` (o vendor recusa publicar na Play Store fora de production; `edge-nav.log` só escreve em debug). | Pendente |
| G-5 | iOS nunca validado em runtime (sem macOS/Xcode). `mobile-camera` exige iOS 18.2+. | Pendente |
| G-6 | `composer.json` ainda se chama `blank-livewire-starter-kit`; skills de Livewire/Blaze/Tailwind seguem em `.agents/skills` e `.github/skills`. Metadado e sobra de agente, não código do app. | Pendente |

## Regra de registro

Cada arquivo tem um §8 com o formato:

```
| Data | Agente | O que mudou | Comandos | Resultado |
```

Sem linha no §8, a pendência continua `Pendente` — independentemente do código existir.
