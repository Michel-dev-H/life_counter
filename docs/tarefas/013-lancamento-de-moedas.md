# 013 — Lançamento de moedas

- **Status:** Implementado e validado em dispositivo — modal Cara/Coroa com totais, histórico e persistência via endpoints JSON
- **Área:** Sorteios
- **Prioridade:** Média

## Objetivo

Permitir um ou vários lançamentos de moeda com contagem e histórico fáceis de consultar.

## Escopo

- Gerar somente Cara ou Coroa com fonte aleatória apropriada.
- Permitir lançamentos múltiplos e exibir cada resultado.
- Exibir totais de caras e coroas.
- Registrar histórico e permitir repetir rapidamente.
- Usar animação opcional que não determine nem altere o resultado.

## Critérios de aceite

- [x] Nenhum resultado fora de Cara/Coroa é produzido.
- [x] Totais correspondem aos lançamentos exibidos.
- [x] Lançamentos persistem no histórico da sessão.
- [ ] Animação pode ser desativada e não atrasa a consulta do resultado. (Não há animação: o resultado é exibido diretamente.)
- [x] Testes cobrem múltiplas jogadas, contagem e histórico.

## Implementação aplicada - 2026-11-06 (Fase 4)

- Componente DuelCoinFlip com botão "Lançar moeda", resultado Cara/Coroa, totais e histórico (até 20 lançamentos).
- Modelo CoinFlip + migration coin_flips.
- Resultado gerado com `random_int` (fonte apropriada).
- Animação não implementada (resultado exibido diretamente, sem atraso).

## Nota de dispositivo - 2026-11-09 (histórico, superada)

- Modal renderiza e abre corretamente no dispositivo.
- Ação de lançar não atualizava a UI em tempo real (erro 'Snapshot missing' no WebView NativePHP).
- Causa raiz atribuída na época à injeção de SafeArea; a causa confirmada depois foi a presença de componentes Livewire aninhados na arena (ver atualização abaixo).
- Contornos tentados: inlining no DuelArena e remoção de wire:key não resolveram; a solução final foi remover os componentes aninhados e manter os modais de ferramentas fora do ciclo do Livewire.
- Dados persistiam no banco (CoinFlip); a UI não refletia em tempo real naquela rodada.

## Atualização e validação em dispositivo - 2026-11-09

- O modal de moeda passou a ser HTML/JS simples dentro de `duel-arena.blade.php`, usando `fetch` para `POST /duel/{duel}/coin-flip` e `GET /duel/{duel}/coin-flips`.
- Endpoints implementados em `App\Http\Controllers\DuelToolsController` (`flipCoin`, `coinFlipHistory`) e registrados em `routes/web.php`.
- Persistência em `coin_flips`; totais de Cara/Coroa e histórico de até 20 lançamentos retornados pela API.
- O componente Livewire antigo (`DuelCoinFlip`) foi removido por não ser mais renderizado.
- Validação no moto g56 5G (ZF525FLTVF): abrir modal, lançar, atualizar resultado/totais e histórico (ex.: "Cara — Cara: 1, Coroa: 0, Total: 1" e item no histórico com horário). Sem erros no `browser.log`.
- Cobertura: `tests/Feature/DuelToolsTest.php` cobre resultado, totais, histórico e persistência; `php artisan test --compact` passou com 142 testes/453 asserções na rodada final.
