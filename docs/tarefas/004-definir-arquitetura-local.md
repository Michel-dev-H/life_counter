# 004 — Definir arquitetura e estado local

- **Status:** Pendente
- **Área:** Arquitetura
- **Prioridade:** Alta

## Objetivo

Definir como os recursos atuais e futuros serão organizados no Laravel/Livewire e persistidos localmente para uso offline via NativePHP.

## Escopo

- Inspecionar e preservar os modelos existentes de duelo, jogador, eventos de PV e configurações.
- Definir limites entre interface, regras de domínio, persistência, preferências e integrações NativePHP.
- Modelar sessão, eventos aleatórios, referências de cartas, contadores, lembretes, turnos e aparência conforme a necessidade comprovada.
- Manter identidades estáveis, associação de eventos à sessão e alvos dos contadores.
- Evitar autenticação, backend remoto e abstrações especulativas.

## Critérios de aceite

- [ ] A proposta identifica modelos/tabelas existentes reutilizáveis e lacunas reais.
- [ ] Regras de jogo não ficam acopladas à apresentação.
- [ ] Preferências visuais não alteram cálculos nem estado da partida.
- [ ] A persistência escolhida e a recuperação de sessão são compatíveis com o teste de viabilidade NativePHP.
- [ ] Mudanças de esquema e interfaces têm estratégia incremental e testável.
