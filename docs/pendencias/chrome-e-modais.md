# Chrome e modais

**Escopo:** `App\Layouts\TabsLayout`, `App\Layouts\StackLayout`, `App\Layouts\WallpaperLayout`, `resources/views/native/wallpaper-layer.blade.php` e o padrão de `native:modal`
**Telas afetadas:** todas as 8 (mudança aqui aparece em mais de uma tela)
**Tarefas antigas absorvidas:** 008, 029, 030, 031
**Cobertura atual:** `tests/Feature/NativeListLayoutTest.php` (guarda de layout); nenhum teste de chrome em si
**Status geral:** Implementado — não validado no aparelho

## 1. Contexto e requisito

O chrome é compartilhado: `TabsLayout` dá top bar + abas aos destinos de primeiro nível (Início, Configuração, Aparência) e `StackLayout` dá top bar com voltar às telas empilhadas (Arena, Dados, Moeda, Lembretes, Duelos Recentes). `WallpaperLayout`, base dos dois, pinta o papel de parede do usuário atrás de tudo. Modais (`native:modal`) são o padrão para confirmação e formulário curto.

## 2. Estado atual

### Layouts

- `WallpaperLayout` monta `BackgroundLayer` uma única vez no layout, com `view('native.wallpaper-layer', [url, scrim])` (`app/Layouts/WallpaperLayout.php:21-35`). O docblock registra a intenção: sobreviver à troca de aba e ao push.
- `wallpaper-layer.blade.php` usa `native:column class="absolute inset-0 …"` com opacidade derivada do scrim — sem `pt-safe`/`pb-safe` em lugar algum do app.
- `TabsLayout` define 3 abas: `Início /`, `Partida /duel/new`, `Aparência /appearance/settings` (`app/Layouts/TabsLayout.php:33-41`). A cor ativa vem de `theme('primary', '#35D8FF')`.
- `StackLayout` acrescenta `->back()` e `usesNativeChrome(): true` (`app/Layouts/StackLayout.php:16-27`).
- Título vem de `navTitle()` de cada componente: `Duel Companion`, `Configuração de Duelo`, `Duelo #<id>`, `Duelos Recentes`, `Lançar dados`, `Lançar moeda`, `Lembretes`, `Aparência`.
- `routes/mobile.php` usa dois grupos de chrome: `Route::nativeGroup(TabsLayout::class, …)` para os destinos de aba e `Route::nativeGroup(StackLayout::class, …)` para arena, dados, moeda, lembretes e duelos recentes. O padrão oficial é grupo, não `->layout()` por rota (`routes/mobile.php:31-45`).

### Modais

- `native:modal :visible="$x" @dismiss="fechar"` é o padrão adotado em: calculadora da arena, identidade do jogador, formulário de lembrete, confirmação de remoção de papel de parede.
- Confirmação destrutiva existe **só** em Aparência (remover papel de parede).

### Regra derivada de bug real

- `native:list` **nunca** pode ficar dentro de `native:scroll-view`: o Compose lança `IllegalStateException: Vertically scrollable component was measured with an infinity maximum height constraints`. As listas hoje são `<native:list class="w-full flex-1">` dentro de uma `column h-full`, com o restante do conteúdo fixo. Guarda: `tests/Feature/NativeListLayoutTest.php`.

## 3. Pendências

