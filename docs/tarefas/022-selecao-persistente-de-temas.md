# 022 — Seleção persistente de temas

- **Status:** Revisado — correções aprovadas aguardando implementação
- **Área:** Aparência
- **Prioridade:** Média

## Implementado no repositório

- Enum com Holographic, Shadow Realm e Minimal Arena.
- Tela de aparência que permite escolher tema e persiste a escolha em `settings`.
- Layout aplica o tema salvo e há testes de seleção e persistência.

## Revisão pendente

- [ ] Validar troca de tema na interface e após nova carga da página.
- [ ] Confirmar que todos os recursos mantêm contraste e estado ao trocar de tema.
- [ ] Revisar comportamento no empacotamento NativePHP e offline.

## Lacuna relacionada

A diferenciação visual completa entre os três temas é escopo da tarefa 023.

## Resultado da revisão

- Teste focado executado: `php artisan test --compact tests\Feature\AppearanceTest.php --filter="lets the user pick a theme and persists it"` — 1 teste passou, 4 assertions.
- A escolha do tema é validada pelo enum e persistida em `settings`; `Appearance::theme()` fornece fallback para Holographic quando o valor salvo é ausente ou inválido.
- A página de configurações e o layout renderizam o tema persistido em requisições novas.
- Na página servida pelo navegador, o `data-theme` é aplicado ao `<body>`, mas o layout não carrega o listener `theme-updated`: o script está em uma seção sem `@yield` no layout. Assim, a seleção salva, mas não há evidência de aplicação imediata na mesma tela.
- A meta `theme-color` do layout permanece fixa em `#0f131d`, embora `Theme::browserColor()` forneça cores específicas por tema.
- `AppearanceSettings::selectTheme()` grava diretamente via `Setting::write`, apesar de `Appearance::selectTheme()` já existir. Reusar o serviço evitaria duplicar o ponto de persistência.
- A execução completa de `AppearanceTest.php` ficou em 9/10 por causa do teste de scrim do papel de parede, falha registrada na tarefa 027. O teste específico de persistência de tema passou.
- A página exibiu `Minimal Arena` selecionado, mas o CSS carregado manteve a superfície escura. A diferenciação completa de paletas é tarefa 023; o teste de NativePHP/offline também não foi feito nesta revisão.

## Decisões acordadas

- Aplicar a seleção visualmente na mesma tela, sem exigir recarga.
- Atualizar a meta `theme-color` conforme `Theme::browserColor()` na carga inicial e ao trocar de tema.
- Manter uma preferência global do app instalado, persistida localmente; o tema não varia por duelo.

## Próximos passos

- [ ] Ligar a seleção Livewire a um mecanismo ativo para atualizar `data-theme` imediatamente.
- [ ] Renderizar/sincronizar `theme-color` com o tema salvo e com as trocas em tempo real.
- [ ] Reutilizar `Appearance::selectTheme()` na gravação do tema, evitando duplicar a persistência.
- [ ] Adicionar teste/validação da aplicação imediata e da cor inicial/dinâmica da meta.
- [ ] Validar a persistência local e a mudança visual num build NativePHP Android.
- [ ] Acompanhar a diferenciação de paletas e a falha do scrim nas tarefas 023 e 027, respectivamente.

**Conclusão:** a seleção e persistência básica estão implementadas, mas a aplicação imediata e a cor do navegador não correspondem ao comportamento esperado. A tarefa permanece aberta até as correções aprovadas serem implementadas e a persistência ser validada no pacote NativePHP.
