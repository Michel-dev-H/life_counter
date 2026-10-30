# 010 — Layout multiplayer básico

- **Status:** Revisado — correção necessária
- **Área:** Arena
- **Prioridade:** Alta

## Implementado no repositório

- Arena com painéis de jogadores e suporte de sessão a até oito participantes.
- Layout em grade para mais de dois participantes.
- Rotação de 180 graus do painel do oponente em partidas de dois jogadores, ativada por padrão e controlável.
- Testes para renderização de oito jogadores e layouts básicos de 2 e 3 participantes.

## Revisão pendente

- [ ] Validar os layouts de 1 a 8 jogadores em larguras de tela reais, retrato e paisagem.
- [ ] Confirmar legibilidade dos PV e alvos de toque para 5–8 participantes.
- [ ] Avaliar comportamento em dispositivo Android NativePHP.
- [ ] Corrigir conflitos de cor/identificação com o sistema de temas, se encontrados.

## Lacunas relacionadas

Expandir temporariamente um painel, reordenar posições e configurar orientação são tratados na tarefa 011.

## Resultado da revisão

- Testes executados: `php artisan test --compact tests\Feature\DuelArenaTest.php tests\Feature\DuelTurnTest.php` — 38 testes passaram.
- A inspeção no navegador confirmou que quatro jogadores em viewport de 390 px ficam em uma coluna, com cartões de largura integral e rolagem vertical, sem overflow horizontal. Isso favorece alvos legíveis, mas exige rolar para alcançar os demais jogadores.
- Em dois jogadores, o painel de fase/turno aparece entre os painéis; o painel do oponente tem controle de rotação de 180 graus.
- Em viewport paisagem de 844 × 390, os dois painéis continuaram empilhados verticalmente e a tela precisa rolar. Considerar layout lado a lado em paisagem como melhoria de UX na tarefa 011; validar a decisão em dispositivo antes de alterar.
- O navegador reportou erro de sintaxe Alpine no atributo `:class="$player->seat === 1 ? 'seat-tone-1' : 'seat-tone-2'"`: a expressão contém sintaxe PHP que não é válida em JavaScript. Corrigir para uma expressão Blade/HTML já resolvida no servidor ou uma expressão Alpine válida e cobrir a renderização sem erro de runtime.
- A rotação não foi considerada funcionalmente verificada nesta rodada; uma interação automatizada no navegador atingiu o controle de restauração e criou um evento sem alteração. Esse evento específico foi removido após confirmação por ID, duelista, valores, operação e horário; os PV permaneceram em 8.000. Evitar usar a interação como prova até repetir com alvo estável.
- O teste existente de oito jogadores confirma a presença de P8 e seu nome, mas não valida visualmente as dimensões, alvos de toque ou distribuição de 5–8 painéis.
- Não houve validação em build NativePHP/dispositivo Android.

## Próximos passos

- [ ] Corrigir o binding Alpine inválido e verificar que não há erro no console.
- [ ] Adicionar teste/validação que cubra o estado de rotação do painel oponente.
- [ ] Decidir e validar layout 2P em paisagem na tarefa 011.
- [ ] Validar dimensões e legibilidade para 5–8 jogadores em dispositivo móvel.

**Conclusão:** a estrutura básica da arena está implementada e os testes focados passam, mas há erro de runtime no binding Alpine e a experiência mobile ainda precisa de validação. A tarefa permanece aberta até corrigir e verificar esses pontos.
