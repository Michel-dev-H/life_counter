# 014 — Sorteio do primeiro jogador

- **Status:** Implementado e validado em dispositivo — sorteio entre os jogadores da sessão com resultado exibido inline; registro em histórico unificado pendente
- **Área:** Sorteios
- **Prioridade:** Média

## Objetivo

Selecionar aleatoriamente um participante da sessão para auxiliar o grupo a decidir quem começa.

## Escopo

- Sortear entre os jogadores participantes da partida atual.
- Exibir o nome/assento sorteado de forma clara e acessível.
- Permitir repetir o sorteio mediante ação explícita.
- Registrar o resultado como evento auxiliar, sem modificar turno ou fase automaticamente.
- Informar que o resultado é apenas um auxílio e que os jogadores devem seguir o formato aplicável.

## Critérios de aceite

- [x] O selecionado pertence à sessão atual.
- [x] O sorteio não modifica automaticamente estado de turno.
- [ ] Resultado e contexto são registrados no histórico. (Exibido inline; não há histórico unificado — tarefa 019.)
- [x] Testes cobrem sessões com diferentes quantidades de participantes.

## Implementação aplicada - 2026-11-06 (Fase 4)

- Botão "Sortear quem começa" no DuelArena que escolhe um jogador aleatório da sessão.
- Exibe o nome do jogador sorteado sem alterar turno ou fase.
- Resultado exibido inline na arena.

## Correção e validação em dispositivo - 2026-11-09

- O botão usa `wire:click="drawFirstPlayer"`; a ação sorteia entre `duel->duelPlayers` e preenche `firstPlayerDrawn`.
- Correção: o valor de `firstPlayerDrawn` era calculado mas não renderizado. A arena agora mostra um bloco `role="status"` com "<nome> começa. O sorteio é apenas um auxílio; sigam o formato aplicável.".
- Validação no moto g56 5G (ZF525FLTVF): o botão respondeu e o nome sorteado apareceu no bloco de status; turno e fase permaneceram inalterados.
- Cobertura em `tests/Feature/DuelArenaTest.php`: sorteio restrito a 2 jogadores e a uma sessão de 8 jogadores, verificação do texto exibido e de que turno/fase não mudam.
