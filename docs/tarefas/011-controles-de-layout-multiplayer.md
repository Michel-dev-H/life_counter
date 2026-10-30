# 011 — Expandir, reorganizar e orientar painéis

- **Status:** Pendente
- **Área:** Arena responsiva
- **Prioridade:** Média

## Objetivo

Melhorar o uso dos painéis multiplayer em telas pequenas e permitir que os jogadores ajustem a organização da mesa.

## Escopo

- Permitir expandir temporariamente um participante sem perder o estado da arena.
- Permitir alterar a ordem/posição dos participantes e preservar a escolha na sessão.
- Considerar orientação retrato e paisagem, respeitando as limitações comprovadas do NativePHP.
- Manter a rotação do oponente em 2 jogadores e definir sua interação com a expansão.
- Adaptar controles para 5–8 participantes sem reduzir alvos a tamanhos inseguros.

## Critérios de aceite

- [ ] Expandir/recolher não altera dados da partida.
- [ ] Reordenação atualiza posições visualmente sem trocar identidades, PV ou histórico.
- [ ] Preferências de layout sobrevivem à recuperação da mesma partida.
- [ ] Testes ou validação visual cobrem 1, 2, 3–4 e 5–8 jogadores.
