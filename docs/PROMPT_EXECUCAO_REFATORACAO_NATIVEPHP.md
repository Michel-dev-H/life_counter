# Prompt de execução — Refatoração NativePHP para EDGE

Você atuará como engenheiro sênior responsável por conduzir a refatoração arquitetural deste projeto Laravel/NativePHP Mobile.

## Instrução inicial obrigatória

1. Leia as instruções do repositório e as Skills já existentes antes de modificar qualquer coisa.
2. Carregue a Skill `nativephp-edge-refactor` localizada em `.agents/skills/nativephp-edge-refactor/SKILL.md` (ID esperado: `nativephp-edge-refactor`).
3. A Skill `grill-me` já está instalada no ambiente. Identifique seu ID real e carregue-a quando a Skill de refatoração determinar. Não crie uma cópia dela.
4. Siga integralmente as regras, etapas, critérios de segurança e definição de pronto da Skill.

## Objetivo do trabalho

Refatorar a interface atual, predominantemente construída em WebView, para uma arquitetura **native-first com EDGE e componentes nativos do NativePHP**, preservando integralmente as funcionalidades e regras de negócio existentes.

- Android é a plataforma prioritária para implementação e validação.
- A arquitetura deve permanecer preparada para iOS.
- WebView poderá continuar em telas ou áreas nas quais exista justificativa técnica/funcional documentada. Não mantenha WebView por inércia, mas também não force uma conversão que aumente risco ou prejudique a experiência.
- Avalie plugins oficiais, comunitários e próprios quando necessário, seguindo os pontos de decisão e a entrevista com `grill-me` previstos na Skill.
- Ao final, elimine arquivos e dependências comprovadamente sem uso, mantendo registro e evidências. O objetivo é não deixar código morto ou duplicado da implementação substituída.

## Regras de execução

- Comece pelo diagnóstico, sem alterar arquivos.
- Descubra a versão exata de PHP, Laravel, NativePHP Mobile, `nativephp/mobile-ui`, Livewire e demais dependências no projeto. Não presuma que o projeto usa NativePHP Mobile v4.
- Consulte a documentação oficial correspondente à versão instalada e compare os exemplos com o código realmente instalado.
- Não faça upgrade de versão principal nem atualize dependências centrais sem explicar os impactos e consultar o usuário via `grill-me`.
- Não remova nem simplifique funcionalidades para acelerar a migração.
- Preserve banco de dados, migrations históricas, dados, integrações, autenticação, autorização, validações e contratos existentes.
- Não instale, registre, remova ou desenvolva plugin sem executar o processo de avaliação e decisão via `grill-me` quando aplicável.
- Não adicione plugin Android-only como dependência estrutural sem aprovação explícita e estratégia documentada para iOS.
- Não apague arquivos até a substituição correspondente funcionar e a ausência de referências estar comprovada.
- Não faça commit, push, deploy ou publicação nas lojas.
- Não conclua apenas com um relatório de recomendações: depois das decisões do usuário, execute a refatoração incremental dentro das permissões e possibilidades do ambiente.

## Etapas que você deve executar

### Etapa 1 — Leitura e diagnóstico

- Leia `AGENTS.md`, instruções do agente, `README`, Skills existentes e documentação interna relevante.
- Verifique `git status`, branch e alterações pendentes para não sobrescrever trabalho existente.
- Inspecione `composer.json`, `composer.lock`, `package.json`, lockfiles, configuração NativePHP, rotas, views, componentes, controllers, serviços, JS, CSS, assets, testes, permissões e integrações.
- Mapeie todas as telas e fluxos atualmente exibidos pela WebView.
- Identifique a versão correta da documentação NativePHP.
- Execute a suíte e os builds disponíveis para registrar o baseline. Separe falhas preexistentes de regressões novas.
- Não instale dependências nem remova arquivos nesta etapa.

### Etapa 2 — Inventário e plano

Crie ou atualize, respeitando a documentação existente, os artefatos abaixo em `docs/nativephp-refactor/`:

- `inventory.md`: telas, rotas, funcionalidades, estados, dependências, destino proposto e critérios de aceite.
- `migration-plan.md`: sequência incremental, dependências, riscos, testes e status.
- `decisions.md`: decisões e respostas obtidas por meio do `grill-me`, além de plugins avaliados.
- `cleanup-audit.md`: evidências e histórico de limpeza.

