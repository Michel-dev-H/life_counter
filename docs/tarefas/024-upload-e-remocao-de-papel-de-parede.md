# 024 — Upload e remoção de papel de parede

- **Status:** Revisado — correções aprovadas aguardando implementação
- **Área:** Aparência
- **Prioridade:** Média

## Implementado no repositório

- O componente Livewire contém operações de upload, substituição e remoção de papel de parede.
- O caminho da imagem é persistido na configuração e há testes para upload, substituição, remoção e arquivo não aceito.
- A implementação atual usa o disco `public`; isso não atende à decisão de manter a imagem em armazenamento privado local.

## Resultado da revisão

- `php artisan test --compact tests\Feature\AppearanceTest.php`: 9/10 testes passaram. O teste de upload falha ao esperar `wallpaper__scrim`; essa correção visual pertence à [tarefa 027](./027-contraste-e-scrim-do-papel-de-parede.md).
- A tela de aparência não contém seletor de arquivo nem botão para enviar. Os testes definem diretamente a propriedade Livewire `wallpaper`, portanto não cobrem a seleção e o envio pela interface.
- O componente confere o MIME, mas não aplica limite de tamanho nem valida explicitamente que a imagem pode ser decodificada e que suas dimensões são aceitáveis. A documentação existente declara limite de 2 MB.
- O componente remove a imagem anterior antes de armazenar a nova. Como o disco está configurado com `throw` e `report` desativados, uma falha de armazenamento pode deixar o usuário sem imagem e sem erro visível.
- A remoção ignora o resultado da exclusão e limpa a configuração mesmo se o arquivo não for removido.
- Há lógica duplicada de armazenamento e remoção em `app/Appearance.php` e `app/Models/Appearance.php`; ambos declaram `App\Appearance`. Consolidar em uma implementação antes de alterar esse fluxo.
- A integração de galeria, persistência e renderização no NativePHP continua sujeita à validação de compatibilidade da [tarefa 003](./003-validar-nativephp-offline.md) e ao fluxo de seleção/enquadramento da [tarefa 026](./026-imagem-local-e-ajustes.md).

## Decisões aprovadas para implementação futura

- A tela terá seletor de arquivo, prévia e ação explícita para aplicar. A integração com a galeria nativa e o enquadramento permanecem na tarefa 026.
- Aceitar JPEG, PNG e WebP; validar o conteúdo como imagem decodificável; limitar o arquivo a 2 MB e rejeitar dimensões superiores a 4096 × 4096 px. Não redimensionar automaticamente nesta tarefa.
- Armazenar a imagem no disco privado local, sem expor o arquivo por URL pública direta.
- Servir somente o papel de parede atualmente configurado por uma rota controlada, sem aceitar caminhos de arquivo fornecidos pelo cliente. O modelo de confiança é aplicação local/offline sem autenticação; validar o isolamento no NativePHP na tarefa 003.
- Salvar a nova imagem antes de remover a anterior. Se o novo armazenamento falhar, preservar a imagem atual e informar o erro. Se a nova imagem for salva mas a exclusão da anterior falhar, manter a nova ativa e reportar a falha de limpeza; o arquivo antigo poderá ficar órfão.
- Se a remoção explícita falhar, manter a referência atual e informar o erro.

## Critérios de aceite

- [ ] Usuário consegue selecionar uma imagem pela interface, visualizar uma prévia e confirmar sua aplicação.
- [ ] Arquivos inválidos, não decodificáveis, acima de 2 MB ou com dimensões maiores que 4096 × 4096 px são rejeitados com mensagem compreensível.
- [ ] Arquivo e caminho persistido usam armazenamento privado local; a rota de exibição entrega apenas o papel de parede ativo.
- [ ] Falha ao salvar o novo arquivo preserva o anterior; falha na limpeza do anterior é reportada sem desfazer a nova imagem ativa.
- [ ] Falha na remoção explícita preserva a configuração e é reportada.
- [ ] Testes cobrem o fluxo de interface, estado persistido, validações e falhas de armazenamento; não dependem apenas de definir diretamente a propriedade Livewire.
- [ ] Compatibilidade e persistência no NativePHP são verificadas conforme as tarefas 003 e 026.
- [ ] Scrim, contraste e remoção visual do fundo passam pelos critérios da tarefa 027.

Nenhum código da aplicação foi alterado nesta revisão.

## Registro para continuidade por outro agente

### Trabalho realizado e evidências

