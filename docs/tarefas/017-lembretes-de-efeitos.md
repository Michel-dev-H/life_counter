# 017 — Lembretes de efeitos e duração

- **Status:** Parcial e validado em dispositivo — criar/concluir/remover lembretes com gatilho por turno/fase e destaque; edição, duração automática e confirmação de remoção pendentes
- **Área:** Lembretes
- **Prioridade:** Alta

## Objetivo

Permitir que os jogadores registrem efeitos manualmente e recebam lembretes sem automatizar regras de cartas.

## Escopo

- Criar e editar lembrete com nome da carta, descrição curta, jogador responsável, turno/fase, duração e condição para remoção.
- Associar lembretes à sessão e opcionalmente a uma referência de carta ou posição.
- Destacar lembretes quando a fase/turno configurados forem alcançados.
- Permitir concluir/remover com confirmação apropriada quando a informação puder ser perdida.
- Não executar efeitos nem presumir interpretação de texto de carta.

## Critérios de aceite

- [ ] Lembretes podem ser criados, editados, concluídos e removidos. (Edição ainda não existe; criar/concluir/remover funcionam.)
- [x] Turno e fase são associações opcionais e usam o estado da sessão correta.
- [ ] Lembretes importantes não desaparecem silenciosamente. (A remoção é imediata, sem confirmação.)
- [ ] Testes cobrem duração, destaque e persistência. (Duração ainda não é aplicada; destaque e persistência têm cobertura.)

## Implementação aplicada - 2026-11-06 (Fase 4)

- Componente DuelReminders com CRUD: nome, descrição, jogador, turno/fase gatilho, concluir/remover.
- Modelo Reminder + migration `reminders`.
- Destaque visual quando o turno/fase configurados são alcançados.
- Remoção direta (sem modal de confirmação — pendência menor).
- Duração em turnos e condição de remoção registradas mas não aplicadas automaticamente.

## Nota de dispositivo - 2026-11-09 (histórico, superada)

- Modal renderiza e abre corretamente no dispositivo.
- Ações de CRUD não atualizavam a UI em tempo real (mesma limitação do Livewire no WebView NativePHP).
- Funcionava no navegador.

## Atualização e validação em dispositivo - 2026-11-09

- O modal de lembretes passou a ser HTML/JS simples dentro de `duel-arena.blade.php`, usando `fetch` contra `DuelToolsController` (`listReminders`, `saveReminder`, `toggleReminder`, `deleteReminder`).
- Rotas: `GET/POST /duel/{duel}/reminders`, `POST /duel/{duel}/reminders/{reminder}/toggle` e `DELETE /duel/{duel}/reminders/{reminder}`.
- O endpoint de listagem agora calcula `highlighted` comparando `trigger_turn`/`trigger_phase` com o turno e a fase atuais; o modal aplica borda/fundo de destaque.
- Ícones dentro do HTML gerado por JS foram substituídos por entidades (`&#10003;`, `&#8635;`, `&#215;`) para não injetar `<svg>`/`<x-icon>` dentro de strings JavaScript.
- O componente Livewire antigo (`DuelReminders`) e a duplicata do modal foram removidos.
- Validação no moto g56 5G (ZF525FLTVF): criar lembrete com turno 1/fase DP exibiu o item destacado; concluir alternou para o estado esmaecido com ícone de reabrir; remover deixou a lista vazia ("Nenhum lembrete registrado."). Sem erros no `browser.log`.
- Cobertura: `tests/Feature/DuelToolsTest.php` cobre criação, alternância, remoção, persistência e cálculo de destaque.
