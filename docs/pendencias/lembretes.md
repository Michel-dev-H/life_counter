# Lembretes

**Rota:** `/duel/{duel}/reminders` · **Componente:** `App\NativeComponents\Reminders` · **View:** `resources/views/native/reminders.blade.php`
**Layout:** `App\Layouts\StackLayout`
**Design Stitch:** **nenhum**
**Tarefas antigas absorvidas:** 017, 019
**Cobertura atual:** `tests/Feature/DuelToolsDomainTest.php`, `tests/Feature/DuelToolsScreenTest.php`
**Status geral:** Implementado — não validado no aparelho

## 1. Contexto e requisito

Lembretes de efeito da partida (017): criar, marcar como concluído e excluir, com gatilho por turno e/ou fase. O lembrete com gatilho casando com o estado atual do duelo é destacado no topo da tela (`Duel::highlightedReminderIds()`).

## 2. Estado atual

- Regras e mensagens no componente (`app/NativeComponents/Reminders.php:37-61`): nome obrigatório até 120, turno inteiro, fase válida.
- Formulário em `native:modal` com Nome, Descrição, "Gatilho por turno" (teclado numérico) e chips de fase (`resources/views/native/reminders.blade.php:24-80`).
- Turno e fase são **opcionais e independentes**: preencher os dois exige que ambos casem (`Duel.php:377-389`).
- Lista ordenada por `completed` e `created_at`, com checkbox (`on-leading-change`) e ícone de lixeira (`on-trailing-press`) (`Reminders.php:120-129`; view `:7-21`).
- Destaque usa `trailingBadges` com ícone `flag` (`view:15`).
- `Reminder` já tem as colunas `duel_player_id`, `duration_turns` e `removal_condition` no `$fillable` (`app/Models/Reminder.php:15-25`), mas **nenhuma delas é usada** pelo formulário.
- Exclusão é imediata: `delete()` sem confirmação (`Reminders.php:115-118`; view `:16-18`).
- Não há edição: só criar, alternar concluído e excluir.

## 3. Pendências

| ID | Pendência | Origem | Critério de aceite | Status |
|---|---|---|---|---|
| LE-1 | Editar lembrete existente | 017 | Tocar no item abre o mesmo formulário preenchido; salvar atualiza em vez de criar | Pendente |
| LE-2 | Duração/expiração do efeito | 017 | Campo `duration_turns` exposto e regra definida (expira após N turnos, some ou fica concluído); hoje a coluna existe e não é usada | Pendente |
| LE-3 | Confirmação antes de excluir | 017, 029 | Excluir pede confirmação (modal) e cancelar não apaga | Pendente |
| LE-4 | Gatilho aceita só um turno | observado 2026-11-17 | Confirmar se "turno 3" basta ou se o efeito precisa recorrer (ex.: todo turno par); hoje é igualdade simples | Pendente |
| LE-5 | Lembrete não é atribuído a jogador | 017 | `duel_player_id` exposto e exibido no item, ou decisão registrada de que lembrete é sempre da mesa | Pendente |
| LE-6 | `removal_condition` sem uso | 017 | Expor a condição de remoção no formulário, ou remover a coluna se não for requisito (YAGNI) | Pendente |
| LE-7 | Sem `#[Poll]`: destaque pode ficar defasado | observado 2026-11-17 | Voltar para a arena, avançar turno e retornar → destaque recalculado; confirmar que não é preciso poll | Pendente |
| LE-8 | Sem teste de destaque por turno/fase | observado 2026-11-17 | Cenários: só turno, só fase, ambos casando, ambos divergindo, lembrete concluído | Pendente |
| LE-9 | Sem design de referência | observado 2026-11-17 | Decidir com o usuário o desenho da lista/formulário | Pendente |

## 4. Validação no aparelho

1. Abrir pela arena → título "Lembretes" e voltar funcionando.
2. Criar lembrete só com nome → aparece na lista.
3. Criar sem nome → mensagem "Informe o nome do lembrete.".
4. Gatilho por turno: digitar texto → mensagem "Informe um número de turno.".
5. Criar com turno + fase iguais ao estado atual → volta à arena e reabre a tela: item destacado com bandeira.
6. Marcar como concluído → sai do destaque e vai para o fim da lista.
7. Excluir → comportamento definido em LE-3.
8. Teclado aberto no campo de turno → botões Cancelar/Salvar continuam alcançáveis.
9. Fechar e reabrir o app → lembretes preservados.

## 5. Regras aplicáveis

- `.ai/rules/scope.md` — sempre.
- `.ai/rules/app.md` — regra de gatilho/destaque fica em `Duel`, não na view; coluna nova só com requisito.
- `.ai/rules/tests.md` — branch novo (edição, duração, confirmação) exige teste antes.
- `.ai/rules/nativephp-docs.md` — **regra de lista** e padrão de `native:modal`.

Decisões: D5, D17, D21, D27, D28. Ver `docs/nativephp-refactor/decisions.md`.

## 6. Skills

- `laravel-best-practices` — domínio, validação e componente.
- `testing-best-practices` — factories de lembrete e cenários de gatilho.
- `nativephp-mobile-ui` (vendor) — `list-item`, `modal`, `chip`, `outlined-text-input`.
- `tailwindcss-development` — classes utilitárias da view.

## 7. Arquivos a inspecionar antes de editar

- `app/NativeComponents/Reminders.php`
- `resources/views/native/reminders.blade.php`
- `app/Models/Reminder.php`, `app/Models/Duel.php` (`highlightedReminderIds`, relação `reminders`)
- `app/Enums/DuelPhase.php`
- `database/migrations/*reminders*` (colunas já criadas)
- `tests/Feature/DuelToolsDomainTest.php`, `tests/Feature/DuelToolsScreenTest.php`
- `docs/tarefas/017-…`

## 8. Registro de execução

| Data | Agente | O que mudou | Comandos | Resultado |
|---|---|---|---|---|
| 2026-11-17 | auditoria | Arquivo criado a partir do código atual e da tarefa 017 | leitura de código | 9 pendências abertas |
| 2026-11-17 | correção | `native:list` retirado de dentro do `scroll-view` (crash de altura infinita no Compose) | `php artisan test --compact tests/Feature/NativeListLayoutTest.php` | verde; validação no device pendente |
