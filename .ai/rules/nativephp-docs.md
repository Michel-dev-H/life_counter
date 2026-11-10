# NativePHP Documentation Reference

## Fonte principal

A documentação oficial do NativePHP Mobile (versão 4.x) é a referência principal para resolver problemas de plataforma, componentes, safe area, layout, e comportamento do WebView.

- **URL base:** https://nativephp.com/docs/mobile/4/
- **Páginas-chave consultadas:**
  - Nested Components: https://nativephp.com/docs/mobile/4/the-basics/nested-components
  - Safe Area: https://nativephp.com/docs/mobile/4/the-basics/safe-area
  - Layouts: https://nativephp.com/docs/mobile/4/the-basics/layouts
  - Reactivity: https://nativephp.com/docs/mobile/4/digging-deeper/reactivity
  - Modal (EDGE): https://nativephp.com/docs/mobile/4/edge-components/modal

## Observações importantes

- A documentação trata de componentes **nativos** (EDGE), não de Livewire. O app usa Livewire em WebView.
- Causa raiz confirmada (2026-11-09) do erro "Snapshot missing on Livewire component": componentes Livewire **aninhados** dentro da arena (dados/moeda/lembretes). Ao re-renderizar o componente pai, os filhos perdiam o snapshot e todas as ações `wire:click` falhavam no WebView.
- Correção aplicada: remover os componentes Livewire aninhados de `duel-arena.blade.php` e mover as ferramentas (moeda/dados/lembretes) para HTML/JS simples + endpoints JSON (`DuelToolsController`). Depois disso, as ações `wire:click` da arena funcionam no dispositivo.
- A injeção de SafeArea **não** era a causa: a hipótese anterior foi descartada.
- O `safe-area` class e `pt-safe`/`pb-safe` são usados no layout Blade para compensar notch/status bar.

## Decisões registradas

- Modo runtime: `classic` (persistent falha com Livewire no WebView).
- Versão do app: `1.0.0` (evita reextração a cada cold start).
- Componentes Livewire aninhados: evitar dentro da arena; usar HTML/JS simples + fetch ou renderizar tudo no componente pai.
- Armazenamento de wallpaper: disco `public`.
- Scrim: opacidade configurável 0-80%.
- Orientação: portrait por ora; vontade de suportar landscape registrada.
- Keep-awake: Wake Lock API (best-effort) + fallback documentado.
