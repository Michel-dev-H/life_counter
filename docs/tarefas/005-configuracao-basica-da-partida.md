# 005 — Configuração básica da partida

- **Status:** Feito — aguardando revisão
- **Área:** Preparação de partida
- **Prioridade:** Alta

## Implementado no repositório

- Seleção de 1 a 8 participantes.
- Campos de nome e início da partida com PV inicial selecionável.
- Presets de 8.000 e 4.000 PV, valor personalizado e um preset adicional de 16.000.
- Testes para limites de participantes, nomes vazios e PV iniciais.

## Revisão pendente

- [ ] Conferir UX e legibilidade do lobby em telas móveis.
- [ ] Confirmar que os textos identificam variantes casuais sem atribuir regras oficiais indevidas.
- [ ] Revisar validações e limites à luz dos presets disponíveis.
- [ ] Confirmar fluxo de início contra os requisitos de navegação e recuperação.

## Fora do escopo já implementado

Seleção de cor/avatar por jogador, orientação/layout no setup e configuração de atalhos são tratados em tarefas próprias.

## Resultado da revisão

- Teste executado: `php artisan test --compact tests\Feature\DuelSetupTest.php` — 10 testes passaram.
- A configuração aceita 1–8 jogadores, nomes em branco com nome automático, PV inicial personalizado e validações no componente.
- A tela associa rótulos de formato aos presets/capacidades. A precisão desses rótulos fica pendente da pesquisa oficial da tarefa [001](./001-pesquisar-regras-oficiais.md), conforme decisão de adiar ajustes até verificar as fontes.
- O preset de 16.000 é aplicado individualmente a cada participante por `Duel::startWith`; a interface o descreve como “2v2 Compartilhado”. Confirmar a intenção e, se necessário, ajustar o texto ou a modelagem depois da pesquisa.
- Os testes cobrem limites do domínio e o fluxo básico, mas ainda podem ganhar casos Livewire para nome acima de 40 caracteres, PV abaixo/acima do limite e seleção dinâmica de capacidade.
- A responsividade visual não foi validada em navegador mobile nem em build NativePHP.

**Conclusão:** a funcionalidade básica está implementada, mas permanece “Feito — aguardando revisão” até validar rótulos, preset compartilhado e UX móvel.
