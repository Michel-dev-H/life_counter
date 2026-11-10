# 030 — Navegação e ferramentas contextuais

- **Status:** Em andamento — início, novas partidas, aparência e retomada recente acessíveis
- **Área:** UX e navegação
- **Prioridade:** Alta

## Objetivo

Organizar os fluxos de início, partida e ferramentas para manter ações frequentes a um ou dois toques.

## Escopo

- Criar entrada clara para nova partida, continuar, histórico, aparência e configurações.
- As rotas existentes ficam acessíveis por barra inferior no celular e navegação lateral em telas largas; a arena mantém acesso ao início e à aparência sem ocupar espaço dos controles.
- O início lista as cinco partidas mais recentes para retomada local; não apresenta ferramentas ainda não implementadas.
- Manter preparação da partida separada da arena.
- Oferecer dados, moedas, contadores, lembretes e histórico em painéis contextuais/overlays quando apropriado.
- Evitar navegar para uma página totalmente diferente para uma operação breve.
- Preservar retorno à partida e estado da sessão ao abrir/fechar uma ferramenta.

## Critérios de aceite

- [x] As ações principais da tela inicial têm destinos funcionais para as rotas já implementadas.
- [x] O menu oferece apenas opções funcionais: início, nova partida e aparência.
- [ ] Implementar histórico/arquivo antes de apresentá-lo como destino separado.
- [ ] Ferramentas auxiliares retornam à arena sem perder estado.
- [x] As rotas principais ficam acessíveis em até dois toques no celular e telas largas.
- [x] Nenhum item de navegação é apresentado como funcionalidade se ainda não estiver conectado.

## Registro do trabalho, metodologia e pendências

### O que foi feito e como

- A raiz passou a ser um dashboard com atalhos para preparar nova partida e aparência, além das cinco partidas recentes com acesso para retomada.
- Foi adicionada navegação compartilhada: barra inferior em mobile e menu lateral em telas largas. A arena mantém atalhos próprios para voltar ao início e abrir aparência sem cobrir os controles.
- Só destinos já implementados aparecem no menu; histórico e ferramentas que ainda não existem não são apresentados como se funcionassem.
- A meta `theme-color` do layout acompanha o tema selecionado. Testes focados cobriram dashboard, estado vazio, partidas recentes, rota raiz e navegação da arena.

### Metodologia e regras aplicadas

- Decisões de UX foram esclarecidas com o usuário antes de implementar: menu responsivo, destinos reais apenas e espaço compacto na arena.
- Reaproveitar rotas nomeadas e layouts existentes; separar dashboard/setup/arena e validar responsividade, navegação e comportamento em testes.
- A suíte encontrou uma falha do contrato da cor do tema; a implementação foi corrigida, mas o checkpoint registra que a suíte completa ainda precisava ser repetida após essa última correção.

### Skills e fontes de apoio

- `grilling` para esclarecer decisões de navegação e escopo.
- `laravel-best-practices`, `livewire-development`, `tailwindcss-development` e `testing-best-practices` para as superfícies Laravel/Livewire, layout e cobertura.
- Ferramentas de browser/Playwright foram usadas para validação visual quando disponíveis; nenhuma ferramenta de pesquisa externa foi necessária para construir o menu.

### O que falta

- Reexecutar testes focados e suíte completa após a correção de `theme-color` registrada no histórico.
- Validar visualmente a navegação em viewport mobile e desktop após a implementação.
- Implementar histórico/arquivo antes de oferecer esse destino.
- Integrar moedas, contadores, lembretes e demais ferramentas como ações contextuais quando cada funcionalidade existir; confirmar que abrir/fechar overlays preserva a partida.

### Arquivos, comandos e cronologia da validação

- Arquivos do dashboard/navegação: `app/Livewire/HomeDashboard.php`, `resources/views/livewire/home-dashboard.blade.php`, `resources/views/layouts/primary-navigation.blade.php`, `resources/views/layouts/app.blade.php`, `resources/views/livewire/duel-arena.blade.php`, `resources/views/components/icon.blade.php` e `routes/web.php`.
- Testes: `tests/Feature/HomeDashboardTest.php` e `tests/Feature/ExampleTest.php`. A validação focada registrada foi 22 testes aprovados.
- `npm run build` passou, com aviso opcional do `fontaine`; Pint (`vendor/bin/pint --dirty --format agent`) foi executado.
- Antes da última correção no meta `theme-color`, a suíte completa foi 84 aprovada/1 falha; a falha era `it_lets_the_user_pick_a_theme_and_persists_it`. O layout foi então corrigido para refletir `Theme::browserColor()`, mas não há evidência no checkpoint de que a suíte completa tenha sido rerodada depois.
- Não há registro de inspeção visual final da navegação em browser após a mudança; não declarar UX visual como validada com base apenas nos testes de feature/build.
- O dashboard consulta até cinco duelos recentes com `with('duelPlayers:id,duel_id,name,seat')`, ordenados por data e ID; o menu compartilhado só aparece fora da arena. No fluxo de QA, preservar partidas existentes e não inventar destinos para histórico/ferramentas ainda ausentes.


## Validação em dispositivo - 2026-11-06

- Navegação Nova partida -> /duel/new ("Configuração de Duelo") confirmada.
- Arena /duel/1 carrega com painéis, ferramentas (calculadora, dados, expansão) e histórico.
- Histórico/arquivo e retorno das ferramentas sem perder estado não foram exercitados nesta rodada.