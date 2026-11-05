# Backlog de funcionalidades

Este diretório transforma o prompt do companion de Yu-Gi-Oh! em tarefas verificáveis para o projeto Laravel/Livewire existente, com empacotamento Android planejado via NativePHP.

## Decisões de escopo

- Laravel/Livewire e NativePHP são a stack escolhida. As instruções do prompt sobre Flutter/React Native foram substituídas; não migrar para Flutter ou React Native.
- Preservar e revisar o que já existe antes de ampliar ou refatorar.
- “Feito — aguardando revisão” indica que há implementação no repositório, mas ela ainda precisa ser confrontada com o escopo e validada. Não equivale a aceite final.
- Funcionalidades não verificadas ou incompletas permanecem pendentes, mesmo que haja algum código relacionado.
- Confirmar compatibilidade NativePHP, APIs nativas, persistência offline e geração de APK no ambiente real antes de declarar esses requisitos concluídos.
- Não incluir artes protegidas sem licença; imagens incluídas no app devem ser originais ou licenciadas para uso.

## Como usar

Executar as tarefas na ordem sugerida abaixo, respeitando dependências e validando cada funcionalidade no navegador e, quando aplicável, em build/dispositivo Android. Cada arquivo lista o escopo e os critérios de aceite.

## Handoff e continuidade do trabalho

Os registros de tarefa são a fonte de continuidade para outro agente ou contribuinte. Antes de iniciar uma tarefa:

1. Ler este índice e o arquivo completo daquela tarefa; não tomar o status isolado como prova de validação.
2. Conferir `git status`/diff antes de editar e preservar alterações preexistentes.
3. Separar fatos observados, decisões aprovadas, resultados de testes e hipóteses. Quando houver resultados de execuções diferentes, manter a cronologia e repetir a validação antes de declarar o estado atual.
4. Usar as skills pertinentes disponíveis e registrar os nomes realmente ativados/usados; registrar também quando nenhuma skill foi necessária. Não atribuir pesquisa, testes, aprovação Android ou comandos que não ocorreram.
5. Em mudanças Laravel/PHP, abrir `.ai/rules/index.md` e ler toda regra aplicável antes de editar; procurar requisitos adicionais em `.ai/rules`. Manter o fluxo de teste mais estreito possível e documentar comandos/resultados exatos.
6. Ao terminar, atualizar no próprio arquivo da tarefa: arquivos tocados, decisão/implementação, metodologia, regras, skills/fontes, comandos e resultados (inclusive falhas), limitações, estado de dados de QA e passos reproduzíveis restantes.
7. Não marcar como validado algo que só compilou. Distinguir teste automatizado, navegador, NativePHP/emulador, offline e persistência.

### Tarefas com trabalho registrado nesta sequência

Os arquivos abaixo incluem histórico/decisões e handoff específico. As demais tarefas continuam sem implementação ou aceite concluídos; 001, 002 e 004 tiveram análise/planejamento preliminares, mas não foram fechadas como tarefas:

- 003: viabilidade NativePHP/offline — evidências de build, histórico divergente da prova offline, ambiente e comandos.
- 005: revisão da configuração básica — testes, questões de preset/rótulos e validação visual pendente.
- 006: identidade dos jogadores — implementação, arquivos e validação browser.
- 007–009: motor, calculadora e feedback de PV — decisões, TDD, limites de análise estática e validações nativas pendentes.
- 010–012: layout multiplayer, controles de layout e dados — posição obrigatória do oponente, testes/browser/emulador e pendências.
- 018: fases/turnos/cronômetro — decisões aprovadas e roteiro de implementação.
- 022: persistência/seleção de temas — correções e validação browser, Android pendente.
- 024: papel de parede — achados, decisões aprovadas e roteiro de implementação, sem código alterado na revisão.
- 030: navegação — decisões de UX, mudanças, evidências e itens ainda não implementados.

## Tarefas

