# 010 — Layout multiplayer básico

- **Status:** Validado — navegador para 1–8 jogadores e NativePHP Android para 2 jogadores
- **Área:** Arena
- **Prioridade:** Alta

## Implementado no repositório

- Arena com painéis de jogadores e suporte de sessão a até oito participantes.
- Layout em grade para mais de dois participantes.
- Em partidas de dois jogadores, o painel do oponente aparece acima do jogador da casa, com a faixa de turno entre os painéis.
- Rotação de 180 graus do painel superior do oponente em partidas de dois jogadores, ativada por padrão e controlável.
- Testes para renderização de oito jogadores e layouts básicos de 2 e 3 participantes.

## Validação pendente

- [x] Validar os layouts de 1 a 8 jogadores em viewports móveis, paisagem e desktop.
- [x] Confirmar legibilidade dos PV e alvos de toque para 5–8 participantes.
- [x] Avaliar comportamento em dispositivo Android NativePHP com uma partida 2P.
- [x] Corrigir o rótulo dos assentos, que usava a mesma cor de texto e fundo e deixava a identificação ilegível.
- [x] Validar contraste dos acentos e identificação dos painéis nos três temas e nas quantidades cobertas.

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

- [x] Corrigir o binding Alpine inválido, renderizando as classes no Blade em vez de passar sintaxe PHP ao Alpine.
- [x] Garantir contraste do rótulo do assento com tokens de superfície e texto do tema.
- [x] Adicionar teste que verifica rótulos renderizados sem expressão PHP no atributo Alpine.
- [x] Adicionar teste/validação que cubra o estado de rotação do painel oponente.
- [ ] Decidir e validar layout 2P em paisagem na tarefa 011.
- [ ] Validar dimensões e legibilidade para 5–8 jogadores em dispositivo móvel.

## Validação após correção

- `php artisan test --compact tests\Feature\DuelArenaTest.php tests\Feature\DuelTurnTest.php` — 39 testes passaram.
- Na rota da arena com quatro participantes, o navegador exibiu todos os rótulos, não encontrou binding `:class` inválido e não reportou erros de JavaScript.
- A validação de larguras/telas móveis e em dispositivo NativePHP permanece pendente; os testes de feature verificam o HTML renderizado e a ausência da expressão PHP inválida.

**Conclusão da revisão inicial:** a falha conhecida de renderização foi corrigida e coberta por teste. Naquela revisão, a tarefa seguia aberta para validar rotação, UX mobile e NativePHP.

## Complemento da validação — 2026-11-03

- Em dois jogadores, a apresentação agora inverte a ordem dos assentos sem alterar seus dados: o assento 2 (oponente) fica no topo, a faixa de turno fica entre os painéis e o assento 1 permanece abaixo.
- Teste de feature confirma a ordem visual do oponente antes do jogador da casa, a rotação padrão e a alternância do controle.
- No navegador, em 390 × 844 e 844 × 390, os painéis de dois jogadores permaneceram sem overflow horizontal. A leitura do estilo calculado confirmou `rotate: 180deg` por padrão, `none` ao desativar e `180deg` ao reativar; os PV continuaram em 8.000 durante a verificação.
- A partida existente de quatro jogadores também foi conferida nas duas dimensões, sem overflow horizontal. Não foram alterados PV nem outros dados persistidos.
- Testes focados da arena: 47 passaram, com 198 assertions.
- Permanecem pendentes: matriz visual de 1–8 jogadores, validação de alvos de toque/contraste em todos os temas e validação no Android NativePHP.

**Conclusão da etapa anterior:** o requisito de ordem e rotação para dois jogadores estava corrigido e validado no navegador e nos testes; a validação responsiva e NativePHP ainda estava pendente. Essa conclusão é substituída pela validação final abaixo.

## Validação final — 2026-11-03

