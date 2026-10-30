# 014 — Sorteio do primeiro jogador

- **Status:** Pendente
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

- [ ] O selecionado pertence à sessão atual.
- [ ] O sorteio não modifica automaticamente estado de turno.
- [ ] Resultado e contexto são registrados no histórico.
- [ ] Testes cobrem sessões com diferentes quantidades de participantes.
