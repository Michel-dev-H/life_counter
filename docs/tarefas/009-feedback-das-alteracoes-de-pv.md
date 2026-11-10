# 009 — Feedback visual, tátil e sonoro de PV

- **Status:** Implementado parcialmente — áudio e validação tátil no Android pendentes
- **Área:** Interação e feedback
- **Prioridade:** Média

## Objetivo

Dar retorno rápido e inequívoco para alterações de PV sem atrasar as jogadas nem comprometer a precisão.

## Escopo

- Exibir transição numérica curta entre valor anterior e novo valor.
- Diferenciar dano e recuperação visualmente com feedback consistente.
- Implementar preferências opcionais de vibração e efeitos sonoros apenas após validar APIs NativePHP.
- Respeitar preferência de movimento reduzido e permitir desativar efeitos.
- Não usar animação como fonte do cálculo: o resultado deve ser determinado e salvo antes da apresentação.

## Critérios de aceite

- [x] Valor armazenado e exibido final permanecem exatos, em inteiros.
- [x] A animação não bloqueia ações seguintes e respeita acessibilidade.
- [ ] Som e vibração podem ser desativados e não são obrigatórios para compreender o resultado.
- [x] Falha de integração nativa é tratada explicitamente, sem sucesso falso.

## Implementação

- Alterações efetivas de PV disparam um evento visual somente depois de persistidas. Ele cobre as ações da arena, incluindo desfazer/refazer, informa jogador, valores anterior/final e tipo de mudança; operações sem efeito não disparam feedback.
- O valor permanece inteiro e exato. A animação CSS dura 420 ms, distingue dano de recuperação por cor e não bloqueia novas ações. O valor tem anúncio `aria-live`; `prefers-reduced-motion` desativa a animação.
- A opção acessível de vibração é desligada por padrão e persiste em `duel.feedback.haptics`. Ao ativá-la, o app testa `Device::vibrate()`. Se a API falhar durante esse teste ou uma alteração de PV, a preferência é desativada e um erro explícito é apresentado; a alteração de PV já persistida não é revertida.

## Pendências e validação

- A API `Native\Mobile\Facades\Device::vibrate(): bool` existe no NativePHP Mobile 4.7.0, mas a vibração ainda não foi exercitada no emulador/dispositivo Android.
- Não foi localizada uma API integrada de reprodução de som no NativePHP Mobile instalado. Não foi adicionado som simulado; áudio permanece pendente até haver uma API/plugin validado.
- Testes focados da arena: 46 passaram, com 194 assertions. Suíte completa: 123 passaram, com 383 assertions.
- A validação no navegador confirmou a animação e as cores para dano/recuperação; com movimento reduzido, a animação calculada é `none`.
- `npm run build` e Pint passaram. PHPStan passou para `DuelArena`; ao incluir `Duel.php`, ainda reporta quatro problemas de tipagem preexistentes nessa classe, fora do fluxo de feedback.

**Conclusão:** feedback visual e tátil foram implementados, mas a tarefa permanece parcial até validar a vibração no Android e resolver a estratégia de áudio.

## Registro do trabalho, metodologia e pendências

### O que foi feito e como

- Depois de persistir uma mudança efetiva de PV, a arena emite dados de jogador, valor anterior/final e tipo de alteração; o valor gravado não depende da animação.
- A interface diferencia dano e recuperação, anuncia o valor por acessibilidade e respeita `prefers-reduced-motion`.
- A preferência de vibração é desligada por padrão e persistida. A API `Device::vibrate()` é testada ao ativar; uma indisponibilidade desliga a opção e mostra erro sem reverter uma alteração de PV já persistida.
- Operações sem mudança não emitem feedback.

### Metodologia e regras aplicadas

- Testes comportamentais foram escritos antes da implementação e falharam pelos motivos esperados; após a mudança, foram usados testes focados, build e inspeção visual no navegador.
- A compatibilidade nativa foi conferida contra a versão instalada antes de usar a API. Não simular som nem tratar uma API indisponível como sucesso.
- Preservar exatidão do domínio, anunciar erros, manter feedback não bloqueante e respeitar movimento reduzido.

### Skills e fontes de apoio

- `laravel-best-practices`, `livewire-development`, `tailwindcss-development` e `testing-best-practices`.
- Documentação/API instalada do NativePHP Mobile 4.7.0 para `Device::vibrate()`.
- Não foi confirmada uma skill NativePHP específica neste ciclo.

### O que falta

- Exercitar a vibração em dispositivo/emulador Android; a validação tátil real não foi feita.
- Definir e validar uma API/plugin de áudio para NativePHP antes de implementar som.
- Reexecutar a validação Android e a suíte focada após mudanças futuras no feedback.
- Manter a tarefa parcial até resolver ou formalmente retirar do escopo áudio e validação tátil.

### Superfícies e reprodução

- Componente e evento: `app/Livewire/DuelArena.php`; acessibilidade/controles/visual: `resources/views/livewire/duel-arena.blade.php`; animação: `resources/css/app.css`; testes: `tests/Feature/DuelArenaTest.php`.
- Comando focado: `php artisan test --compact tests\Feature\DuelArenaTest.php` — 46 testes/194 assertions no resultado registrado; suíte completa `php artisan test --compact` — 123 testes/383 assertions.
- `npm run build` e Pint passaram. PHPStan passou para `DuelArena`; com `Duel.php`, apontou quatro problemas de tipagem preexistentes naquela classe.
- Browser confirmou dano/recuperação e `prefers-reduced-motion`; isso não valida vibração física. A API NativePHP foi identificada como `Device::vibrate(): bool`, porém não foi exercitada em Android. Não há API de som integrada confirmada; áudio segue pendente.


## Validação em dispositivo - 2026-11-06

- Toggle de vibração funciona e persiste após reload (Vibração ativada <-> Vibração desativada).
- Som: sem API de áudio no NativePHP 4.7; permanece bloqueado.
- Keep-awake/orientação: não implementados (tarefa 028).