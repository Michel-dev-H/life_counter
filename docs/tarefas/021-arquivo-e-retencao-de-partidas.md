# 021 — Arquivo e retenção de partidas

- **Status:** Pendente
- **Área:** Histórico e armazenamento
- **Prioridade:** Baixa

## Objetivo

Permitir consultar partidas anteriores localmente e controlar o crescimento do armazenamento.

## Escopo

- Criar lista de partidas arquivadas com data, participantes e informações de resultado disponíveis.
- Permitir abrir detalhes/histórico de uma partida encerrada sem alterar a sessão atual.
- Definir política simples e configurável de retenção.
- Exigir confirmação antes de excluir dados históricos.
- Não exigir conta, sincronização ou servidor.

## Critérios de aceite

- [ ] Partidas arquivadas permanecem isoladas da sessão ativa.
- [ ] A política de retenção é explícita e não remove dados sem aviso indevido.
- [ ] Exclusões são confirmadas e consistentes entre dados associados.
- [ ] Testes cobrem listagem, consulta e retenção.