- A revisão comparou escopo, componente Livewire, serviço `Appearance`, view, disco configurado, testes e guia `docs/theme-guidelines.md`.
- Comando executado: `php artisan test --compact tests\Feature\AppearanceTest.php` — 9 de 10 passaram. O teste de upload/papel de parede falha porque espera `wallpaper__scrim`; essa apresentação pertence à tarefa 027 e não prova, por si só, falha de gravação do arquivo.
- Os testes existentes definem diretamente `wallpaper` via `Livewire::test()->set(...)`; não comprovam que a interface permita selecionar/enviar arquivo. A view inspecionada não continha input file nem ação de envio.
- `AppearanceSettings::saveWallpaper()` confere presença/validade e MIME JPEG/PNG/WebP, mas não aplica o limite de 2 MB do guia nem valida imagem decodificável/dimensões. Remove o fundo antigo antes de armazenar o novo.
- O disco `public` tem `throw`/`report` desativados; `store()` pode falhar sem exceção útil. A remoção ignora retorno de `Storage::delete`, podendo limpar a referência mesmo se o arquivo continuar no disco.
- Foi observada lógica duplicada entre `app/Appearance.php` e `app/Models/Appearance.php`, ambos declarando `App\Appearance`. A tarefa 003 também registra um warning PSR-4 e uma tentativa de remover arquivo obsoleto; antes de tocar essas classes, verificar `git status`, árvore atual e autoload, pois esses registros descrevem momentos diferentes do worktree.
- Persistência atual via disco `public`/`storage:link` não foi validada no NativePHP e não atende à decisão de armazenamento privado local.

### Decisões já aprovadas (não perguntar novamente sem nova evidência)

- Incluir na tela atual seletor de arquivo, prévia e ação explícita para aplicar; integração com galeria nativa/enquadramento fica na tarefa 026.
- Aceitar JPEG, PNG e WebP, validar conteúdo decodificável, tamanho máximo 2 MB e dimensões máximas 4096×4096. Não redimensionar automaticamente nesta tarefa.
- Usar disco privado local; não expor caminho de arquivo fornecido pelo cliente nem URL pública direta.
- Servir somente o papel de parede atualmente ativo por rota controlada. O modelo previsto é app local/offline sem autenticação; validar isolamento no NativePHP pela tarefa 003.
- Salvar novo arquivo antes de remover o anterior. Falha ao salvar mantém o anterior e informa erro. Se novo arquivo for salvo, mas limpeza do anterior falhar, manter o novo ativo e reportar limpeza incompleta (arquivo antigo órfão). Remoção explícita que falha deve preservar a referência e informar erro.
- Scrim/contraste/renderização visual pertencem também à tarefa 027; NativePHP/galeria/enquadramento dependem das tarefas 003/026.

### Metodologia, regras e skills

- Esta etapa foi revisão sem alteração de código: nenhum teste foi escrito ou removido, e não foram executados Pint, PHPStan ou build.
- O defeito foi analisado da interface até armazenamento e resposta de erro; os testes foram tratados como evidência parcial, não como prova de um fluxo de seleção que a UI não oferece.
- Aplicar Laravel way para validação/armazenamento/erros, preservar o arquivo atual em falhas e não retornar sucesso falso quando o filesystem falhar.
- `grilling` foi usado para obter decisões explícitas de escopo e comportamento. `laravel-best-practices` é a skill aplicável à implementação Laravel desta tarefa; `livewire-development` e `testing-best-practices` devem ser ativadas antes de construir o fluxo reativo e seus testes.

### Arquivos que o próximo agente deve inspecionar antes de editar

- `app/Livewire/AppearanceSettings.php`: validação, upload e remoção atuais.
- `app/Appearance.php` e `app/Models/Appearance.php`: duplicidade/estado real de cada implementação; não assumir qual é usada sem procurar referências.
- `resources/views/livewire/appearance-settings.blade.php`: controles atuais de temas, preview e wallpaper.
- `resources/views/layouts/app.blade.php`: resolução/uso do tema e renderização do wallpaper.
- `tests/Feature/AppearanceTest.php`: adaptar testes para fluxo real de UI e falhas do Storage; manter o teste conhecido do scrim corretamente alocado.
- `config/filesystems.php` e `docs/theme-guidelines.md`: disco público, `throw`/`report` e limite documentado.
- `docs/tarefas/003-validar-nativephp-offline.md`, `026-imagem-local-e-ajustes.md` e `027-contraste-e-scrim-do-papel-de-parede.md`: dependências e limites de plataforma/visual.

### Próximos passos executáveis

1. Abrir `.ai/rules/index.md`; ler as regras correspondentes a cada arquivo PHP/teste/Blade que será alterado e buscar requisitos adicionais com `grep -rin 'wallpaper' .ai/rules`.
2. Conferir `git status` e referências da classe `Appearance` para resolver a duplicidade sem descartar alterações do usuário. Confirmar versões instaladas dos pacotes relevantes antes de usar APIs específicas.
3. Escrever testes de UI Livewire para seleção/prévia/aplicação, conteúdo inválido, limite 2 MB, dimensão acima de 4096, falha ao salvar/substituir/remover, referência persistida e resposta da rota restrita ao arquivo ativo. Provar a falha antes de implementar.
4. Implementar armazenamento novo antes da limpeza do anterior, erros explícitos e caminho controlado; não usar caminhos fornecidos pelo cliente.
5. Executar `php artisan test --compact tests\Feature\AppearanceTest.php`; depois a suíte focal relacionada, Pint nos PHP alterados, PHPStan focado, build web e `git diff --check`.
6. Validar browser para preview/aplicação/remoção e NativePHP para persistência local/rota; registrar separadamente a falha de scrim se 027 ainda não estiver pronta.
