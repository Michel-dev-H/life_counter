# 007 — Motor de Pontos de Vida

- **Status:** Implementado — aguardando revisão
- **Área:** Regras e cálculo
- **Prioridade:** Bloqueadora

## Implementado no repositório

- Operações de adicionar, subtrair, reduzir à metade, restaurar, definir e dobrar no modelo.
- PV inteiros, limite inferior zero e limite superior seguro.
- Registro de valores anterior/posterior e tipo de operação.
- Desfazer/refazer em ordem, com histórico isolado por duelo.
- Interface atual para atalhos de adição/subtração, metade e restauração.
- Testes unitários e de integração para as operações implementadas.

## Validações concluídas

- [x] Validar cálculos, limites e arredondamento no domínio e na interface.
- [x] Confirmar comportamento do histórico em sequências de desfazer/refazer e após nova operação.
- [x] Revisar autorização/escopo do jogador associado ao duelo.
- [x] Verificar visualmente a legibilidade dos valores em telas mobile e desktop.

## Lacunas relacionadas

Teclado numérico próprio, ação visível para definição direta/dobro e atalhos configuráveis são cobertos pela tarefa 008. Feedback animado/tátil/sonoro está na tarefa 009.

## Resultado da revisão inicial

- Testes executados: `php artisan test --compact tests\Feature\LifePointsTest.php tests\Feature\DuelArenaTest.php` — 36 testes passaram.
- A lógica cobre operações básicas, limite inferior/superior dos PV, arredondamento da metade, eventos, undo/redo, limpeza da trilha de redo e isolamento entre jogadores.
- O escopo do jogador na arena é limitado ao duelo carregado; há teste que confirma 404 para jogador de outro duelo.
- A revisão identificou uma falha de limite: `addLifePoints(PHP_INT_MAX)` podia converter a soma para `float` antes do clamp, incompatível com o retorno `int` do callback. Quantidades também são persistidas em coluna `unsignedInteger`, que não comporta qualquer `int` aceito pelo método.
- Também identificou que ações sem mudança efetiva (por exemplo, adicionar PV já no teto) criavam eventos e apagavam eventos de redo; esse comportamento foi corrigido nesta implementação.
- A atualização calcula o resultado antes de entrar na transação. Corrida entre requisições simultâneas pode causar histórico/estado incoerentes; foi decidido adiar a proteção enquanto o uso esperado for um único aparelho offline. Reavaliar antes de permitir clientes simultâneos.

## Decisões acordadas

- Rejeitar quantidades de operação acima de `DuelPlayer::MAX_LIFE_POINTS` (999.999), com erro explícito, preservando o clamp normal do resultado para valores válidos.
- Ignorar operações cujo PV final seja igual ao PV atual: não criar evento e não apagar redo pendente.
- Adiar proteção contra concorrência para uma etapa em que múltiplos aparelhos/clientes possam operar a mesma partida.

## Próximos passos da tarefa

- [x] Implementar a validação de quantidade máxima no domínio e expor erro apropriado na chamada Livewire.
- [x] Fazer operações sem alteração efetiva retornarem antes de limpar redo ou criar evento.
- [x] Adicionar testes para os dois comportamentos acordados e para limites da quantidade.
- [x] Manter a decisão de concorrência documentada até o escopo de execução mudar.

## Implementação e validação

- Quantidades acima de `DuelPlayer::MAX_LIFE_POINTS` (999.999) agora são rejeitadas no domínio; a arena mostra um erro explícito sem alterar PV ou criar evento.
- Operações que não alteram os PV retornam antes de limpar eventos de redo ou criar um novo evento.
- Testes focados: 56 passaram. Suíte completa: 104 passaram, com 281 assertions.
- Pint concluiu sem erros. PHPStan, executado com limite de 512 MB, encontrou 24 problemas de tipagem preexistentes no worktree, inclusive assinaturas de relacionamentos Eloquent não tipadas; não apontou as novas validações de quantidade ou o retorno antecipado.
- Verificação visual da arena em 390 px e 1280 px: os valores permanecem legíveis e não há rolagem horizontal.
- Proteção contra concorrência segue adiada para partidas operadas em um único aparelho offline; reavaliar antes de permitir operação simultânea em vários clientes.

**Conclusão:** as melhorias aprovadas foram implementadas e verificadas; a tarefa aguarda revisão.

## Registro do trabalho, metodologia e pendências

### O que foi feito e como

- Foi corrigido o limite de quantidade de operação: valores acima de `DuelPlayer::MAX_LIFE_POINTS` (999.999) são rejeitados no domínio e a arena apresenta erro explícito.
- Operações que deixam o PV inalterado retornam antes de criar evento ou apagar o histórico de refazer.
- O tratamento de limites foi aplicado às ações Livewire de adicionar/subtrair e coberto com testes de domínio e integração, incluindo overflow com `PHP_INT_MAX`.

### Metodologia e regras aplicadas

- TDD: primeiro foram adicionados testes que falhavam para entrada excessiva, overflow, ausência de mudança e perda indevida de redo; depois foram feitas as alterações mínimas e os testes focados foram repetidos.
- Conforme `.ai/rules/app.md`, a regra de limite fica no domínio, sem duplicar a regra de negócio no componente; conforme `.ai/rules/tests.md`, testes comportamentais seguem os padrões do projeto.
- Validação focada, suíte completa, Pint e inspeção visual foram executados; os problemas PHPStan observados foram separados dos novos comportamentos e registrados como preexistentes.

### Skills e fontes de apoio

- `laravel-best-practices`, `livewire-development` e `testing-best-practices`; fluxo TDD conforme a metodologia de testes do projeto.
- `.ai/rules/app.md` e `.ai/rules/tests.md`.

### O que falta

- A implementação e os testes estão concluídos; resta a revisão final indicada pelo status.
- Reavaliar concorrência/transações antes de permitir que mais de um aparelho ou cliente altere a mesma partida. O uso suportado aqui continua sendo um único dispositivo offline.
- Os 24 diagnósticos PHPStan relatados no ciclo eram preexistentes no worktree; não foram resolvidos como parte desta tarefa.

### Superfícies e reprodução

- Domínio: `app/Models/DuelPlayer.php`; ações/erros: `app/Livewire/DuelArena.php` e `resources/views/livewire/duel-arena.blade.php`.
- Testes: `tests/Feature/LifePointsTest.php` e `tests/Feature/DuelArenaTest.php`.
- Testes focados finais registrados: 56; suíte completa: 104 testes, 281 assertions. A contagem inicial de 53 focados pertence ao ciclo anterior à inclusão de todos os casos finais, não é uma divergência de resultado.
- Comandos de referência: `php artisan test --compact tests\Feature\LifePointsTest.php tests\Feature\DuelArenaTest.php` e `php artisan test --compact`.
- Validação manual registrada: arena em 390 px e 1280 px, PV legíveis e sem rolagem horizontal. Pint passou. PHPStan, com 512 MB, encontrou 24 diagnósticos preexistentes no worktree; não os atribuir à alteração nem os declarar corrigidos.
- TDD cobriu overflow com `PHP_INT_MAX`, limite de quantidade 999.999, ausência de evento em no-op e preservação do redo. Concorrência continua explicitamente fora do escopo.