| ID | Tarefa | Status inicial |
|---|---|---|
| [001](./001-pesquisar-regras-oficiais.md) | Pesquisar regras oficiais e fontes | Pendente |
| [002](./002-pesquisar-ux-mobile.md) | Pesquisar companions e UX mobile | Pendente |
| [003](./003-validar-nativephp-offline.md) | Validar NativePHP, Android e offline | Parcial — build/abertura confirmados; prova offline anterior existe, repetição posterior inconclusiva; primeiro frame/retomada pendentes |
| [004](./004-definir-arquitetura-local.md) | Definir arquitetura e estado local | Pendente |
| [005](./005-configuracao-basica-da-partida.md) | Configuração básica da partida | Revisado — pendências de pesquisa e validação |
| [006](./006-identidade-e-opcoes-dos-jogadores.md) | Identidade e opções dos jogadores | Implementado — aguardando revisão |
| [007](./007-motor-de-pontos-de-vida.md) | Motor de Pontos de Vida | Implementado — aguardando revisão |
| [008](./008-controles-avancados-de-pv.md) | Teclado e atalhos configuráveis de PV | Implementado — aguardando revisão |
| [009](./009-feedback-das-alteracoes-de-pv.md) | Feedback visual, tátil e sonoro de PV | Implementado parcialmente — áudio e validação tátil no Android pendentes |
| [010](./010-layout-multiplayer-basico.md) | Layout multiplayer básico | Validado — navegador 1–8 jogadores e NativePHP Android 2P |
| [011](./011-controles-de-layout-multiplayer.md) | Expandir, reorganizar e orientar painéis | Implementado e validado — navegador; expansão 2P verificada no Android |
| [012](./012-lancamento-de-dados.md) | Lançamento de dados | Implementada e validada no navegador; validação Android pendente por ANR no emulador |
| [013](./013-lancamento-de-moedas.md) | Lançamento de moedas | Pendente |
| [014](./014-sorteio-de-primeiro-jogador.md) | Sorteio do primeiro jogador | Pendente |
| [015](./015-gerenciador-de-contadores.md) | Gerenciador de contadores | Pendente |
| [016](./016-referencias-de-cartas-e-campo-auxiliar.md) | Referências de cartas e campo auxiliar | Pendente |
| [017](./017-lembretes-de-efeitos.md) | Lembretes de efeitos e duração | Pendente |
| [018](./018-fases-turnos-e-cronometro.md) | Fases, turnos e cronômetro | Revisado — melhorias aprovadas aguardando implementação |
| [019](./019-historico-unificado-de-eventos.md) | Histórico unificado da partida | Pendente |
| [020](./020-salvamento-e-recuperacao.md) | Salvamento e recuperação de partida | Pendente |
| [021](./021-arquivo-e-retencao-de-partidas.md) | Arquivo e retenção de partidas | Pendente |
| [022](./022-selecao-persistente-de-temas.md) | Seleção persistente de temas | Correções implementadas — validação NativePHP pendente |
| [023](./023-identidade-visual-dos-temas.md) | Identidade visual completa dos temas | Pendente |
| [024](./024-upload-e-remocao-de-papel-de-parede.md) | Upload e remoção de papel de parede | Revisado — correções aprovadas aguardando implementação |
| [025](./025-galeria-interna-de-papeis-de-parede.md) | Galeria interna de papéis de parede | Pendente |
| [026](./026-imagem-local-e-ajustes.md) | Imagem local e ajustes de enquadramento | Pendente |
| [027](./027-contraste-e-scrim-do-papel-de-parede.md) | Contraste e scrim de papel de parede | Pendente |
| [028](./028-recursos-nativos-durante-a-partida.md) | Recursos nativos durante a partida | Pendente |
| [029](./029-acessibilidade-e-movimento-reduzido.md) | Acessibilidade e movimento reduzido | Pendente |
| [030](./030-navegacao-e-ferramentas-contextuais.md) | Navegação e ferramentas contextuais | Em andamento — início, nova partida, aparência e retomada acessíveis |
| [031](./031-design-system-e-polimento-visual.md) | Design system e polimento visual | Pendente |
| [032](./032-qa-e-testes-de-integracao.md) | QA e testes de integração | Pendente |
| [033](./033-build-android-e-documentacao-de-entrega.md) | Build Android e documentação de entrega | Pendente |

## Ordem sugerida

1. Descoberta e viabilidade: 001–004.
2. Revisão da base e experiência central: 005–011.
3. Ferramentas de partida: 012–018.
4. Persistência, histórico e personalização: 019–027.
5. Integração nativa, acessibilidade e navegação: 028–031.
6. Validação e entrega: 032–033.

As tarefas marcadas como feitas devem ser revistas antes de iniciar extensões que dependem delas. Dependências reais e decisões técnicas devem ser anotadas na tarefa correspondente, em vez de presumidas.
