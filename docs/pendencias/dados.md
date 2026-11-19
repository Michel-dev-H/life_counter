# Dados

**Rota:** `/duel/{duel}/dice` · **Componente:** `App\NativeComponents\DiceTools` · **View:** `resources/views/native/dice-tools.blade.php`
**Layout:** `App\Layouts\StackLayout`
**Design Stitch:** **nenhum**
**Tarefas antigas absorvidas:** 012, 019
**Cobertura atual:** `tests/Feature/DuelToolsDomainTest.php`, `tests/Feature/DuelToolsScreenTest.php`
**Status geral:** Implementado — não validado no aparelho

## 1. Contexto e requisito

Rolagem de dados dentro do duelo (012): escolher quantidade, lançar, ver o total/resultados e o histórico da partida. D5 decidiu tela nativa empilhada em vez de modal/rota JSON.

## 2. Estado atual

- `DiceTools::roll()` limita a quantidade entre 1 e 10 e delega a `Duel::rollDice()` (`app/NativeComponents/DiceTools.php:20-27`).
- Histórico: 10 últimos lançamentos; "último" consultado separadamente (`:32-33`).
- View: quantidade + botão no topo, último resultado em destaque, histórico em `<native:list class="w-full flex-1">` (`resources/views/native/dice-tools.blade.php`).
- Linha do histórico mostra resultados e horário, com o total como `trailingText`.
- Persistência em `dice_rolls` por duelo.

## 3. Pendências

| ID | Pendência | Origem | Critério de aceite | Status |
|---|---|---|---|---|
| DA-1 | Limpar histórico de lançamentos | 019, 021 | Ação que apaga os lançamentos do duelo, com confirmação | Pendente |
| DA-2 | Histórico limitado a 10 sem "ver mais" | observado 2026-11-17 | Decidir entre limite fixo, paginação ou lista completa com rolagem | Pendente |
| DA-3 | Quantidade inválida ajustada em silêncio | observado 2026-11-17 | Digitar 0, 11 ou texto hoje é corrigido sem mensagem (`:22`); decidir se valida com mensagem como no legado | Pendente |
| DA-4 | Dados não alimentam o histórico unificado | 019 | Lançamentos aparecem no histórico da partida quando AR-2 existir | Pendente |
| DA-5 | Sem teste para quantidade fora da faixa | observado 2026-11-17 | Cenário cobrindo 0, 11 e entrada não numérica | Pendente |

## 4. Validação no aparelho

1. Abrir a tela pela arena → título "Lançar dados" e voltar funcionando.
2. Lançar 1 dado → resultado e total corretos.
3. Lançar 6 dados → lista de resultados e total somado.
4. Digitar 0, 11 e texto no campo → comportamento definido em DA-3.
5. Repetir lançamentos → histórico atualiza e mantém os 10 recentes.
6. Fechar e reabrir o app → histórico preservado.
7. Muitos lançamentos → lista rola sem derrubar o app.

## 5. Regras aplicáveis

- `.ai/rules/scope.md` — sempre.
- `.ai/rules/app.md` — regra de rolagem/limite no domínio (`Duel`/`DiceRoll`), não na view.
- `.ai/rules/tests.md` — branch novo exige teste antes.
- `.ai/rules/nativephp-docs.md` — **regra de lista**: `native:list` nunca dentro de `native:scroll-view`.

Decisões: D5, D17, D21, D27, D28. Ver `docs/nativephp-refactor/decisions.md`.

## 6. Skills

- `laravel-best-practices` — domínio e persistência.
- `testing-best-practices` — cenários de rolagem.
- `nativephp-mobile-ui` (vendor) — `list`, `list-item`, `outlined-text-input`.
- `tailwindcss-development` — classes utilitárias da view.

## 7. Arquivos a inspecionar antes de editar

- `app/NativeComponents/DiceTools.php`
- `resources/views/native/dice-tools.blade.php`
- `app/Models/Duel.php` (`rollDice`, relação `diceRolls`), `app/Models/DiceRoll.php`
- `tests/Feature/DuelToolsDomainTest.php`, `tests/Feature/DuelToolsScreenTest.php`
- `docs/tarefas/012-…`

## 8. Registro de execução

| Data | Agente | O que mudou | Comandos | Resultado |
|---|---|---|---|---|
| 2026-11-17 | auditoria | Arquivo criado a partir do código atual e da tarefa 012 | leitura de código | 5 pendências abertas |
| 2026-11-17 | correção | `native:list` retirado de dentro do `scroll-view` (crash de altura infinita) | `php artisan test --compact tests/Feature/NativeListLayoutTest.php` | verde; validação no device pendente |
| 2026-11-19 | implementação | Animação nativa 2D dos dados (pips com `rect`/`circle`, `rotate`/`scale` e `native:poll` de 90 ms por ~600 ms); resultado continua vindo de `Duel::rollDice()` | `php artisan test --compact`, `php artisan native:validate`, `vendor/bin/phpstan analyse` | verde; validação no device pendente |
