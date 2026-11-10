# 029 — Acessibilidade e movimento reduzido

- **Status:** Pendente
- **Área:** Acessibilidade
- **Prioridade:** Alta

## Objetivo

Garantir que funções essenciais sejam compreensíveis e operáveis por usuários com diferentes necessidades.

## Escopo

- Revisar contraste, escala de texto e legibilidade dos PV.
- Adicionar nomes acessíveis e foco previsível a botões/controles.
- Manter alvos de toque confortáveis, inclusive em layouts compactos.
- Respeitar preferência de movimento reduzido e oferecer controle de animações.
- Não usar apenas cor, som ou vibração para comunicar alterações.

## Critérios de aceite

- [ ] Ações principais são operáveis sem depender de som, vibração ou cor.
- [ ] Controles têm rótulos acessíveis e estados perceptíveis.
- [ ] Preferência de movimento reduzido desativa transições não essenciais.
- [ ] Testes/inspeção cobrem fontes ampliadas e layouts de 1–8 jogadores.


## Validação em dispositivo - 2026-11-06

- Regra @media (prefers-reduced-motion: reduce) presente nas folhas de estilo carregadas.
- Exercício visual da animação com reduced-motion não foi possível (este modelo não vê screenshots); validar manualmente.