| ID | Pendência | Origem | Critério de aceite | Status |
|---|---|---|---|---|
| CH-1 | Aba "Partida" aponta para `/duel/new` | 030 | Confirmar a intenção: a aba cria uma nova partida, não retoma a atual. Se for retomar, a rota da aba muda; se for criar, o rótulo deve deixar isso explícito | Pendente |
| CH-2 | Voltar do sistema no `StackLayout` | 030 | Botão/gesto de voltar retorna à tela anterior (arena → ferramenta) e não sai do app nem duplica a pilha | Pendente |
| CH-3 | Safe area não é tratada explicitamente | 029 | Conteúdo e scrim não ficam sob status bar/navigation bar/notch; validar em aparelho com recorte e com barras por gesto | Pendente |
| CH-4 | Regra `native:list` fora de `scroll-view` só existe como teste | observado 2026-11-17 | Manter a guarda e estendê-la a qualquer view nova com lista; documentar no PR/commit quando a regra for tocada | Pendente |
| CH-5 | Exclusões sem confirmação | 017, 021 | Toda ação destrutiva (excluir lembrete, excluir/arquivar duelo) pede confirmação como a remoção de papel de parede | Pendente |
| CH-6 | Teclado cobre campos no fim da tela | observado 2026-11-17 | Com o teclado aberto, o campo em foco e os botões de ação continuam alcançáveis (configuração, lembrete) | Pendente |
| CH-7 | Wallpaper montado uma vez no layout | D22 | Confirmar que trocar o papel de parede em Aparência repinta o fundo das outras telas sem reiniciar | Pendente |
| CH-8 | `@dismiss` dos modais não é validado | observado 2026-11-17 | Fechar por toque fora/voltar executa o mesmo caminho do botão Cancelar e limpa o estado do formulário | Pendente |
| CH-9 | Tema novo pode não repintar o chrome | 022, 023 | `theme('primary')` do `TabBar` e a cor da nav bar acompanham a troca de tema sem reiniciar (ligado a AP-6) | Pendente |
| CH-10 | Nenhum teste para navegação/abas | 030 | Teste cobrindo que cada aba resolve a rota esperada e que as telas empilhadas usam `StackLayout` | Pendente |

## 4. Validação no aparelho

1. Abrir o app → top bar com título e 3 abas; aba ativa destacada.
2. Tocar em cada aba → tela certa, sem empilhar (a aba substitui, não acumula).
3. Entrar na Arena → o chrome vira stack com voltar e título "Duelo #N".
4. Em Dados/Moeda/Lembretes → voltar retorna à arena; voltar de novo sai para a aba de origem.
5. Trocar de tema → top bar e abas repintam sem reiniciar (CH-9).
6. Trocar papel de parede → fundo novo aparece nas outras telas sem reiniciar (CH-7).
7. Aparelho com notch/barra por gesto → nada de conteúdo ou texto sob o recorte (CH-3).
8. Modal da calculadora → toque fora e botão físico de voltar fecham sem alterar PV (CH-8).
9. Teclado aberto nos formulários → campo e botões alcançáveis (CH-6).
10. Abrir todas as telas e confirmar no `logcat` que não há `Vertically scrollable component` (CH-4).

## 5. Regras aplicáveis

- `.ai/rules/scope.md` — sempre.
- `.ai/rules/app.md` — chrome não carrega regra de negócio; layout só compõe.
- `.ai/rules/tests.md` — mudança de navegação/layout compartilhado exige teste.
- `.ai/rules/nativephp-docs.md` — **obrigatório** aqui: layouts, safe area, `native:list` e `native:modal`.

Decisões: D8, D15, D16, D22, D26, D27, D28, D29. Ver `docs/nativephp-refactor/decisions.md`.

## 6. Skills

- `nativephp-mobile-ui` (vendor) — fonte primária para `NavBar`, `Tab`, `TabBar`, `BackgroundLayer`, `modal`, safe area.
- `laravel-best-practices` — layouts e rotas nativas.
- `testing-best-practices` — testes de navegação.
- `tailwindcss-development` — classes utilitárias usadas no `wallpaper-layer` e nas views.

## 7. Arquivos a inspecionar antes de editar

- `app/Layouts/TabsLayout.php`, `StackLayout.php`, `WallpaperLayout.php`
- `resources/views/native/wallpaper-layer.blade.php`
- `routes/mobile.php`
- `tests/Feature/NativeListLayoutTest.php`
- `docs/design/` (os designs mostram chrome e não estão implementados como tela)
- `docs/nativephp-refactor/decisions.md` (D8, D15, D22, D27)

## 8. Registro de execução

| Data | Agente | O que mudou | Comandos | Resultado |
|---|---|---|---|---|
| 2026-11-17 | auditoria | Arquivo criado a partir dos 3 layouts, do `wallpaper-layer` e das rotas | leitura de código | 10 pendências abertas |
| 2026-11-17 | correção | Padrão de lista corrigido nas 4 telas com `native:list`; guarda estática adicionada | `php artisan test --compact tests/Feature/NativeListLayoutTest.php` | verde; validação no device pendente |
