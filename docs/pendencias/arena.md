# Arena

**Rota:** `/duel/{duel}` · **Componente:** `App\NativeComponents\DuelArena` · **View:** `resources/views/native/duel-arena.blade.php`
**Layout:** `App\Layouts\StackLayout` (top bar com voltar)
**Design Stitch:** `docs/design/arena.html` (2 jogadores) e `docs/design/multi_duelists.html` (multi)
**Tarefas antigas absorvidas:** 007, 008, 009, 010, 011, 014, 018, 019, 020, 021, 028, 029
**Cobertura atual:** `tests/Feature/DuelArenaScreenTest.php`, `tests/Feature/DuelTurnDomainTest.php`, `tests/Feature/LifePointsTest.php`, `tests/Feature/DuelToolsDomainTest.php`
**Status geral:** Implementado — parcialmente validado no aparelho (navegação e render)

## 1. Contexto e requisito

Tela central: pontos de vida por jogador com controles rápidos, calculadora, undo/redo, fases/turno/rodada, cronômetro, sorteio de primeiro jogador, reordenação e edição de identidade, além do acesso às ferramentas (dados, moeda, lembretes).

## 2. Estado atual

- PV e regras no domínio: `DuelPlayer::addLifePoints/subtractLifePoints/halveLifePoints/doubleLifePoints/setLifePointsTo/restoreStartingLifePoints`; a tela só orquestra (`DuelArena::changeLifePoints`, `:496-517`).
- Controles rápidos `−1000 / −500 / +500 / +1000`, `÷2`, `×2`, `Calc`, `Rest.` (`duel-arena.blade.php:79-120`).
- Calculadora em modal com operações Somar/Subtrair/Definir (`DuelArena.php:181-218`; view `:198-235`).
- Undo/redo por evento, com botões desabilitados quando não há evento (`DuelArena.php:220-242`; view `:34-47`).
- Fases por botão, turno/rodada, cronômetro Iniciar/Pausar/Zerar (`:285-322`; view `:13-48`), com `#[Poll(1000)]` (`:324-328`).
- Sorteio de primeiro jogador inline, sem persistir (`:301-310`; view `:60-65`).
- Reordenar jogadores grava a ordem em `Setting` (`duel.layout.order.{id}`, `:244-277`, `:488-491`).
- Identidade editável em modal (`:353-394`; view `:238-293`).
- Vibração opt-in gravada em `Setting` (`duel.feedback.haptics`), desligada com mensagem se o device recusar (`:330-351`).
- "Última alteração" com autor e valor (`:405-409`; view `:50-57`).
- Ferramentas navegam para telas empilhadas (`view:150-172`).
- Root em `scroll-view` (`view:1-2`), sem `native:list` — a guarda `NativeListLayoutTest` não se aplica.

## 3. Pendências

| ID | Pendência | Origem | Critério de aceite | Status |
|---|---|---|---|---|
| AR-1 | Feedback sonoro de PV | 009 | Som (ou confirmação explícita de que só vibração é aceitável) ao alterar PV; hoje só há `Device::vibrate()` | Pendente |
| AR-2 | Histórico unificado da partida | 019 | Lista/tela com todas as alterações (PV, turno, fase, dados, moeda, lembretes), não só a última | Pendente |
| AR-3 | Arquivamento e retenção de duelos | 021 | Política para duelos antigos (arquivar/excluir por idade), sem perder os recentes | Pendente |
| AR-4 | Confirmação ao reabrir duelo | 020 | Reabrir um duelo não finalizado pede confirmação e restaura turno/fase/PV corretamente após cold start | Pendente |
| AR-5 | Controles avançados de PV | 008 | Definir se existem presets configuráveis/atalhos; no nativo não há teclado físico, então a decisão precisa ser explícita | Pendente |
| AR-6 | Cronômetro além de play/pause/zerar | 018 | Duração por turno, aviso de tempo esgotado e comportamento ao avançar turno | Pendente |
| AR-7 | Wake lock durante a partida | 028 | Tela não apaga durante um duelo ativo (ou decisão registrada de não usar) | Pendente |
| AR-8 | Layout multi vs. `multi_duelists.html` | 010, 011 | Painéis hoje empilhados verticalmente; comparar com o design (que mostra 2P lado a lado) e decidir a divergência | Pendente |
| AR-9 | Custo do `#[Poll(1000)]` em partida longa | D12 | Medir bateria/CPU em 30 min de partida; hoje o poll roda sempre, mesmo com cronômetro parado | Pendente |
| AR-10 | Sorteio não entra no histórico | 014 | Registrar o resultado do sorteio como evento quando AR-2 existir | Pendente |

