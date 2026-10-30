# 032 — QA e testes de integração

- **Status:** Pendente
- **Área:** Qualidade
- **Prioridade:** Bloqueadora

## Objetivo

Validar regras, persistência, acessibilidade e interfaces de ponta a ponta sem considerar compilação isolada como aceite.

## Escopo

- Manter testes para PV, histórico, limites e isolamento entre jogadores.
- Cobrir criação de sessões com 1 e 8 jogadores e rejeição de limites inválidos.
- Adicionar testes para sorteios, contadores, lembretes, temas e recuperação conforme implementados.
- Validar persistência local e recuperação após encerramento.
- Revisar layouts por viewport, orientação, temas, teclado e seletores de imagem.
- Executar a suíte e registrar falhas existentes sem ocultá-las.

## Situação observada

Na inspeção inicial, 79 de 80 testes passaram. O teste pendente envolve a ausência de `wallpaper__scrim` na resposta após upload e está detalhado na tarefa 027.

## Critérios de aceite

- [ ] Suíte relevante passa sem ignorar ou remover testes para mascarar defeitos.
- [ ] Regras críticas têm testes de domínio e integração.
- [ ] Cenários NativePHP são validados em build/dispositivo quando disponíveis.
- [ ] Falhas e limitações de ambiente são reportadas com evidência.
