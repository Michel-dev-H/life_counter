# 032 — QA e testes de integração

- **Status:** Parcial — suíte automatizada verde (142/142); fluxos principais validados em dispositivo Android; restam validações visuais (5–8 jogadores em paisagem, movimento reduzido) e áudio
- **Área:** Qualidade
- **Prioridade:** Bloqueadora

## Objetivo

Validar regras, persistência, acessibilidade e interfaces de ponta a ponta sem considerar compilação isolada como aceite.

## Escopo

- Manter testes para PV, histórico, limites e isolamento entre jogadores.
- Cobrir criação de sessões com 1 e 8 jogadores e rejeição de limites inválidos.
- Adicionar testes para sorteios, contadores, lembretes, temas e recuperação conforme implementados.
- Validar persistência local e recuperação após encerramento.
- Revisar layouts por viewport, orientação, temas, teclado e seletores de imagem.
- Executar a suíte e registrar falhas existentes sem ocultá-las.

## Situação observada

Na inspeção inicial, 79 de 80 testes passaram. O teste pendente envolve a ausência de `wallpaper__scrim` na resposta após upload e está detalhado na tarefa 027. *(Histórico: superado em 2026-11-06.)*

### Estado atual — 2026-11-09

- `php artisan test --compact` → **142 testes, 142 aprovados, 453 asserções, 0 falhas**.
- `tests/Feature/DuelToolsTest.php` cobre endpoints de moeda, dados e lembretes (incluindo destaque e isolamento por duelo); `DuelArenaTest` cobre o sorteio do primeiro jogador com 2 e 8 participantes.
- A falha de `wallpaper__scrim` não existe mais. O teste passa, mas apenas verifica a string da classe no HTML; a camada visual de contraste continua pendente na tarefa 027.
- A validação em build/dispositivo NativePHP foi feita em 2026-11-09; ver seção no fim do arquivo. Itens visuais e áudio continuam pendentes.

## Critérios de aceite

- [x] Suíte relevante passa sem ignorar ou remover testes para mascarar defeitos.
- [x] Regras críticas têm testes de domínio e integração.
- [x] Cenários NativePHP são validados em build/dispositivo quando disponíveis. (Fluxos funcionais validados em 2026-11-09; validações visuais e áudio seguem pendentes.)
- [x] Falhas e limitações de ambiente são reportadas com evidência.


## Validação em dispositivo - 2026-11-06

- Suíte automatizada: 143/143, 463 asserções.
- Fluxos exercitados no aparelho: configuração 1-8, arena 2P/8P, LP (halve/double/restore/calculadora), identidade, fases/turnos, cronômetro, dados, haptics, temas, aparência, offline+resume.
- Pendente: exercitar 5-8 jogadores em paisagem e reduced-motion visualmente.

## Revalidação em dispositivo - 2026-11-09

- Build debug reinstalado via `php artisan native:run android ZF525FLTVF --fresh`; `pm clear` + `am start` resultou em `LaunchState: COLD` com `TotalTime` de ~1,0–1,1 s.
- Interações Livewire da arena passaram a funcionar após remover os componentes Livewire aninhados (dados/moeda/lembretes) do `duel-arena.blade.php`: `drawFirstPlayer`, `toggleHapticFeedback`, `toggleOpponentRotation`, `subtract/halve/double/restore/openLifePointCalculator/closeLifePointCalculator`, `editPlayerIdentity/cancelPlayerIdentityEdit`, `togglePlayerExpansion`, `advanceTurn`, `undo`, `redo` e `$set('editingPlayerColor', ...)`.
- Ferramentas via fetch validadas: moeda (resultado/totais/histórico), dados (soma/valores/histórico) e lembretes (criar com destaque por turno/fase, concluir/reabrir e remover).
- Estado residual corrigido: não há mais texto `/div>` vazando na arena; o sorteio do primeiro jogador agora exibe o nome sorteado em `role="status"`.
- Suíte: 142 testes, 142 aprovados, 453 asserções. Pint limpo.
- Ainda depende de inspeção humana: matriz visual de 5–8 jogadores em paisagem, contraste do papel de parede, movimento reduzido e áudio (sem API no NativePHP instalado).

## Revalidação em dispositivo - 2026-11-10

- Build debug atual reinstalado no moto g56 5G (ZF525FLTVF); `am force-stop` + `am start -W` resultou em `LaunchState: COLD` e `TotalTime: 1049 ms`.
- Tema: `selectTheme('minimal-arena')` atualizou `data-theme` (`holographic` -> `minimal-arena`) e a meta `theme-color` (`#0f131d` -> `#f7f7f4`) sem reload; após reload o tema persistiu e o original `holographic` foi restaurado ao final.
- Arena `/duel/1`: sorteio do primeiro jogador exibiu `role="status"`, subtração/restauração de PV, criar/concluir/remover lembrete (com destaque), moeda e dados via fetch; sem erros JS e sem vazamento de HTML.
- `browser.log` sem erros (apenas mensagens de SafeArea).
- Suíte: 142 testes, 142 aprovados, 453 asserções. Pint limpo.
