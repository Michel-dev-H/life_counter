# 018 — Fases, turnos e cronômetro

- **Status:** Revisado — melhorias aprovadas aguardando implementação
- **Área:** Acompanhamento de partida
- **Prioridade:** Média

## Implementado no repositório

- Fases Draw, Standby, Main 1, Battle, Main 2 e End.
- Avanço de turno/rodada e identificação do assento ativo.
- Cronômetro de partida com iniciar, pausar, reiniciar e limite de 40 minutos.
- Interface da faixa de fases e testes de domínio/Livewire.

## Revisão pendente

- [ ] Confirmar rótulos e comportamento sem sugerir automação de regras.
- [ ] Validar estado do cronômetro após recarregar e após encerrar o app.
- [ ] Confirmar UX opcional para quem não utiliza fases/turnos.
- [ ] Revisar associação da fase atual e turno com lembretes da tarefa 017.

## Limite

Avanço automático de fases ou execução de efeitos não faz parte deste recurso.

## Resultado da revisão

- Teste executado: `php artisan test --compact tests\Feature\DuelTurnTest.php` — 22 testes passaram.
- A fase é selecionável manualmente; avançar turno atualiza turno, rodada e assento ativo. O cronômetro tem iniciar, pausar, retomar, reiniciar e limite de 40 minutos.
- O estado do cronômetro é baseado em `timer_elapsed_seconds` e `timer_started_at`, persistidos no duelo; os testes cobrem contagem, pausa/retomada, fim, reinício e formatação.
- A interface atual mantém a faixa de fase/turno sempre visível. O cronômetro é um `setInterval` local dentro de `wire:ignore`; não há sincronização explícita da exibição quando o app retorna do segundo plano, então o valor visual pode ficar atrasado mesmo com o horário persistido correto.
- A fase atual é mantida ao avançar turno e a identificação da vez usa “Sua vez / Vez do Oponente” para os dois primeiros assentos. Esses comportamentos foram revistos com decisões explícitas abaixo.
- Ainda não há preferência por partida para ocultar o acompanhamento nem registro desses eventos no histórico unificado. O registro de eventos fica para a tarefa 019.
- Não houve validação em build NativePHP/dispositivo Android. O comportamento de suspensão e retorno precisa ser validado nessa plataforma.

## Decisões acordadas

- Ao avançar turno, a fase volta para Draw; não há avanço automático entre fases.
- Oferecer um controle combinado para mostrar/ocultar fases e turnos. Ocultar não apaga o estado; enquanto oculto, o estado não avança sozinho.
- Persistir essa preferência por partida.
- Manter Undo/Redo acessíveis mesmo quando a faixa de fases/turnos estiver oculta.
- O cronômetro continua contando no segundo plano e sua exibição deve ser sincronizada ao retornar ao app.
- Identificar o participante ativo como “Vez de [nome]”, inclusive para os dois primeiros assentos.

## Próximos passos

- [ ] Resetar a fase para Draw ao avançar o turno.
- [ ] Implementar controle combinado de exibição por partida, mantendo o estado enquanto oculto.
- [ ] Garantir acesso a Undo/Redo fora da faixa ocultável.
- [ ] Exibir o nome do participante ativo na indicação da vez.
- [ ] Recalcular a exibição do cronômetro a partir do tempo persistido ao retornar do segundo plano.
- [ ] Adicionar testes para reset da fase, preferência por partida, estado preservado ao ocultar e rótulo da vez.
- [ ] Validar cronômetro em build NativePHP Android, incluindo suspensão, retorno e encerramento do app.

**Conclusão:** as funções básicas estão implementadas e os testes focados passam. A tarefa permanece aberta até as decisões aprovadas serem implementadas e verificadas em Android.
