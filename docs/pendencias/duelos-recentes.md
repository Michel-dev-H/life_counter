# Duelos Recentes

**Rota:** `/duels` · **Componente:** `App\NativeComponents\RecentDuels` · **View:** `resources/views/native/recent-duels.blade.php`
**Layout:** `App\Layouts\StackLayout`
**Design Stitch:** **nenhum**
**Tarefas antigas absorvidas:** 020, 021, 030
**Cobertura atual:** `tests/Feature/RecentDuelsTest.php`, `tests/Feature/NativeListLayoutTest.php` (guarda de layout)
**Status geral:** Implementado — não validado além da abertura da tela

## 1. Contexto e requisito

Lista os duelos mais recentes para retomada rápida (030), substituindo a query morta que existia no `HomeDashboard` do legado (D11). Deve servir de porta de entrada para retomar ou limpar partidas (020/021).

## 2. Estado atual

- Consulta: 5 duelos mais recentes, com jogadores ordenados por assento (`app/NativeComponents/RecentDuels.php:19-24`).
- View: estado vazio com texto próprio e, com itens, `<native:list class="w-full flex-1">` dentro de `column h-full` (`resources/views/native/recent-duels.blade.php`).
- Cada linha mostra "Duelo #id" e "nome vs nome", com chevron e `@navigate="/duel/{id}"`.
- Sem ação de excluir, arquivar ou indicar se o duelo está em andamento.

## 3. Pendências

| ID | Pendência | Origem | Critério de aceite | Status |
|---|---|---|---|---|
| DR-1 | Retomar duelo pela lista | 020 | Tocar na linha abre a arena com PV/turno/fase restaurados | Implementado — não validado |
| DR-2 | Excluir ou arquivar duelo | 021 | Ação por item (swipe ou botão) com confirmação; exclusão remove jogadores/eventos em cascata | Pendente |
| DR-3 | Limite fixo de 5 sem "ver mais" | observado 2026-11-17 | Definir: manter 5, paginar ou aumentar o limite com rolagem | Pendente |
| DR-4 | Linha sem estado do duelo | 030 | Mostrar data e se está em andamento/finalizado; hoje só o número e os nomes | Pendente |
| DR-5 | Sem design de referência | observado 2026-11-17 | Decidir com o usuário o desenho da lista antes de acrescentar informação | Pendente |

## 4. Validação no aparelho

1. Sem duelos → mensagem "Nenhum duelo ainda".
2. Com 1 duelo → linha aparece com nomes na ordem dos assentos.
3. Com 6+ duelos → aparece o mais recente; confirmar o limite de 5 (DR-3).
4. Tocar na linha → abre a arena correta (DR-1); voltar retorna à lista.
5. Iniciar um duelo novo e voltar → ele aparece no topo.
6. Lista longa → rola sem derrubar o app (regressão do crash de altura infinita já corrigida em 2026-11-17).

## 5. Regras aplicáveis

- `.ai/rules/scope.md` — sempre.
- `.ai/rules/app.md` — consulta/ordenação no componente; view não decide.
- `.ai/rules/tests.md` — cenário novo de retomada/exclusão exige teste.
- `.ai/rules/nativephp-docs.md` — **regra de lista**: `native:list` nunca dentro de `native:scroll-view`.

Decisões: D11, D17, D27. Ver `docs/nativephp-refactor/decisions.md`.

## 6. Skills

- `laravel-best-practices` — Eloquent e escopo de consulta.
- `testing-best-practices` — factories de duelo/jogadores.
- `nativephp-mobile-ui` (vendor) — `list`, `list-item`, swipe actions.
- `tailwindcss-development` — classes utilitárias da view.

## 7. Arquivos a inspecionar antes de editar

- `app/NativeComponents/RecentDuels.php`
- `resources/views/native/recent-duels.blade.php`
- `app/Models/Duel.php` (relação `duelPlayers`, estados)
- `tests/Feature/RecentDuelsTest.php`, `tests/Feature/NativeListLayoutTest.php`
- `docs/tarefas/020-…`, `docs/tarefas/021-…`

## 8. Registro de execução

| Data | Agente | O que mudou | Comandos | Resultado |
|---|---|---|---|---|
| 2026-11-17 | auditoria | Arquivo criado a partir do código atual e das tarefas 020/021/030 | leitura de código | 5 pendências abertas |
| 2026-11-17 | correção | `native:list` retirado de dentro do `scroll-view` (crash de altura infinita no Compose) | `php artisan test --compact tests/Feature/NativeListLayoutTest.php` | verde; precisa novo build para validar no device |
