# 027 — Contraste e scrim de papel de parede

- **Status:** Pendente
- **Área:** Aparência e acessibilidade
- **Prioridade:** Alta

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
