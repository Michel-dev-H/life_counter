# 011 — Expandir, reorganizar e orientar painéis

- **Status:** Implementado e validado no navegador; expansão 2P também verificada no NativePHP Android
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

- [x] Expandir/recolher não altera dados da partida.
- [x] Reordenação atualiza posições visualmente sem trocar identidades, PV ou histórico.
- [x] Preferências de layout sobrevivem à recuperação da mesma partida.
- [x] Testes ou validação visual cobrem 1, 2, 3–4 e 5–8 jogadores.

## Implementação e validação

- Cada painel pode ser expandido temporariamente. A expansão não é persistida e não altera PV nem histórico.
- Em partidas com mais de dois participantes, os controles movem os painéis para cima/baixo e persistem a ordem por duelo. A ordem inválida volta à ordem dos assentos.
- Em partidas 2P, a ordem permanece fixa para manter o oponente acima do jogador da casa; a rotação de 180 graus continua ativa durante a expansão.
- Testes focados: `DuelArenaTest.php` e `DuelTurnTest.php` — 78 testes passaram, cobrindo expansão, reordenação, persistência, limites e integridade dos dados.
- A validação visual cobriu 1–8 jogadores em 390 × 844, 844 × 390 e 1280 × 800. Não houve overflow horizontal; os controles observados tiveram alvos de pelo menos 44 px.
- No emulador NativePHP Android, o painel do oponente foi expandido e recolhido mantendo a rotação e os PV inalterados. A validação Android de expansão foi feita em 2P; reordenação e layouts de 3–8 foram validados em navegador.

**Resultado:** critérios da tarefa atendidos. Em 2P, a reordenação é intencionalmente desabilitada para preservar a posição do oponente no topo.

## Registro do trabalho, metodologia e pendências

### O que foi feito e como

- A expansão é temporária e não altera PV/histórico; em partidas com mais de dois participantes, a ordem é persistida por duelo e validada antes de renderizar.
- No 2P, a ordenação fica fixa para manter o oponente no painel superior; a rotação de 180° permanece ao expandir/recolher.
- Testes cobriram expansão, reordenação, persistência e integridade; navegador cobriu 1–8 jogadores e Android cobriu expansão em partida 2P.

### Metodologia e regras aplicadas

- Separar estado visual temporário da persistência da partida; validar que comandos de layout não trocam identidade, PV ou histórico.
- Seguir a solicitação explícita do usuário para posição do oponente em cima; quando uma opção conflita com essa regra, desabilitar a reordenação 2P em vez de alterar a posição.
- Testes automatizados, inspeção de múltiplas viewports e emulador foram usados como evidências distintas.

### Skills e fontes de apoio

- `laravel-best-practices`, `livewire-development`, `tailwindcss-development` e `testing-best-practices`.
- Ferramentas de browser e NativePHP/ADB para a validação visual/documentada.

### O que falta

- Não há pendência de aceite registrada nos critérios atuais.
- Reordenação e layouts de 3–8 foram validados no navegador, não no Android; validar no dispositivo se o escopo mobile nativo for ampliado.
- Mudanças futuras devem preservar a exceção deliberada de ordenação fixa e rotação no modo 2P.

### Superfícies, evidências e dados de QA

- Estado/layout: `app/Livewire/DuelArena.php`, `resources/views/livewire/duel-arena.blade.php`; ícones: `resources/views/components/icon.blade.php`; regressões: `tests/Feature/DuelArenaTest.php` e `tests/Feature/DuelTurnTest.php`.
- Comando focado registrado: `php artisan test --compact tests\Feature\DuelArenaTest.php tests\Feature\DuelTurnTest.php` — 78 testes passaram antes da correção final da faixa duplicada da tarefa 010; a correção foi então coberta pelo teste de regressão de 1 e 3 jogadores. Não usar a contagem de 78 como prova de que a correção posterior foi incluída naquele run.
- Browser: 1–8 em 390×844, 844×390 e 1280×800, sem overflow horizontal; alvos dos controles ≥44 px. Android NativePHP cobriu apenas expansão/recolhimento 2P, mantendo rotação e PV.
- Duelos de QA `LayoutQA` IDs 7–14 foram criados para a matriz 1–8 e removidos após a validação; nenhum duelo real deve ser limpo como parte da repetição.
