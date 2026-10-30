# 009 — Feedback visual, tátil e sonoro de PV

- **Status:** Pendente
- **Área:** Interação e feedback
- **Prioridade:** Média

## Objetivo

Dar retorno rápido e inequívoco para alterações de PV sem atrasar as jogadas nem comprometer a precisão.

## Escopo

- Exibir transição numérica curta entre valor anterior e novo valor.
- Diferenciar dano e recuperação visualmente com feedback consistente.
- Implementar preferências opcionais de vibração e efeitos sonoros apenas após validar APIs NativePHP.
- Respeitar preferência de movimento reduzido e permitir desativar efeitos.
- Não usar animação como fonte do cálculo: o resultado deve ser determinado e salvo antes da apresentação.

## Critérios de aceite

- [ ] Valor armazenado e exibido final permanecem exatos, em inteiros.
- [ ] A animação não bloqueia ações seguintes e respeita acessibilidade.
- [ ] Som e vibração podem ser desativados e não são obrigatórios para compreender o resultado.
- [ ] Falha de integração nativa é tratada explicitamente, sem sucesso falso.
