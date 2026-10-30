# 028 — Recursos nativos durante a partida

- **Status:** Pendente
- **Área:** NativePHP e experiência mobile
- **Prioridade:** Média

## Objetivo

Integrar recursos do dispositivo úteis durante uma partida, somente quando suportados pela versão NativePHP adotada.

## Escopo

- Modo tela cheia quando apropriado.
- Preferência para manter a tela ligada durante a partida.
- Bloqueio opcional de orientação.
- Feedback tátil e sonoro opcional.
- Tratar ciclo de vida e retorno do app sem perder estado.
- Integrar apenas APIs documentadas; não criar botões sem ação ou simulações de recurso nativo.

## Critérios de aceite

- [ ] Cada preferência tem comportamento verificado em Android.
- [ ] Preferências são persistidas localmente e podem ser desativadas.
- [ ] O estado é restaurado após interrupção ou retorno ao app.
- [ ] Indisponibilidade de API é comunicada sem indicar que o recurso está ativo.
