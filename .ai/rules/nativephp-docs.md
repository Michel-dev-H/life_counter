---
paths:
  - 'app/NativeComponents/**/*.php'
  - 'app/Layouts/**/*.php'
  - 'resources/views/native/**/*.blade.php'
  - 'routes/mobile.php'
---

# NativePHP Mobile (EDGE) — regra vigente

> Este arquivo substitui a versão de 2026-11-09. Ela descrevia Livewire em WebView, runtime `classic` e `pt-safe`: **nada disso vale mais**. O histórico superado está no fim; não o siga.

## 1. Arquitetura em vigor

- **EDGE nativo puro.** Não existe WebView, Livewire, Vite, `resources/js` nem bundle web. Toda tela é um `NativeComponent` + uma view Blade com componentes `native:*`.
- **Device-only.** Rotas vivem em `routes/mobile.php` (`Route::native()`), carregadas só onde o NativePHP roda (device, testes, Jump, comandos `native:*`). Não crie rota web paralela.
- **Versões instaladas:** Laravel 13 · PHP 8.5 · `nativephp/mobile` 4.7 · `nativephp/mobile-ui` 0.9 · `nativephp/mobile-camera` 1.1. Confirme com `composer show --direct` antes de usar API de memória.
- **Runtime:** `NATIVEPHP_RUNTIME_MODE=persistent`, `NATIVEPHP_APP_VERSION=DEBUG`. Rollback é trocar para `classic` no `.env` — não é o modo de desenvolvimento.
- **Chrome:** cada família de tela tem seu grupo. Destinos de primeiro nível em `Route::nativeGroup(TabsLayout::class, …)`; telas empilhadas em `Route::nativeGroup(StackLayout::class, …)`. Não misture `->layout()` por rota com grupo — o padrão é grupo. Layouts vivem em `app/Layouts` (não em `app/NativeComponents`, que o `native:validate` exige que sejam telas).
- **Fonte de verdade:** `docs/nativephp-refactor/decisions.md` (D1–D29) e `docs/pendencias/`. Em conflito com anotação antiga de `docs/tarefas/`, vale a decisão.

## 2. Onde consultar antes de inventar markup

1. `vendor/nativephp/mobile-ui/resources/` — componentes, tokens e a skill `nativephp-mobile-ui` (stubs e assinaturas reais da versão instalada).
2. Documentação oficial: https://nativephp.com/docs/mobile/4/ (Layouts, Safe Area, Reactivity, EDGE Components).
3. `php artisan native:validate` — falha se um componente não for válido ou se um `NativeComponent` estiver fora do padrão.
4. `search-docs` do Boost, com a versão instalada.

Componentes já usados no app: `native:column`, `row`, `text`, `button`, `pressable`, `scroll-view`, `list`, `list-item`, `modal`, `outlined-text-input`, `chip`, `slider`, `image`.

## 3. Regras que já causaram defeito (obrigatórias)

### R1 — `native:list` nunca dentro de `native:scroll-view`

O Compose lança `IllegalStateException: Vertically scrollable component was measured with an infinity maximum height constraints`. Uma lista precisa de altura limitada:

```blade
<native:column class="w-full h-full">
    {{-- conteúdo fixo --}}
    <native:list class="w-full flex-1">…</native:list>
</native:column>
```

Nunca aninhe `list` em `scroll-view`. Guarda estática: `tests/Feature/NativeListLayoutTest.php`. Vale para qualquer tela nova.

### R2 — O boot não pode consultar o banco antes do `migrate`

O primeiro boot roda antes das migrações existirem. `Appearance::applyNativeTheme()` consulta `settings`, então **precisa** capturar `Throwable` e manter a paleta de `config/native-ui.php`. Sem esse fallback o app fica preso na tela de loading (D27).

### R3 — Não use `runningInConsole()` para decidir comportamento de device

O vendor lê `PHP_SAPI` internamente (`NativeServiceProvider.php`) e o processo no device também se apresenta como CLI. Guardas por `runningInConsole()` quebram o runtime real. Prefira `try/catch` com fallback (R2) ou um valor de configuração explícito.

### R4 — Tema vem de tokens, não de hex inline

`Appearance::applyNativeTheme()` aplica `Theme::nativeTokens()` (24 tokens, iguais em `light` e `dark`). Nas views use `bg-theme-*`, `text-theme-*`, `border-theme-*`. Não escreva hex solto nem reintroduza `browserColor()`.

### R5 — Wallpaper mora no layout

`WallpaperLayout::backgroundLayer()` monta a `BackgroundLayer` uma vez; a tela não pinta o próprio fundo. Trocar o papel de parede deve refletir nas outras telas sem reiniciar (validar — CH-7).

### R6 — Modal fora do `scroll-view`, sempre com `@dismiss`

```blade
<native:modal :visible="$show" @dismiss="fechar">…</native:modal>
```

`@dismiss` precisa executar o mesmo caminho do botão Cancelar (limpar estado/validação). Ação destrutiva pede confirmação — padrão de referência em `appearance-settings.blade.php`.

### R7 — `#[Poll]` é custo, não default

Só use `#[Poll]` quando outro ator muda o estado (ex.: cronômetro da arena, `#[Poll(1000)]`). Não adicione poll a telas que só mudam por ação local.

### R8 — Um arquivo em `app/NativeComponents` = uma tela

`native:validate` percorre o diretório e exige `NativeComponent` em cada classe. Layout, trait ou helper vai para `app/Layouts`, `app/Support` ou o domínio.

### R9 — Regra de negócio não mora na view

Cálculo de PV, sorteio, limite de dados, gatilho de lembrete: tudo em `app/Models`/`app/`. O componente orquestra; a view apresenta.

## 4. Verificação mínima antes de finalizar

```bash
php artisan native:validate
php artisan route:list --except-vendor
vendor/bin/pint --dirty --format agent
composer test          # config:clear + pint + phpstan + pest
```

Build e instalação no aparelho (`php artisan native:run android`) são feitos pelo usuário em terminal próprio; no harness o processo trava. Build verde não substitui validação em device.

## 5. Histórico superado — não seguir

| Anotação antiga | Realidade desde 2026-11-17 |
|---|---|
| "Livewire em WebView; evitar componentes aninhados por causa de snapshot" | Livewire e WebView removidos; app é EDGE nativo puro (D1) |
| "Runtime `classic` porque `persistent` falha com Livewire" | `persistent` é o modo atual; o problema era o Livewire, não o runtime (D15) |
| "`pt-safe`/`pb-safe`/`safe-area` no Blade compensam o notch" | Nenhuma dessas classes existe no código; safe area é do chrome nativo (validar CH-3) |
| "Ferramentas da arena via `DuelToolsController` + endpoints JSON" | Telas nativas empilhadas (D5, D21) |
| "Versão do app `1.0.0` para evitar reextração" | `NATIVEPHP_APP_VERSION=DEBUG` em desenvolvimento |
| "`Theme::browserColor()` pinta o chrome do WebView" | Removido em 2026-11-19 (AP-9); o chrome usa `nativeTokens` |
