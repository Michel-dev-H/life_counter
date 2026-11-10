# 006 — Identidade e opções dos jogadores

- **Status:** Implementado — aguardando revisão
- **Área:** Preparação de partida
- **Prioridade:** Média

## Objetivo

Permitir configurar a identidade visual e as opções individuais dos participantes antes e durante uma partida, mantendo as configurações consistentes.

## Escopo

- Permitir selecionar ou editar uma cor de identificação por jogador sem depender de cores fixas inadequadas para o tema.
- Permitir avatar ou ícone opcional, sem exigir imagem ou reconhecimento automático.
- Preservar nome/apelido, identidade estável e ordenação dos assentos.
- Disponibilizar layout e opções relevantes no contexto de configuração, sem duplicar preferências globais.
- Distinguir claramente modos casuais de regras oficiais quando aplicável.

## Critérios de aceite

- [x] Configurações de cada jogador são persistidas na sessão correta.
- [x] Cores e ícones continuam distinguíveis nos temas; os acentos semânticos não dependem da imagem de papel de parede.
- [x] Participantes sem avatar ou cor personalizada recebem uma opção padrão legível.
- [x] Edição não altera PV, histórico ou identidade de outros participantes.

## Implementação

- O lobby permite escolher uma entre oito cores semânticas e um ícone opcional por assento. Os padrões são aplicados de acordo com a posição.
- Cada tema mapeia as mesmas cores semânticas para sua própria paleta; nenhum valor hexadecimal escolhido pelo usuário é armazenado.
- A arena permite editar nome/apelido, cor e ícone de um participante. A edição é limitada ao duelo atual e mantém o vínculo de perfil, os PV, o histórico, os assentos e os demais participantes.
- Duelos existentes com cores hexadecimais são exibidos com o acento padrão do respectivo assento.
- O ícone de espadas é usado quando nenhum ícone foi escolhido. Não há upload de imagem, conforme o escopo opcional da tarefa.

## Validação

- `php artisan test --compact tests\Feature\DuelSetupTest.php tests\Feature\DuelArenaTest.php` — 38 testes passaram.
- `php artisan test --compact` — 96 testes passaram.
- `vendor/bin/phpstan analyse --no-progress app\Livewire\DuelSetup.php app\Livewire\DuelArena.php app\Models\Duel.php app\Models\DuelPlayer.php` — sem erros.
- `npm run build` — concluído.
- A inspeção no navegador confirmou os controles do lobby em largura móvel, a abertura e o cancelamento da edição na arena, e a resolução das cores nos três temas.

O contraste global do papel de parede e seu scrim permanecem no escopo da tarefa [027](./027-contraste-e-scrim-do-papel-de-parede.md).

## Registro do trabalho, metodologia e pendências

### O que foi feito e como

- O lobby passou a oferecer oito cores semânticas e ícones opcionais por assento; a arena permite editar identidade sem modificar PV, histórico, assentos ou outros jogadores.
- As cores são resolvidas pela paleta do tema, em vez de persistir hexadecimais escolhidos no jogador. Cores antigas semânticas são apresentadas com fallback legível; ícone padrão permanece disponível.
- O fluxo foi coberto por testes de setup e arena e verificado no navegador em largura móvel e nos três temas.

### Metodologia e regras aplicadas

- Reutilizar os modelos, componentes e convenções existentes; validar a alteração no limite da partida e não duplicar preferências globais em cada jogador.
- Cobrir o comportamento com testes focados e build de frontend; revisar visualmente o resultado em vez de inferir contraste apenas do HTML.
- Aplicar as regras de domínio Laravel, estado Livewire server-side, acessibilidade e utilitários Tailwind existentes.

### Skills e fontes de apoio

- `laravel-best-practices`, `livewire-development`, `tailwindcss-development` e `testing-best-practices`.
- Documentação da tarefa e padrões já existentes de paleta/tema do projeto.

### O que falta

- Fazer a revisão final contra todos os requisitos da tarefa; o status permanece “aguardando revisão”.
- A validação desta tarefa foi feita no navegador; validar novamente as opções e a edição de identidade no NativePHP Android se a interface for alterada ou antes do aceite integrado.
- Contraste geral sobre papel de parede continua pertencendo à tarefa [027](./027-contraste-e-scrim-do-papel-de-parede.md), não a esta implementação.

### Superfícies e comandos registrados

- Implementação: `app/Livewire/DuelSetup.php`, `app/Livewire/DuelArena.php`, `app/Models/Duel.php`, `app/Models/DuelPlayer.php`, `resources/views/livewire/duel-setup.blade.php` e `resources/views/livewire/duel-arena.blade.php`.
- Testes: `tests/Feature/DuelSetupTest.php` e `tests/Feature/DuelArenaTest.php`.
- Reexecução focada: `php artisan test --compact tests\Feature\DuelSetupTest.php tests\Feature\DuelArenaTest.php` — 38 passaram. Suíte: `php artisan test --compact` — 96 passaram.
- Verificações registradas: PHPStan nos quatro arquivos PHP citados (sem erros), `npm run build` (passou) e inspeção browser dos controles em largura móvel e dos três temas. Não há validação NativePHP registrada para essa implementação.
- Essa lista identifica as superfícies do recurso; não usar o checklist como afirmação de que todas as versões do arquivo foram editadas exclusivamente na tarefa 006.


## Validação em dispositivo - 2026-11-06

- Modal de identidade abre com nome, cor e ícone; Salvar persiste o nome (testado Michel -> MichelX -> Michel) e o painel atualiza.
- Cancelar fecha sem alterar.