# 005 — Configuração básica da partida

- **Status:** Revisado — pendências de pesquisa e validação
- **Área:** Preparação de partida
- **Prioridade:** Alta

## Implementado no repositório

- Seleção de 1 a 8 participantes.
- Campos de nome e início da partida com PV inicial selecionável.
- Presets de 8.000 e 4.000 PV, valor personalizado e um preset adicional de 16.000.
- Testes para limites de participantes, nomes vazios e PV iniciais.

## Pendências após a revisão

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

**Conclusão:** a revisão foi realizada. A funcionalidade básica e os testes estão aprovados; rótulos, preset compartilhado e UX móvel permanecem como pendências de pesquisa/validação, não como revisão por fazer.

## Registro do trabalho, metodologia e pendências

### O que foi feito e como

- A implementação existente do lobby foi revisada: seleção de 1–8 participantes, nomes, PV inicial, presets e início do duelo.
- O fluxo e os limites foram confrontados com os testes de `DuelSetupTest.php`; os resultados e dúvidas de produto foram registrados acima, sem alterar regras não confirmadas.

### Metodologia e regras aplicadas

- Revisão orientada pelos critérios de aceite e pela evidência dos testes, distinguindo funcionalidade implementada de UX ainda não validada.
- Seguir a stack escolhida pelo projeto (Laravel/Livewire/NativePHP), preservar o comportamento existente e não atribuir caráter oficial a formatos casuais sem pesquisa de fonte.
- As regras específicas de `app/**/*.php` e `tests/**/*.php` não se aplicavam a uma alteração de documentação/revisão nesta tarefa; não foram usados testes como substituto de validação visual.

### Skills e fontes de apoio

- `grilling`: esclarecer decisões de comportamento e de escopo antes de recomendar mudanças.
- Skills de implementação Laravel/Livewire não foram necessárias para esta revisão; a tarefa não introduziu código.
- A tarefa 001 continua sendo a fonte planejada para a pesquisa de nomenclatura/regras oficiais.

### O que falta

- Validar visualmente o lobby em mobile e no NativePHP Android.
- Pesquisar regras oficiais antes de confirmar rótulos de formatos casuais e a descrição do preset de 16.000 PV como compartilhado.
- Ampliar testes Livewire para limites de nome/PV e seleção dinâmica de capacidade; os testes existentes cobrem o fluxo básico, não toda essa matriz.
- Revisar a configuração de 16.000 PV: atualmente o valor é aplicado individualmente por duelista, portanto confirmar se isso corresponde à intenção de produto.

### Referências de execução

- Superfícies revisadas: `app/Livewire/DuelSetup.php`, `app/Models/Duel.php`, `resources/views/livewire/duel-setup.blade.php` e `tests/Feature/DuelSetupTest.php`.
- Reexecução: `php artisan test --compact tests\Feature\DuelSetupTest.php` — 10 testes passaram.
- A prova é automatizada; não foi feita inspeção visual mobile/desktop nem validação NativePHP nesta revisão. Skills de Laravel/Livewire não foram ativadas porque não houve alteração de código; `grilling` apoiou a definição do escopo e das pendências.
