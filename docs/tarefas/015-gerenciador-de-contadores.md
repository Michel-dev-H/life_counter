# 015 — Gerenciador de contadores

- **Status:** Pendente
- **Área:** Contadores
- **Prioridade:** Alta

## Objetivo

Permitir gerenciar contadores genéricos e simultâneos para diferentes alvos sem codificar regras específicas de cartas.

## Escopo

- Criar contadores associados a jogador, carta/posição ou efeito temporário.
- Permitir nome personalizado, quantidade, cor e ícone.
- Implementar adicionar, remover, definir quantidade, renomear, transferir manualmente e excluir.
- Manter tipos de contador independentes, inclusive múltiplos tipos no mesmo alvo.
- Permitir desfazer exclusão ou confirmar remoção, conforme padrão definido.
- Persistir operações e manter histórico suficiente para restauração.

## Critérios de aceite

- [ ] Contadores distintos nunca compartilham quantidade por engano.
- [ ] Operações respeitam limites e não permitem quantidade inválida.
- [ ] Transferências atualizam origem e destino atomicamente.
- [ ] Exclusão não remove referências de outros contadores ou sessões.
- [ ] Testes cobrem ciclo de vida, múltiplos contadores e recuperação.
