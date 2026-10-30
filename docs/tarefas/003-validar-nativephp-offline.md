# 003 — Validar NativePHP, Android e funcionamento offline

- **Status:** Pendente
- **Área:** Viabilidade técnica
- **Prioridade:** Bloqueadora

## Objetivo

Confirmar que a aplicação Laravel existente pode ser empacotada com NativePHP para Android e que os fluxos essenciais funcionam sem internet.

## Escopo

- Conferir versões instaladas e requisitos oficiais da versão escolhida do NativePHP.
- Validar suporte às dependências atuais do projeto, ao Livewire, armazenamento local, navegação e seletores de imagem necessários.
- Identificar APIs nativas para manter a tela ligada, orientação, tela cheia e feedback tátil/sonoro.
- Criar uma prova de viabilidade mínima em branch/ambiente de trabalho apropriado, sem substituir a stack por Flutter/React Native.
- Documentar limitações, permissões, fluxo de build e pré-requisitos Android.

## Critérios de aceite

- [ ] Compatibilidade documentada com versões e fontes oficiais.
- [ ] Um build Android de teste é gerado e instalado, se o ambiente permitir.
- [ ] A arena e as operações essenciais funcionam sem conexão após a instalação.
- [ ] Persistência e arquivos locais continuam disponíveis após fechar e reabrir o app.
- [ ] Limitações e bloqueios do ambiente são explícitos; APK não é declarado pronto sem verificação.
