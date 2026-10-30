# 026 — Imagem local e ajustes de enquadramento

- **Status:** Pendente
- **Área:** Aparência e recursos nativos
- **Prioridade:** Média

## Objetivo

Permitir usar uma imagem do próprio dispositivo sem exigir reconhecimento de imagem nem acesso desnecessário a arquivos.

## Escopo

- Abrir seletor de imagens compatível com NativePHP após validar a API disponível.
- Permitir pré-visualização e ajuste de enquadramento.
- Permitir escurecer e aplicar desfoque opcional.
- Armazenar cópia em localização local apropriada do app, com remoção explícita.
- Solicitar apenas permissões necessárias e explicar erros de acesso.

## Critérios de aceite

- [ ] Usuário consegue selecionar, pré-visualizar, aplicar e remover imagem.
- [ ] Permissões seguem o mínimo necessário e comportamento da versão Android suportada.
- [ ] Imagem persiste após reiniciar o app e não exige conexão.
- [ ] Falha de seleção/armazenamento é comunicada claramente.
- [ ] Prévia não altera a aparência salva antes de confirmação.
