# Início

**Rota:** `/` · **Componente:** `App\NativeComponents\Home` · **View:** `resources/views/native/home.blade.php`
**Layout:** `App\Layouts\TabsLayout` (top bar + abas Início/Partida/Aparência)
**Design Stitch:** **nenhum** — o app não tem referência visual para esta tela
**Tarefas antigas absorvidas:** 020, 029, 030, 031
**Cobertura atual:** `tests/Feature/HomeTest.php`
**Status geral:** Implementado — não validado no aparelho

## 1. Contexto e requisito

Hub de entrada do companion. Deve dar acesso em um toque a: começar um duelo, voltar a um duelo recente e ajustar a aparência (030). É o destino da aba "Início" (`TabsLayout::tabBar()`).

## 2. Estado atual

- `Home::render()` só devolve a view, sem consultar dados (`app/NativeComponents/Home.php:15`).
- Três botões com `@navigate`: `/duel/new`, `/duels`, `/appearance/settings` (`resources/views/native/home.blade.php:7-29`).
- Raiz é `<native:column class="w-full h-full items-center justify-center …">` **sem** `scroll-view` (`:1`).
- Nenhum conteúdo além dos três botões — correto pela regra YAGNI, já que não há design de referência.
- Título da tela: "Duel Companion" (`Home::navTitle()`).

## 3. Pendências

| ID | Pendência | Origem | Critério de aceite | Status |
|---|---|---|---|---|
| IN-1 | Retomar partida em andamento em um toque | 020, 030 | Com duelo ativo, o Início mostra "Continuar duelo" abrindo `/duel/{id}`; sem duelo ativo, o botão não aparece | Pendente |
| IN-2 | Estado vazio / primeiro uso | 031 | Sem nenhum duelo, a tela orienta o primeiro passo sem criar tela nova | Pendente |
| IN-3 | Conteúdo cortado com fonte grande ou tela baixa | 029 | Com fonte do sistema em 200%, os três botões continuam alcançáveis | Pendente |
| IN-4 | Ausência de design de referência | observado 2026-11-17 | Decidir com o usuário se o Início ganha tela no Stitch antes de receber conteúdo novo | Pendente |

IN-4 não pede implementação: sem design aprovado, vale a regra "nada além do que as telas do Stitch mostram" (`.ai/rules/app.md`).

## 4. Validação no aparelho

1. Abrir o app do zero → Início aparece com as três ações.
2. Tocar em cada botão → chega em Configuração, Duelos Recentes e Aparência respectivamente.
3. Voltar do sistema a partir de qualquer aba → mantém a aba anterior.
4. Fonte do sistema em 200% e em 100% → nada cortado.
5. Com wallpaper/tema escuro → texto legível sobre o scrim.

## 5. Regras aplicáveis

Leia antes de editar (`.ai/rules/index.md` mapeia os globs):

- `.ai/rules/scope.md` — sempre: menor diff, nada além do pedido.
- `.ai/rules/app.md` — ao tocar `app/**`: YAGNI e "as telas do Stitch são requisito".
- `.ai/rules/tests.md` — ao tocar `tests/**`: teste nasce antes.
- `.ai/rules/nativephp-docs.md` — arquitetura EDGE vigente.

Decisões que valem aqui: D1 (device-only), D8 (abas), D15 (`persistent`), D17 (testes), D26 (limpeza), D27 (boot do tema). Ver `docs/nativephp-refactor/decisions.md`.

## 6. Skills

- `laravel-best-practices` — componente/domínio.
- `testing-best-practices` — cenários e dados de teste.
- `nativephp-mobile-ui` (vendor) — conferir elemento antes de usar markup novo.
- `tailwindcss-development` — só ao mexer nas classes utilitárias da view.

Registre no §8 apenas as que foram realmente usadas.

## 7. Arquivos a inspecionar antes de editar

- `app/NativeComponents/Home.php`
- `resources/views/native/home.blade.php`
- `app/Layouts/TabsLayout.php` e `app/Layouts/WallpaperLayout.php`
- `tests/Feature/HomeTest.php`
- `docs/design/` (não há design de Início — confirmar antes de inventar)

## 8. Registro de execução

| Data | Agente | O que mudou | Comandos | Resultado |
|---|---|---|---|---|
| 2026-11-17 | auditoria | Arquivo criado a partir do código atual e das tarefas 020/029/030/031 | leitura de código | 4 pendências abertas |
