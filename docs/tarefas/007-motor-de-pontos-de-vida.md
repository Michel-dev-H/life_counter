# 007 — Motor de Pontos de Vida

- **Status:** Revisado — melhorias aprovadas aguardando implementação
- **Área:** Regras e cálculo
- **Prioridade:** Bloqueadora

## Implementado no repositório

- Operações de adicionar, subtrair, reduzir à metade, restaurar, definir e dobrar no modelo.
- PV inteiros, limite inferior zero e limite superior seguro.
- Registro de valores anterior/posterior e tipo de operação.
- Desfazer/refazer em ordem, com histórico isolado por duelo.
- Interface atual para atalhos de adição/subtração, metade e restauração.
- Testes unitários e de integração para as operações implementadas.

## Revisão pendente

- [ ] Validar cálculos, limites e arredondamento no domínio e na interface.
- [ ] Confirmar comportamento do histórico em sequências de desfazer/refazer e após nova operação.
- [ ] Revisar autorização/escopo do jogador associado ao duelo.
- [ ] Verificar visualmente se os valores continuam legíveis em todos os layouts.

## Lacunas relacionadas

Teclado numérico próprio, ação visível para definição direta/dobro e atalhos configuráveis são cobertos pela tarefa 008. Feedback animado/tátil/sonoro está na tarefa 009.

## Resultado da revisão

- Testes executados: `php artisan test --compact tests\Feature\LifePointsTest.php tests\Feature\DuelArenaTest.php` — 36 testes passaram.
- A lógica cobre operações básicas, limite inferior/superior dos PV, arredondamento da metade, eventos, undo/redo, limpeza da trilha de redo e isolamento entre jogadores.
- O escopo do jogador na arena é limitado ao duelo carregado; há teste que confirma 404 para jogador de outro duelo.
- Existe uma falha de limite: `addLifePoints(PHP_INT_MAX)` pode converter a soma para `float` antes do clamp, incompatível com o retorno `int` do callback. Quantidades também são persistidas em coluna `unsignedInteger`, que não comporta qualquer `int` aceito pelo método.
- Ações sem mudança efetiva (por exemplo, adicionar PV já no teto) atualmente criam evento e apagam eventos de redo. Esse comportamento deve ser ajustado.
- A atualização calcula o resultado antes de entrar na transação. Corrida entre requisições simultâneas pode causar histórico/estado incoerentes; foi decidido adiar a proteção enquanto o uso esperado for um único aparelho offline. Reavaliar antes de permitir clientes simultâneos.

## Decisões acordadas

- Rejeitar quantidades de operação acima de `DuelPlayer::MAX_LIFE_POINTS` (999.999), com erro explícito, preservando o clamp normal do resultado para valores válidos.
- Ignorar operações cujo PV final seja igual ao PV atual: não criar evento e não apagar redo pendente.
- Adiar proteção contra concorrência para uma etapa em que múltiplos aparelhos/clientes possam operar a mesma partida.

## Próximos passos da tarefa

- [ ] Implementar a validação de quantidade máxima no domínio e expor erro apropriado na chamada Livewire.
- [ ] Fazer operações sem alteração efetiva retornarem antes de limpar redo ou criar evento.
- [ ] Adicionar testes para os dois comportamentos acordados e para limites da quantidade.
- [ ] Manter a decisão de concorrência documentada até o escopo de execução mudar.

**Conclusão:** a base do motor de PV está funcional e os testes focados passam, mas não deve ser considerada concluída até as melhorias aprovadas acima serem implementadas e verificadas.
