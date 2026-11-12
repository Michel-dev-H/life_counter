# 034 — Correções da validação visual (navegador)

- **Status:** Em andamento → corrigidos os itens de código; itens de teste manual permanecem pendentes
- **Área:** Visual / UX
- **Prioridade:** Alta

## Achados da validação manual (viewport mobile, 2026-11-12)

1. **Home**: padrão `circuit-bg` quase imperceptível. ✅ corrigido (opacidade 0.12).
2. **Arena 2P**: sem navbar no rodapé (footer fixo não renderizou). ✅ footer fixo adicionado.
3. **Arena 2P**: LP não atualiza ao clicar −500/−1000 (clique não refletiu). ⏳ pendente de revalidação manual.
4. **Arena multi**: botão 8P quebra com `ReferenceError: setPlayerLayout is not defined`. ✅ JS implementado.
5. **Arena multi**: "Expandir" não altera o painel visivelmente. ⏳ pendente de revalidação manual.
6. **Fontes**: Orbitron/Rajdhani não carregam (`document.fonts.check` false); fallback para JetBrains/Space Grotesk. ✅ link restaurado.
7. **Aparência**: upload/remove/save não testados. ⏳ pendente de teste manual.
8. **Movimento reduzido**: transições ainda em 0.15s (apenas animações desativadas). ✅ transições zeradas.
9. **Clicks com timeout** no navegador (Playwright) — Livewire requests não completam. ⏳ pendente de revalidação.

## Critérios de aceite

- [ ] `circuit-bg` visível.
- [ ] Footer fixo presente em todas as telas novas.
- [ ] LP atualiza ao clicar nos botões de dano/recuperação.
- [ ] Switcher 1P/4P/8P funciona sem erro JS.
- [ ] Fontes Orbitron/Rajdhani carregam (ou fallback aceitável documentado).
- [ ] Aparência: upload, preview, aplicar, remover e scrim testados.
- [ ] `prefers-reduced-motion`: transições também desativadas.
- [ ] Sem timeouts de clique.
