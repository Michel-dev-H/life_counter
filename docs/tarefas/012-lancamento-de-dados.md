# 012 — Lançamento de dados

- **Status:** Implementado e validado no navegador e em dispositivo Android — modal de dados via fetch; componente DuelDice permanece coberto por testes
- **Área:** Sorteios
- **Prioridade:** Média

## Objetivo

Oferecer lançamento rápido de um ou vários dados tradicionais de seis faces.

## Escopo

- Gerar resultados de 1 a 6 usando fonte aleatória adequada à plataforma.
- Permitir escolher quantidade de dados dentro de limite seguro definido para a interface.
- Mostrar cada resultado e, opcionalmente, a soma.
- Registrar resultados recentes na sessão e permitir repetir rapidamente.
- Fazer a animação representar o resultado já sorteado, sem afetar a aleatoriedade.

## Critérios de aceite

- [x] Quantidade de resultados corresponde à quantidade solicitada.
- [x] Todos os resultados pertencem ao intervalo inclusivo de 1 a 6.
- [x] Soma é correta e opcional.
- [x] Histórico está associado à partida e permite consultar resultados anteriores.
- [x] Testes cobrem limites, formato dos resultados e registro de histórico.

## Validação

- Testes focados: `tests/Feature/DuelDiceTest.php` — 10 testes e 51 assertions.
- Suíte completa: 143 testes e 463 assertions.
- Build web, PHPStan focado, Pint e `git diff --check` concluídos sem erros.
- Navegador: fluxo de dois dados, resultados, soma, repetição, histórico e ocultação da soma verificados em 320×720 e 390×844; também verificado em desktop a 1440×844. Sem overflow horizontal; ações empilhadas em telas estreitas.
- Android: o build debug NativePHP foi compilado e instalado; as telas inicial e de configuração abriram. A interação no emulador gerou ANR do app/Sistema UI antes de chegar à arena, então o lançamento de dados no Android ainda não foi confirmado.

## Registro do trabalho, metodologia e pendências

### O que foi feito e como

- Um componente Livewire integrado à arena gera de 1 a 10 D6 no servidor usando `random_int(1, 6)`, persiste os resultados por duelo, calcula a soma e permite repetir a quantidade do último lançamento.
- O modal mostra resultados e até dez lançamentos anteriores. A animação apenas revela valores já sorteados e respeita movimento reduzido.
- No navegador foram conferidos lançamento, repetição, histórico, soma opcional e ausência de overflow a 320×720, 390×844 e 1440×844. Em telas estreitas as ações ficam empilhadas.

### Metodologia e regras aplicadas

- TDD: os testes de integração foram escritos primeiro e falharam antes de existir o componente; após a implementação, foram repetidos junto à suíte.
- Limites são validados no servidor além dos controles da UI; histórico fica associado ao duelo e resultados persistidos são a fonte da apresentação/animação.
- Seguir `.ai/rules/app.md` e `.ai/rules/tests.md`, padrões Laravel/Livewire existentes e validar browser, build e emulador sem alterar partidas reais.

### Skills e fontes de apoio

- `laravel-best-practices`, `livewire-development`, `tailwindcss-development` e `testing-best-practices`.
- `vscode-laravel-artisan` para o fluxo de build/run NativePHP via Artisan.
- Ferramentas browser/Playwright e NativePHP/ADB para a validação manual. O primeiro lançamento manual usou apenas o duelo temporário `DiceQA`, removido depois.

### O que falta

- *(Superado em 2026-11-09: a validação do lançador na arena foi repetida no aparelho físico e passou; ver seção de atualização no fim do arquivo.)* Repetir a validação do lançador dentro da arena em Android quando o emulador estiver responsivo. O build compilou, mas ocorreram ANRs antes de se alcançar a arena; o fluxo Android permaneceu pendente naquela rodada.
- *(Superado em 2026-11-09.)* Não marcar a tarefa como integralmente validada em dispositivo até essa etapa ser concluída.

### Superfícies, comandos e limite da evidência Android

- Implementação: `app/Livewire/DuelDice.php`, `app/Models/DiceRoll.php`, relação em `app/Models/Duel.php`, migração `database/migrations/2026_11_04_101205_create_dice_rolls_table.php`, `resources/views/livewire/duel-dice.blade.php` e integração em `resources/views/livewire/duel-arena.blade.php`.
- Testes: `tests/Feature/DuelDiceTest.php` (10 testes/51 assertions). Referência: `php artisan test --compact tests\Feature\DuelDiceTest.php`; suíte completa registrada em 143 testes/463 assertions.
- Verificações registradas: PHPStan focado, Pint, `npm run build` e `git diff --check` passaram. Browser: 320×720, 390×844 e 1440×844, incluindo rolamento/repetição/soma/histórico; não houve overflow horizontal.
- Android: houve build debug, instalação e abertura da tela inicial/configuração, mas ocorreram ANRs antes de chegar à arena. Portanto nem o lançador nem a migração/uso da tabela foram aceitos no dispositivo Android.
- Histórico manual foi testado em duelo temporário `DiceQA` ID 15 e removido após confirmação; não usar partidas reais. O AVD era `emulator-5554`; comandos NativePHP/ADB e dependências do SDK estão detalhados na tarefa 003.


## Validação em dispositivo - 2026-11-06

- Modal Lançar dados abre, rola 1 a 5 dados, mostra soma e histórico (até 10 lançamentos) com timestamp.
- Funciona offline.

## Atualização e validação em dispositivo - 2026-11-09

- O modal exibido na arena passou a ser HTML/JS simples dentro de `duel-arena.blade.php`, com `fetch` para `POST /duel/{duel}/dice-roll` e `GET /duel/{duel}/dice-rolls` (implementados em `DuelToolsController`).
- O contador é limitado no servidor a 1–10; os resultados usam `random_int(1, 6)` e ficam persistidos em `dice_rolls`.
- O componente `DuelDice` e `tests/Feature/DuelDiceTest.php` continuam no repositório cobrindo a implementação Livewire, embora a arena use o modal via fetch.
- Validação no moto g56 5G (ZF525FLTVF), após a remoção dos componentes Livewire aninhados: abrir modal, rolar, ver soma/valores e histórico persistido (ex.: "Soma: 4 — 4" e "1 dado(s): 4 = 4"). Sem erros no `browser.log`.
- A pendência de ANR no emulador registrada em 2026-11-06 não se reproduziu no aparelho físico.
