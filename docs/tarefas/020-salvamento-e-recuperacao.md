# 020 — Salvamento e recuperação de partida

- **Status:** Pendente
- **Área:** Persistência
- **Prioridade:** Bloqueadora

## Objetivo

Salvar automaticamente o estado local da partida e permitir retomá-la após fechamento inesperado do aplicativo.

## Escopo

- Persistir jogadores, PV, histórico, fase/turno, cronômetro, contadores, lembretes e layout conforme implementação dos recursos.
- Oferecer continuar partida ou iniciar nova partida quando houver sessão em andamento.
- Exigir confirmação antes de abandonar/reiniciar uma sessão com alterações.
- Recuperar estado após reinício do app e lidar explicitamente com dados incompletos ou migrações.
- Confirmar que a estratégia de persistência funciona offline em build NativePHP.

## Critérios de aceite

- [ ] Uma sessão salva pode ser restaurada com valores e associações consistentes.
- [ ] Fechar o processo durante uma partida não perde operações já confirmadas.
- [ ] Nova partida não sobrescreve sessão recuperável sem confirmação.
- [ ] Falhas de leitura/gravação são informadas; não há fallback silencioso para sucesso.
- [ ] Testes cobrem gravação, leitura e recuperação de estado.