**Fora de escopo (decisão registrada):** rotação 180° do painel do oponente (D3) e "expandir painel do jogador" (D20). Contadores de campo não existem (D4). Nenhum dos três deve ser reintroduzido sem nova decisão.

## 4. Validação no aparelho

1. Configurar 2 jogadores → arena com um painel por jogador e PV inicial correto.
2. Aplicar cada controle: `−1000`, `−500`, `+500`, `+1000`, `÷2`, `×2`, `Rest.` → valor e "última alteração" corretos.
3. Calculadora: somar, subtrair, definir; valor 0, valor > 999.999 e campo vazio → mensagens.
4. Undo/redo em sequência → valores voltam e botões desabilitam nos extremos.
5. Fases: tocar cada fase → seleção muda; avançar turno → rodada/turno corretos.
6. Cronômetro: iniciar, pausar, zerar; deixar 5 min rodando → número acompanha sem travar.
7. Sorteio → nome aparece; repetir → pode mudar.
8. Reordenar jogadores (3+ participantes) → ordem persiste após sair e voltar.
9. Editar identidade (nome/cor/ícone) → painel atualiza e histórico antigo não muda.
10. Vibração: ativar → vibra; desativar; device que recusa → mensagem e preferência volta a 0.
11. Dados/Moeda/Lembretes → abre a tela empilhada do duelo correto; voltar retorna à arena.
12. Fechar e reabrir o app → duelo e estado preservados (AR-4).

## 5. Regras aplicáveis

- `.ai/rules/scope.md` — sempre.
- `.ai/rules/app.md` — cálculo e faixa de PV ficam **só** no domínio; view/componente não reimplementam.
- `.ai/rules/tests.md` — cada branch novo (operação, fase, timer) com teste.
- `.ai/rules/nativephp-docs.md` — arquitetura EDGE vigente.

Decisões: D3, D12, D17, D20, D27. Ver `docs/nativephp-refactor/decisions.md`.

## 6. Skills

- `laravel-best-practices` — domínio e componente.
- `testing-best-practices` — cenários de PV/turno e factories.
- `nativephp-mobile-ui` (vendor) — `modal`, `chip`, `button`, `slider`; conferir `#[Poll]` e `#[On]`.
- `tailwindcss-development` — classes utilitárias da view.

## 7. Arquivos a inspecionar antes de editar

- `app/NativeComponents/DuelArena.php`
- `resources/views/native/duel-arena.blade.php`
- `app/Models/Duel.php` (fases, turno, timer, eventos), `app/Models/DuelPlayer.php`
- `app/Enums/DuelPhase.php`, `app/Models/Setting.php`
- `tests/Feature/DuelArenaScreenTest.php`, `DuelTurnDomainTest.php`, `LifePointsTest.php`
- `docs/design/arena.html`, `docs/design/multi_duelists.html`
- `docs/tarefas/018-…`, `docs/tarefas/019-…`

## 8. Registro de execução

| Data | Agente | O que mudou | Comandos | Resultado |
|---|---|---|---|---|
| 2026-11-17 | auditoria | Arquivo criado a partir do código atual e das tarefas 007–021/028/029 | leitura de código e log do device | 10 pendências abertas |
| 2026-11-17 | device | `moto_g56_5G`: navegação Home → RecentDuels → DuelSetup → DuelArena → AppearanceSettings → Home com render de 3–12 ms | `edge-nav.log` | navegação ok; checklist funcional pendente |
