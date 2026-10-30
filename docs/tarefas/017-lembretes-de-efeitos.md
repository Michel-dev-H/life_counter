# 017 — Lembretes de efeitos e duração

- **Status:** Pendente
- **Área:** Lembretes
- **Prioridade:** Alta

## Objetivo

Permitir que os jogadores registrem efeitos manualmente e recebam lembretes sem automatizar regras de cartas.

## Escopo

- Criar e editar lembrete com nome da carta, descrição curta, jogador responsável, turno/fase, duração e condição para remoção.
- Associar lembretes à sessão e opcionalmente a uma referência de carta ou posição.
- Destacar lembretes quando a fase/turno configurados forem alcançados.
- Permitir concluir/remover com confirmação apropriada quando a informação puder ser perdida.
- Não executar efeitos nem presumir interpretação de texto de carta.

## Critérios de aceite

- [ ] Lembretes podem ser criados, editados, concluídos e removidos.
- [ ] Turno e fase são associações opcionais e usam o estado da sessão correta.
- [ ] Lembretes importantes não desaparecem silenciosamente.
- [ ] Testes cobrem duração, destaque e persistência.
