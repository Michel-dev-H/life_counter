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