Para cada tela, recomende EDGE, componente composto, WebView isolada, solução híbrida ou plugin. Justifique a escolha e os efeitos para Android/iOS.

Apresente o diagnóstico e o plano. Use `grill-me` para ambiguidades relevantes e para qualquer oportunidade real de plugin. Não faça perguntas sobre fatos que possam ser obtidos no próprio repositório ou na documentação.

### Etapa 3 — Definição da arquitetura

Escolha a arquitetura compatível com a versão instalada. Não misture APIs de versões diferentes.

Priorize nesta ordem: recursos core; componentes EDGE existentes; componentes compostos; plugins oficiais; plugins comunitários; plugins próprios; WebView/híbrido quando for a alternativa mais adequada.

Se o projeto estiver na v4, avalie o uso dos recursos documentados dessa versão, como Native Components, rotas nativas, NativeLayout e o harness de testes nativo. Em versões anteriores, siga exclusivamente a arquitetura da major instalada.

Preserve a lógica de negócio existente no backend/domínio. A camada nativa deve cuidar da apresentação e interação, reutilizando os serviços e validações existentes.

### Etapa 4 — Refatoração incremental

- Escolha uma primeira fatia vertical representativa e de risco controlado.
- Implemente a tela/fluxo nativo completo, incluindo estados e tratamento de erros.
- Valide no Android antes de replicar o padrão.
- Reutilize componentes e layouts, sem transformar tudo em um componente monolítico.
- Migre outras telas em blocos pequenos e independentes.
- Mantenha as telas antigas até que a versão substituta esteja validada.
- Atualize inventário, plano, decisões e testes a cada fatia.
- Antes de implementar plugin novo, volte ao fluxo de decisão obrigatório da Skill e consulte `grill-me`.

### Etapa 5 — Testes e validação

Execute os testes automatizados e builds disponíveis, além dos testes manuais possíveis no Android. Valide pelo menos os fluxos críticos, navegação, voltar, formulários, permissões, teclado, rolagem, safe areas, rede/offline e retomada do app, conforme o escopo real.

Verifique compatibilidade com iOS na arquitetura e nos plugins. Não afirme que iOS foi validado se não houve build/teste no Xcode ou em dispositivo iOS; registre como pendência quando necessário.

### Etapa 6 — Limpeza final

Após a migração e validação:

- Localize código, views, scripts, estilos, assets, dependências, plugins e configurações substituídos.
- Procure referências diretas e indiretas antes de remover.
- Considere autoload, convenções Laravel, views dinâmicas, imports, rotas, eventos, assets, recursos nativos, manifests, CI/build e dependências indiretas.
- Remova somente itens comprovadamente sem uso.
- Preserve dados, uploads, `.env`, certificados, migrations, arquivos de plataforma e alterações preexistentes.
- Execute novamente testes, análise estática e builds relevantes depois da limpeza.
- Registre cada remoção e sua evidência em `cleanup-audit.md`.

## Comunicação durante a execução

Trabalhe por etapas, mas não fique apenas narrando progresso. Ao chegar a uma decisão que dependa do usuário, use `grill-me` e apresente contexto suficiente para uma escolha informada. Quando não houver bloqueio, avance para a próxima etapa.

Em cada marco, informe de forma objetiva:

- O que foi concluído;
- Arquivos/áreas alteradas;
- Testes executados e resultados;
- Decisões tomadas;
- Bloqueios e próximos passos.

## Condições para declarar a tarefa concluída

Só declare a refatoração concluída quando cumprir a definição de pronto da Skill `nativephp-edge-refactor` e entregar um relatório final com:

1. Versões e dependências relevantes detectadas;
2. Arquitetura adotada;
3. Telas migradas e WebViews mantidas com justificativas;
4. Plugins adotados, rejeitados ou criados e por quê;
5. Funcionalidades preservadas;
6. Arquivos e dependências removidos com evidências;
7. Testes e builds executados com resultados reais;
8. Validação Android;
9. Compatibilidade/pendências iOS;
10. Riscos e pendências restantes.

**Comece agora pela Etapa 1. Não modifique o projeto antes de concluir o diagnóstico inicial e registrar o estado atual.**