- Os testes focados `DuelArenaTest.php` e `DuelTurnTest.php` passaram: 78 testes e 280 assertions.
- No navegador, os layouts de 1–8 jogadores foram conferidos em 390 × 844, 844 × 390 e 1280 × 800. Nenhuma combinação apresentou overflow horizontal ou controles dos painéis abaixo de 44 px.
- A inspeção visual confirmou que 4 jogadores usam duas colunas no desktop e 8 jogadores permanecem em uma coluna no celular, com PV e controles legíveis. O layout continua vertical em paisagem.
- Os oito acentos de jogador nos três temas mantêm contraste mínimo de 3:1 contra as superfícies examinadas, adequado aos elementos gráficos não textuais usados nos painéis.
- O build debug NativePHP Android foi concluído e aberto no emulador. A primeira tela levou cerca de 30 segundos para sair de “Loading”; a latência de inicialização continua sendo uma pendência da tarefa 003. Depois, uma partida 2P confirmou o oponente no painel superior rotacionado e o jogador da casa abaixo; PV permaneceram em 8.000 durante a verificação. A expansão do painel superior também preservou a rotação e os PV.
- O emulador foi validado em 2P, não em todas as contagens. Os layouts de 1–8 foram validados no navegador.

**Conclusão:** a tarefa 010 está validada para o escopo funcional e responsivo do navegador e para o caso 2P no NativePHP Android.

## Registro do trabalho, metodologia e pendências

### O que foi feito e como

- A arena renderiza de 1 a 8 participantes, usando uma coluna em larguras estreitas e grade em larguras maiores; em duelo 2P o oponente fica acima e a orientação do painel superior é invertida por padrão.
- O binding Alpine inválido foi corrigido e o contraste dos rótulos/acentos foi revisado para os temas.
- A matriz de browser cobriu retrato, paisagem e desktop; o caso 2P e a expansão do painel foram observados no NativePHP Android.

### Metodologia e regras aplicadas

- Inspecionar primeiro os layouts e testes existentes; corrigir a causa do erro de expressão (Blade/PHP passado como JavaScript) e adicionar teste de regressão.
- Validar separadamente renderização automatizada, dimensões/overflow no navegador e comportamento real do build NativePHP.
- Preservar assentos e identidade dos jogadores; a ordem visual do 2P não altera os dados do duelo.

### Skills e fontes de apoio

- `laravel-best-practices`, `livewire-development`, `tailwindcss-development` e `testing-best-practices`.
- Ferramentas de browser e NativePHP/ADB para validação visual e no emulador.

### O que falta

- Os layouts de 1–8 foram validados no navegador, mas não no Android; o emulador foi validado em 2P.
- A matriz de 1–8 no Android não faz parte da comprovação registrada e deve ser feita se houver regressão de layout ou mudança de suporte.
- Layout lado a lado em paisagem não foi adotado; reavaliar como melhoria separada de UX, sem violar a regra de manter o painel do oponente acima em 2P.

### Superfícies e reprodução

- Arena/controles: `app/Livewire/DuelArena.php`, `resources/views/livewire/duel-arena.blade.php`, `resources/views/components/icon.blade.php`.
- Testes: `tests/Feature/DuelArenaTest.php` e `tests/Feature/DuelTurnTest.php`.
- Testes focados da validação final: `php artisan test --compact tests\Feature\DuelArenaTest.php tests\Feature\DuelTurnTest.php` — 78 testes/280 assertions.
- Browser: 390×844, 844×390 e 1280×800; layouts de 1–8, overflow, alvos ≥44 px, acentos e contraste; no Android NativePHP apenas a partida 2P foi comprovada. A primeira tela do emulador levou ~30 s para sair de “Loading”; isso é evidência da tarefa 003, não um bloqueio funcional do layout.
- O percurso histórico incluiu binding Alpine inválido, faixa de turno duplicada em 1P/3P e uma interação de QA que criou evento de restauração sem alteração; cada incidente foi corrigido/isolado e não deve ser confundido com falha atual sem reproduzir.


## Validação em dispositivo - 2026-11-06

- Tela de configuração carrega com 1 a 8 jogadores, presets 8000/4000/16000/custom, seletores de ícone e botão INICIAR ARENA DE DUELO.
- Navegação Nova partida -> /duel/new confirmada.
- Criado duelo com 8 jogadores (Duelo #2) no aparelho; todos os 8 painéis renderizam com botão Expandir; expansão responde ao toque.
- Paisagem 2P e legibilidade de 5-8 jogadores não foram exercitadas visualmente (este modelo não vê screenshots); validar manualmente.