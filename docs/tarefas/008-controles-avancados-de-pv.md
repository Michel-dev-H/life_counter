# 008 — Teclado e atalhos configuráveis de PV

- **Status:** Implementado — aguardando revisão
- **Área:** Calculadora de PV
- **Prioridade:** Alta

## Objetivo

Completar os controles de cálculo da arena sem exigir navegação excessiva nem reduzir a legibilidade em partidas com vários participantes.

## Escopo

- Adicionar teclado numérico próprio para inserir um valor e escolher adicionar, subtrair ou definir PV diretamente.
- Disponibilizar ação de dobrar PV na interface.
- Manter atalhos rápidos de dano/recuperação em 100, 500 e 1.000 PV, com valores personalizados disponíveis na calculadora.
- Garantir validação de entrada, números inteiros e apresentação explícita do resultado antes de ações destrutivas quando necessário.
- Manter os controles acessíveis a partir do painel do jogador.

## Critérios de aceite

- [x] Definir PV e dobrar PV geram eventos compatíveis com undo/redo.
- [x] Entrada inválida ou fora do limite não modifica o estado e apresenta erro ao usuário.
- [x] Atalhos podem ser usados com poucos toques e não sobrecarregam layouts compactos.
- [x] Valores e rótulos são acessíveis por leitor de tela e possuem alvos de toque adequados.
- [x] Testes cobrem os novos comandos e limites.

## Decisões

- Atalhos rápidos permanecem fixos em 100, 500 e 1.000 PV, conforme preferência do usuário. Valores personalizados são inseridos sob demanda pela calculadora; não há configuração persistente de presets.
- A calculadora mostra o PV atual e o resultado exato antes de definir PV diretamente. A confirmação aplica a ação somente após validação no servidor.
- O teclado mantém os dígitos localmente durante a entrada para evitar perder toques rápidos; a operação completa é enviada e validada pelo Livewire ao confirmar.

## Implementação e validação

- Cada painel tem acesso à calculadora numérica com operações de adicionar, subtrair e definir PV, além de dobrar PV.
- Definição, adição, subtração e dobra usam o motor existente e registram eventos reversíveis. Valores fora de 0–999.999, decimais, texto inválido, operações desconhecidas e zero em adição/subtração não alteram o duelo e mostram erro.
- Testes focados da arena: 38 passaram. Suíte completa: 115 testes passaram, com 343 assertions.
- Build de produção (`npm run build`) concluído. A compilação exibiu somente o aviso opcional do pacote `fontaine` para otimização de fallback de fontes.
- Verificação no navegador: em 390 px, o modal tem 343 px de largura, alvos de toque com pelo menos 44 px e não há rolagem horizontal; em 1280 px também não há rolagem horizontal. A prévia de definição e o erro ao ultrapassar 999.999 foram conferidos.
- Pint e `git diff --check` passaram. PHPStan continua apontando 24 problemas de tipagem preexistentes no worktree; nenhum é novo nesta tarefa.

**Conclusão:** os critérios desta tarefa foram implementados e validados; aguarda revisão.

## Registro do trabalho, metodologia e pendências

### O que foi feito e como

- Foi criada uma calculadora contextual por jogador com entrada numérica, prévia do PV atual/resultado e ações de adicionar, subtrair ou definir PV; a arena também oferece dobra de PV.
- Os atalhos rápidos permanecem em 100, 500 e 1.000, conforme decisão do usuário; valores personalizados são digitados na calculadora.
- As ações reaproveitam o motor de PV e o histórico existente, validam no servidor e mostram erro sem alterar o estado quando a entrada é inválida.

### Metodologia e regras aplicadas

- Definição destrutiva exige prévia do resultado; entrada é mantida localmente durante a digitação e validada pelo Livewire na confirmação.
- Testes focados cobrem operações, limites e falhas; build, Pint e verificação de layout/tamanho de toque foram usados como evidências separadas.
- Seguir regras do domínio, acessibilidade, estado Livewire e padrões Tailwind existentes; não introduzir dependência ou presets configuráveis sem aprovação.

### Skills e fontes de apoio

- `laravel-best-practices`, `livewire-development`, `tailwindcss-development` e `testing-best-practices`.
- Regras locais de app/testes em `.ai/rules/app.md` e `.ai/rules/tests.md`.

### O que falta

- A implementação e os critérios de aceite estão validados; resta a revisão final indicada pelo status.
- PHPStan do ciclo reportou 24 problemas de tipagem preexistentes no worktree, sem relação com as operações novas.
- Revalidar em Android se futuras alterações tocarem a interação da calculadora ou os alvos de toque nativos.

### Superfícies e reprodução

- Lógica e UI: `app/Livewire/DuelArena.php`, `resources/views/livewire/duel-arena.blade.php`; testes: `tests/Feature/DuelArenaTest.php`.
- Comandos registrados: `php artisan test --compact tests\Feature\DuelArenaTest.php` (38 focados), `php artisan test --compact` (115 testes/343 assertions), `npm run build`, Pint e `git diff --check`.
- PHPStan reportou 24 problemas preexistentes no worktree. A inspeção browser mediu 390 px (modal 343 px, alvos ≥44 px) e 1280 px; também conferiu prévia de definir PV e erro acima do limite.
- Nenhum teste NativePHP desta calculadora foi registrado; não inferir a partir do build web.


## Validação em dispositivo - 2026-11-06

- Calculadora abre com modos Subtrair/Adicionar/Definir e teclado numérico; valor inicial 0.
- Botão Valor abre a calculadora.