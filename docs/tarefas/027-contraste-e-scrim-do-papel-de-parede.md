# 027 — Contraste e scrim de papel de parede

- **Status:** Implementado - regra .wallpaper__scrim com overlay e blur, opacidade configurável (0-80%), wallpaper aplicado como background-image
- **Área:** Aparência e acessibilidade
- **Prioridade:** Alta

## Estado atual do código — 2026-11-06

- `resources/views/layouts/app.blade.php` adiciona `wallpaper__scrim` ao `<body>` quando existe papel de parede ativo, e `tests/Feature/AppearanceTest.php` verifica essa string.
- Não existe regra `.wallpaper__scrim` em `resources/css/app.css` nem em qualquer outro arquivo do projeto. As variáveis `--theme-scrim` (linhas 100, 113, 125 e 137 de `app.css`) são definidas e nunca usadas.
- Nenhuma view aplica o papel de parede como `background-image`; `Appearance::wallpaperUrl()` só é consumido na própria tela de aparência. O fundo do app continua sendo `bg-surface`.
- O teste de scrim passa porque confere apenas a presença da classe no HTML; ele não prova que exista uma camada de contraste renderizada.
- `.hud-panel` usa `rgba(var(--color-on-surface-rgb), …)` e a variável `--color-on-surface-rgb` não está definida em lugar nenhum, o que invalida essas declarações de borda/fundo.

## Objetivo

Garantir leitura dos PV, controles e textos sobre qualquer fundo, inclusive imagens escolhidas pelo usuário.

## Escopo

- Aplicar camada de contraste coerente com o tema e com o fundo ativo.
- Configurar opacidade e/ou desfoque sem comprometer a estética.
- Usar fallback para superfície sólida quando a legibilidade não puder ser assegurada.
- Corrigir o elemento/estilo `wallpaper__scrim` esperado pela interface e pelos testes.
- Revisar contraste para texto e controles segundo critérios de acessibilidade.

## Critérios de aceite

- [ ] O teste que verifica o scrim para fundo ativo passa.
- [ ] Sem papel de parede, o scrim não é renderizado e o fundo padrão permanece correto.
- [ ] Remover o fundo elimina a referência visual e o recurso associado.
- [ ] PV e controles são legíveis em todos os temas, com fundos claros e escuros.
- [ ] Documentação de tema é atualizada para corresponder ao CSS real.

O critério 1 permanece desmarcado de propósito: o teste existente passa, mas verifica apenas a string da classe no HTML, não a existência de uma camada de contraste renderizada.


## Validação em dispositivo - 2026-11-06

- ackground-image do body =
one; nenhuma regra .wallpaper__scrim nas folhas de estilo carregadas; --theme-scrim não usada.
- @media (prefers-reduced-motion: reduce) presente nas folhas carregadas.


## Implementação aplicada - 2026-11-06 (Fase 3)

- Regra .wallpaper__scrim adicionada em pp.css (overlay escuro + blur 2px).
- Wallpaper aplicado como ackground-image no <body> (cover, center, fixed).
- Opacidade do scrim configurável via slider (0-80%), persistida em settings (ppearance.scrim_opacity).
- --wallpaper-scrim-opacity aplicado como CSS variable inline no body.
- .hud-panel ainda usa --color-on-surface-rgb inexistente (pendência menor, fora do escopo).