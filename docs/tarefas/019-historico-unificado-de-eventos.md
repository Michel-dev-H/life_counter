# 019 — Histórico unificado da partida

- **Status:** Pendente
- **Área:** Histórico
- **Prioridade:** Média

## Objetivo

Unificar em uma consulta simples os eventos relevantes da sessão, sem tornar a arena excessivamente complexa.

## Escopo

- Apresentar início da partida, mudanças de PV, sorteios, alterações de contadores e eventos relevantes de turnos/lembretes.
- Registrar jogador/alvo, valores anterior e final, operação e data/hora quando aplicável.
- Permitir consulta rápida e filtragem simples, preservando a sequência temporal.
- Diferenciar eventos informativos de alterações que podem ser desfeitas.
- Respeitar o escopo da sessão e evitar crescimento sem controle.

## Critérios de aceite

- [ ] Eventos exibidos pertencem exclusivamente à partida consultada.
- [ ] Histórico de PV atual é integrado sem duplicar nem corromper eventos.
- [ ] Eventos aleatórios exibem resultados individuais e contexto.
- [ ] Formato permanece legível em telas pequenas e com oito participantes.
- [ ] Testes cobrem ordenação, associação e operações destrutivas.
