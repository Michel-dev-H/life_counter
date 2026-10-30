# 008 — Teclado e atalhos configuráveis de PV

- **Status:** Pendente
- **Área:** Calculadora de PV
- **Prioridade:** Alta

## Objetivo

Completar os controles de cálculo da arena sem exigir navegação excessiva nem reduzir a legibilidade em partidas com vários participantes.

## Escopo

- Adicionar teclado numérico próprio para inserir um valor e escolher adicionar, subtrair ou definir PV diretamente.
- Disponibilizar ação de dobrar PV na interface.
- Permitir personalizar ou adaptar atalhos de dano/recuperação, incluindo valores comuns de 100, 500 e 1.000.
- Garantir validação de entrada, números inteiros e apresentação explícita do resultado antes de ações destrutivas quando necessário.
- Manter os controles acessíveis a partir do painel do jogador.

## Critérios de aceite

- [ ] Definir PV e dobrar PV geram eventos compatíveis com undo/redo.
- [ ] Entrada inválida ou fora do limite não modifica o estado e apresenta erro ao usuário.
- [ ] Atalhos podem ser usados com poucos toques e não sobrecarregam layouts compactos.
- [ ] Valores e rótulos são acessíveis por leitor de tela e possuem alvos de toque adequados.
- [ ] Testes cobrem os novos comandos e limites.
