# Configuração de Duelo

**Rota:** `/duel/new` · **Componente:** `App\NativeComponents\DuelSetup` · **View:** `resources/views/native/duel-setup.blade.php`
**Layout:** `App\Layouts\TabsLayout`
**Design Stitch:** `docs/design/multi_duelists.html` (referência de arena multi, não de configuração)
**Tarefas antigas absorvidas:** 005, 006, 010
**Cobertura atual:** `tests/Feature/DuelSetupDomainTest.php`, `tests/Feature/DuelSetupScreenTest.php`
**Status geral:** Implementado — não validado no aparelho

## 1. Contexto e requisito

Define a partida antes de começar: capacidade da arena (1 a 8), pontos de vida iniciais (presets + valor livre) e identidade de cada duelista (nome, cor, ícone) — 005/006. Ao iniciar, cria o duelo pelo domínio (`Duel::startWith()`) e navega para a arena.

## 2. Estado atual

- Regras de validação e mensagens no componente (`app/NativeComponents/DuelSetup.php:63-93`); nenhuma regra duplicada na view.
- Capacidade: `1P`, `Duelo`, `Trio`, `Tag/2v2`, `Battle`, `Cluster`, `Lobby`, `Torneio` (`:28-37`).
- Presets de PV: `Padrão TCG` 8000, `Speed Duel` 4000, `Tag Team` 16000 (`:40-44`), mais campo livre (`duel-setup.blade.php:35-41`).
- Identidade por assento com cor obrigatória e ícone opcional (`:68-70`); cores/ícones em `Duel::PLAYER_ACCENTS` / `Duel::PLAYER_ICONS`.
- Limites: `Duel::MIN_PLAYERS = 1`, `Duel::MAX_PLAYERS = 8` (`app/Models/Duel.php:28-30`).
- View inteira dentro de `scroll-view` (`duel-setup.blade.php:1-2`), sem `native:list` — a guarda `NativeListLayoutTest` não se aplica aqui.
- Erros de validação aparecem abaixo de cada bloco (`:43-45`, `:97-113`).

## 3. Pendências

| ID | Pendência | Origem | Critério de aceite | Status |
|---|---|---|---|---|
| CF-1 | Rótulos de capacidade e presets sem fonte oficial | 005, 001 | Cada rótulo ("Trio", "Battle", "Cluster", "Lobby", "Torneio", "Padrão TCG", "Speed Duel", "Tag Team") com referência de regra ou substituído por rótulo neutro | Pendente |
| CF-2 | Ícone do duelista é opcional | 006 | Confirmar se identidade exige ícone; se sim, aplicar regra e mensagem, se não, registrar a decisão | Pendente |
| CF-3 | Nomes repetidos aceitos | observado 2026-11-17 | Dois assentos podem ter o mesmo nome sem qualquer aviso — decidir se bloqueia ou alerta | Pendente |
| CF-4 | `MIN_PLAYERS = 1` permite duelo de um jogador | observado 2026-11-17 | Confirmar se 1P é modo válido; a arena assume painel único e o layout multi não se aplica | Pendente |
| CF-5 | Rascunho perdido ao sair | observado 2026-11-17 | Sair e voltar perde nomes/cores digitados. Decidir se persiste (Setting) ou se permanece assim | Pendente |
| CF-6 | Sem design Stitch próprio | observado 2026-11-17 | Decidir com o usuário se a configuração ganha design; `multi_duelists.html` não cobre formulário | Pendente |

## 4. Validação no aparelho

1. Abrir a aba "Partida" → tela de configuração.
2. Alternar capacidade de 1 a 8 → número de blocos de duelista acompanha.
3. Remover jogador → botão desabilitado em 1; adicionar desabilita em 8.
4. Preset de PV → campo reflete o valor; digitar valor livre → valida 1..999.999.
5. Iniciar sem nome → mensagem "Selecione ao menos um jogador.".
6. Escolher cor/ícone em cada assento → seleção visível.
7. Iniciar → cria duelo e abre a arena com os assentos corretos.
8. Teclado numérico abre no campo de PV e não cobre o botão de iniciar.

## 5. Regras aplicáveis

- `.ai/rules/scope.md` — sempre.
- `.ai/rules/app.md` — validação e regras de negócio ficam no domínio, não na view.
- `.ai/rules/tests.md` — comportamento novo exige teste antes.
- `.ai/rules/nativephp-docs.md` — arquitetura EDGE vigente.

Decisões: D1, D8, D17, D26, D27. Ver `docs/nativephp-refactor/decisions.md`.

## 6. Skills

- `laravel-best-practices` — validação/domínio.
- `testing-best-practices` — factories e cenários.
- `nativephp-mobile-ui` (vendor) — `outlined-text-input`, `chip`, `pressable`, `button`.
- `tailwindcss-development` — classes utilitárias da view.

## 7. Arquivos a inspecionar antes de editar

- `app/NativeComponents/DuelSetup.php`
- `resources/views/native/duel-setup.blade.php`
- `app/Models/Duel.php` (`PLAYER_ACCENTS`, `PLAYER_ICONS`, `PLAYER_ICON_NAMES`, `startWith`)
- `app/Models/DuelPlayer.php` (`MAX_LIFE_POINTS`)
- `tests/Feature/DuelSetupDomainTest.php`, `tests/Feature/DuelSetupScreenTest.php`
- `docs/tarefas/005-…`, `docs/tarefas/006-…`

## 8. Registro de execução

| Data | Agente | O que mudou | Comandos | Resultado |
|---|---|---|---|---|
| 2026-11-17 | auditoria | Arquivo criado a partir do código atual e das tarefas 005/006 | leitura de código | 6 pendências abertas |
