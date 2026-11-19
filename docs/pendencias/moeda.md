# Moeda

**Rota:** `/duel/{duel}/coin` · **Componente:** `App\NativeComponents\CoinTools` · **View:** `resources/views/native/coin-tools.blade.php`
**Layout:** `App\Layouts\StackLayout`
**Design Stitch:** **nenhum**
**Tarefas antigas absorvidas:** 013, 019
**Cobertura atual:** `tests/Feature/DuelToolsDomainTest.php`, `tests/Feature/DuelToolsScreenTest.php`
**Status geral:** Implementado — não validado no aparelho

## 1. Contexto e requisito

Sorteio de cara ou coroa dentro da partida (013): lançar, ver o último resultado em destaque e o placar acumulado do duelo, com histórico. D5 decidiu tela nativa empilhada em vez de modal.

## 2. Estado atual

- `CoinTools::flip()` só delega a `Duel::flipCoin()`; a aleatoriedade mora no domínio (`app/NativeComponents/CoinTools.php:18-21`; `app/Models/Duel.php:351-356`).
- Placar do duelo via `Duel::coinFlipCounts()` — duas queries `count()` por render (`Duel.php:363-369`).
- Histórico: 20 últimos lançamentos; "último" consultado separadamente (`CoinTools.php:25-30`).
- View: botão "Lançar" fixo no topo, destaque do último resultado, linha Cara/Coroa/Total e `<native:list class="w-full flex-1">` fora de `scroll-view` (`resources/views/native/coin-tools.blade.php:1-36`).
- Resultado é `heads`/`tails` no banco e traduzido para "Cara"/"Coroa" na view (`:7`, `:30`).
- Sem `#[Poll]`: a tela não se atualiza sozinha (não precisa, só muda por ação local).

## 3. Pendências

| ID | Pendência | Origem | Critério de aceite | Status |
|---|---|---|---|---|
| MO-1 | Limpar histórico de lançamentos | 019, 021 | Ação que apaga os lançamentos do duelo, com confirmação, sem zerar o placar de outro duelo | Pendente |
| MO-2 | Histórico limitado a 20 sem "ver mais" | observado 2026-11-17 | Decidir entre limite fixo, paginação ou lista completa com rolagem | Pendente |
| MO-3 | Sem retenção: lançamentos crescem indefinidamente | 021 | Política de expurgo/arquivamento definida junto com AR-3, aplicada também a `coin_flips` | Pendente |
| MO-4 | Placar custa duas queries `count()` a cada render | observado 2026-11-17 | Placar em uma consulta agregada (ou cache no componente) sem mudar o resultado | Pendente |
| MO-5 | Histórico não diferencia "lançamento do jogador X" | 019 | Registrar autor do lançamento quando o histórico unificado (AR-2) existir; hoje não há coluna de jogador | Pendente |
| MO-6 | Sem teste de persistência entre sessões | observado 2026-11-17 | Teste garantindo que lançamentos e placar sobrevivem a reabrir o duelo | Pendente |
| MO-7 | Sem design de referência | observado 2026-11-17 | Decidir com o usuário se a tela ganha design antes de receber conteúdo novo | Pendente |

## 4. Validação no aparelho

1. Abrir a tela pela arena → título "Lançar moeda" e voltar funcionando.
2. Lançar uma vez → resultado em destaque, horário correto.
3. Lançar várias vezes → placar Cara/Coroa/Total bate com o número de toques.
4. Contagem de cada lado confere com a soma do histórico exibido.
5. Fechar e reabrir o app → histórico e placar preservados.
6. Muitos lançamentos → lista rola sem derrubar o app (regressão de 2026-11-17).
7. Tema Minimal Arena → texto do resultado legível sobre a superfície clara.

## 5. Regras aplicáveis

- `.ai/rules/scope.md` — sempre.
- `.ai/rules/app.md` — sorteio e contagem ficam em `Duel`, nunca na view.
- `.ai/rules/tests.md` — branch novo exige teste antes.
- `.ai/rules/nativephp-docs.md` — **regra de lista**: `native:list` nunca dentro de `native:scroll-view`.

Decisões: D5, D17, D21, D27, D28. Ver `docs/nativephp-refactor/decisions.md`.

## 6. Skills

- `laravel-best-practices` — domínio e consultas.
- `testing-best-practices` — cenários de sorteio/persistência.
- `nativephp-mobile-ui` (vendor) — `list`, `list-item`, `button`.
- `tailwindcss-development` — classes utilitárias da view.

## 7. Arquivos a inspecionar antes de editar

- `app/NativeComponents/CoinTools.php`
- `resources/views/native/coin-tools.blade.php`
- `app/Models/Duel.php` (`flipCoin`, `coinFlipCounts`, relação `coinFlips`), `app/Models/CoinFlip.php`
- `tests/Feature/DuelToolsDomainTest.php`, `tests/Feature/DuelToolsScreenTest.php`
- `docs/tarefas/013-…`, `docs/tarefas/017-…`

## 8. Registro de execução

| Data | Agente | O que mudou | Comandos | Resultado |
|---|---|---|---|---|
| 2026-11-17 | auditoria | Arquivo criado a partir do código atual e da tarefa 013 | leitura de código | 7 pendências abertas |
| 2026-11-17 | correção | `native:list` retirado de dentro do `scroll-view` (crash de altura infinita no Compose) | `php artisan test --compact tests/Feature/NativeListLayoutTest.php` | verde; validação no device pendente |
| 2026-11-19 | implementação | Animação nativa 2D da moeda (círculo girando com `rotate` e `native:poll` de 90 ms por ~600 ms, face com `content-transition`); resultado continua vindo de `Duel::flipCoin()` | `php artisan test --compact`, `php artisan native:validate`, `vendor/bin/phpstan analyse` | verde; validação no device pendente |
