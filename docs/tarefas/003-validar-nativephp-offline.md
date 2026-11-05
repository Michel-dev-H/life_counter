# 003 — Validar NativePHP, Android e funcionamento offline

- **Status:** Parcial — build/abertura confirmados; evidência offline anterior existe, mas repetição posterior ficou inconclusiva; primeiro frame, retomada e APIs nativas pendentes
- **Área:** Viabilidade técnica
- **Prioridade:** Bloqueadora

## Objetivo

Confirmar que a aplicação Laravel existente pode ser empacotada com NativePHP para Android e que os fluxos essenciais funcionam sem internet.

## Escopo

- Conferir versões instaladas e requisitos oficiais da versão escolhida do NativePHP.
- Validar suporte às dependências atuais do projeto, ao Livewire, armazenamento local, navegação e seletores de imagem necessários.
- Identificar APIs nativas para manter a tela ligada, orientação, tela cheia e feedback tátil/sonoro.
- Criar uma prova de viabilidade mínima em branch/ambiente de trabalho apropriado, sem substituir a stack por Flutter/React Native.
- Documentar limitações, permissões, fluxo de build e pré-requisitos Android.

## Progresso de viabilidade — 2026-11-03

- Dependências detectadas: PHP 8.5.7, Laravel 13.39.0, Livewire 4.4.8, NativePHP Mobile 4.7.0 e NativePHP Mobile UI 0.9.0.
- O manifesto instalado do NativePHP aceita PHP `^8.3` e Illuminate até `^13.0`; o Livewire instalado declara compatibilidade com Laravel `^13.0`. `composer check-platform-reqs --no-interaction` passou.
- `php artisan native:version` confirma NativePHP Mobile 4.7.0. `php artisan native:plugin:list` confirma o plugin `nativephp/mobile-ui` registrado. O projeto Android e a configuração NativePHP já existiam no início da validação e foram reutilizados; não foi editado manualmente código nativo.
- A documentação oficial v4 instrui instalar `nativephp/mobile` e `nativephp/mobile-ui`, registrar explicitamente o plugin, executar `native:install` e usar `native:run`. Ela também informa que NativePHP executa a aplicação no dispositivo, sem servidor web, e não oferece suporte a WSL: [Instalação NativePHP Mobile v4](https://nativephp.com/docs/mobile/4/getting-started/installation) e [Introdução NativePHP Mobile v4](https://nativephp.com/docs/mobile/4/getting-started/introduction).
- O ambiente reporta Android Studio 2026.2.2, Java 17, `adb`, Emulator e o AVD `Medium_Phone_API_37.0`. O emulador ativo foi detectado como `emulator-5554` por `adb devices -l`.
- O SDK contém plataformas Android 35, 36 e 37; Build Tools 36.0.0. `sdkmanager.bat` não foi encontrado em `cmdline-tools\latest`, mas isso não bloqueou o build. O Gradle instalou NDK 27, Build Tools 35 e CMake 3.22.1 durante a compilação.
- A configuração efetiva do banco está em SQLite, o que é compatível com uma estratégia local/offline. Não foram encontrados usos diretos de cliente HTTP nas áreas `app`, `resources/js` e `routes`; isso não comprova offline do app empacotado nem ausência de requisições em dependências.
- A primeira tentativa falhou porque `adb` não estava no `PATH`; adicionar `platform-tools` e `emulator` ao `PATH` do processo resolveu a detecção.
- A primeira compilação Gradle chegou ao processamento de recursos, mas o identificador efetivo `com.personal.life-counter` foi rejeitado por conter hífen. A compilação de teste usou `com.personal.lifecounter`; nesta retomada, `php artisan config:show nativephp.app_id` também resolve para esse identificador. Confirmar que o namespace é controlado pelo proprietário do app antes de distribuição.
- A compilação de teste com o identificador sem hífen concluiu com `BUILD SUCCESSFUL`, gerou `nativephp/android/app/build/outputs/apk/debug/app-debug.apk` e iniciou o app. Após remover a classe duplicada, `composer dump-autoload --optimize --no-interaction` e uma nova execução de `php artisan native:run android emulator-5554 --no-interaction --no-tty` também passaram sem o aviso PSR-4; o APK foi reinstalado e abriu a tela de configuração.
- A verificação final `php artisan native:status android --device=emulator-5554` confirmou `Installed: yes` e `Running: yes`. O pacote instalado reportou `versionName=DEBUG`, `minSdk=33` e `targetSdk=36`.
- A primeira captura, feita logo após iniciar o processo, ainda mostrava `Loading…`; após aguardar a inicialização, uma nova captura confirmou a renderização da arena em `/duel/1`, com dois jogadores e controles de PV.
- O primeiro build reportou `sdk.dir` vazio em `local.properties` e incompatibilidade de leitura do SDK XML v4. No build final, `NATIVEPHP_ANDROID_SDK_LOCATION` foi definido somente no processo como `%LOCALAPPDATA%\Android\Sdk`; NativePHP gravou o caminho no `local.properties`, o aviso de `sdk.dir` desapareceu e `php artisan native:devices` detectou `emulator-5554` ligado. Para um checkout/ambiente novo, definir essa variável antes de compilar:

  ```powershell
  $env:NATIVEPHP_ANDROID_SDK_LOCATION = "$env:LOCALAPPDATA\Android\Sdk"
  ```

- O aviso de SDK XML v4 foi observado na primeira compilação; não apareceu no output da compilação final.
- A classe duplicada `App\Appearance` em `app/Models/Appearance.php` não tinha referências e estava fora do caminho PSR-4 esperado. O arquivo obsoleto foi removido para eliminar o aviso do Composer durante a geração do bundle.

## Prova manual offline e persistência — 2026-11-03

- No emulador `emulator-5554`, os dados móveis e o Wi-Fi foram desligados; `dumpsys connectivity` confirmou `Active default network: none`.
- Um toque no controle `-1000` alterou os PVs de 7.000 para 6.000 sem rede. A captura confirmou a atualização visual e o histórico da operação.
- O SQLite privado do app registrou o jogador 1 com 6.000 PV e dois eventos. Após forçar o encerramento e relançar o app, uma nova leitura do SQLite confirmou os mesmos 6.000 PV e eventos.
- O app voltou à tela de configuração (`/`), não à partida anterior. A gravação local sobreviveu ao relançamento, mas a retomada visual da última partida não foi comprovada e pertence ao fluxo de recuperação da tarefa 020.
- Em um relançamento anterior, a captura inicial mostrava `Loading…` e a tela de configuração apareceu após cerca de 30 segundos. Após o build final, as capturas ADB permaneceram pretas por mais de 30 segundos, embora a WebView registrasse `/` como carregada. Um toque na posição conhecida do botão de início navegou para `/duel/3` e a arena foi renderizada; logcat não apresentou fatal nativo nem erro JavaScript correspondente. A causa da captura/preenchimento inicial não foi isolada, então a inicialização visual consistente continua pendente. A partida de teste #3 ficou aberta no emulador.
- O processo permaneceu ativo e a rede foi restaurada, com rede padrão ativa.
- Essa prova foi manual via ADB; o repositório não contém teste automatizado de interação Android/offline.

## APIs nativas verificadas na versão instalada

- `Device::vibrate()` está disponível para feedback tátil; ainda não foi exercitado no dispositivo.
- `Camera::pickImages()` está disponível para abrir o seletor nativo de mídia; a seleção e o armazenamento de uma imagem ainda não foram testados no app.
- A orientação pode ser restringida em `config/nativephp.php` (`orientation.android`); `System::orientation()` e `OrientationChanged` permitem consultar e observar a orientação. A configuração atual restringe o app a retrato.
- O pacote documenta `safe-area`/viewport edge-to-edge para WebView. `NavBarOptions::hidden()` é uma opção do fluxo EDGE nativo e não foi validada nesta aplicação, cujo log de runtime confirma uma WebView em `127.0.0.1`.
- Não foi localizada API integrada de keep-awake nem de reprodução de sons no pacote core instalado. Esses requisitos precisam de implementação/plugin e validação próprios antes de serem considerados suportados.
- Referências: [Device](https://nativephp.com/docs/mobile/4/the-basics/device), [configuração](https://nativephp.com/docs/mobile/4/getting-started/configuration) e [instalação](https://nativephp.com/docs/mobile/4/getting-started/installation).

## Suíte automatizada

- `php artisan test --compact`: **80 aprovados, 1 falha, 190 assertions**.
- A única falha é `it_stores_an_uploaded_wallpaper_on_the_public_disk`, em [AppearanceTest](../../tests/Feature/AppearanceTest.php): a resposta não contém `wallpaper__scrim`. O upload e a gravação no disco passam; a expectativa do scrim continua pendente da implementação visual da tarefa [027](./027-contraste-e-scrim-do-papel-de-parede.md). Essa falha não invalida a prova offline, mas a suíte ainda não está verde.
- O aviso PSR-4 de `App\Appearance` foi eliminado removendo a classe duplicada sem referências. O aviso de `sdk.dir` também foi eliminado no build final; o aviso de SDK XML só foi observado na primeira compilação.

## Bloqueios e próximos passos

- [ ] Confirmar que `com.personal.lifecounter` está sob namespace controlado pelo proprietário do app antes de distribuição.
- [x] Configurar o SDK para o ambiente atual e verificar `native:devices`; documentar `NATIVEPHP_ANDROID_SDK_LOCATION` para novos ambientes.
- [x] Gerar e instalar APK debug no AVD; registrar os avisos e usar identificador Android sem hífen.
- [x] Confirmar que o app abre e renderiza a arena.
- [x] Executar ação de PV sem rede e confirmar a gravação em SQLite após força-parada e relançamento.
- [ ] Validar a retomada visual da partida anterior; o relançamento atual abre a tela de configuração.
- [ ] Investigar a tela/captura preta no primeiro frame após o build final; a arena só foi renderizada após uma interação.
- [ ] Validar persistência de configurações e arquivos locais, incluindo seleção nativa de imagem.
- [ ] Exercitar orientação, tela ligada, tela cheia e feedback tátil/sonoro no dispositivo; keep-awake e som não têm API core identificada.

## Critérios de aceite

- [x] Compatibilidade de dependências PHP documentada com as versões instaladas e fontes oficiais consultadas.
- [x] Um build Android debug de teste é gerado e instalado no emulador.
- [x] Arena e alteração de PV funcionam sem conexão após a instalação.
- [x] O estado dos PVs permanece no SQLite após fechar e reabrir o app.
- [ ] A primeira tela após cold start renderiza consistentemente sem ficar preta ou em `Loading…`.
- [ ] Retomada visual da partida, configurações e arquivos locais após reabertura.
- [x] Limitações e bloqueios atuais do ambiente são explícitos; APK não é declarado pronto sem verificação.

## Registro do trabalho, metodologia e pendências

### O que foi feito e como

- As versões instaladas e os requisitos foram conferidos nos manifests Composer, configuração do app, documentação oficial NativePHP Mobile v4 e comandos `native:*`.
- O projeto NativePHP existente foi reutilizado para gerar, instalar e abrir um APK debug no AVD Android Studio. O SDK/ADB foi localizado e a variável `NATIVEPHP_ANDROID_SDK_LOCATION` foi definida no processo de build.
- A operação offline foi comprovada no emulador: rede desligada, PV alterados, gravação verificada no SQLite privado e persistência confirmada após encerrar e relançar o app.
- Foram consultadas as APIs NativePHP disponíveis para vibração, seleção de imagens, orientação e barra de navegação. Ausência de API core de keep-awake e som foi registrada sem simular suporte.

### Metodologia e regras aplicadas

- Primeiro verificar compatibilidade e documentação da versão instalada; depois executar um build real e validar no emulador, sem substituir Laravel/Livewire por Flutter.
- Separar evidência automatizada da validação manual no dispositivo e manter pendências explícitas; build bem-sucedido não equivale a aceite de UX nem a APK pronto para publicação.
- Respeitar o escopo offline/local e evitar alterações em código Android nativo quando a configuração e os comandos oficiais do pacote eram suficientes.

### Skills e fontes de apoio

- `vscode-laravel-artisan`: comandos NativePHP executados pelo Artisan.
- `laravel-best-practices`: compatibilidade e convenções do app Laravel.
- Documentação oficial NativePHP Mobile v4 foi consultada. Não havia uma skill NativePHP dedicada confirmada no conjunto de skills desta execução.

### O que falta

- Investigar a tela/captura preta e comprovar renderização consistente no primeiro frame.
- Implementar e validar retomada visual da partida anterior; persistência dos PV já foi comprovada, mas a retomada atual abre outra tela.
- Validar persistência de configurações e seleção/armazenamento de imagem local no app empacotado.
- Exercitar orientação e vibração em dispositivo/emulador. Identificar e validar plugin/API de keep-awake e áudio antes de anunciar esses recursos.
- Adicionar cobertura automatizada de integração do fluxo Android/offline quando a infraestrutura do projeto permitir.

### Histórico completo das tentativas de validação Android

Este histórico é cumulativo. Não apagar uma observação anterior; usar a data/ordem e o estado reproduzido para decidir se ela continua sendo evidência aplicável.

1. **Primeiro setup e build**
   - Foram detectados NativePHP Mobile 4.7.0 e Mobile UI 0.9.0, PHP 8.5.7, Laravel 13.39.0, Livewire 4.4.8, Java 17, Android Studio 2026.2.2, ADB e AVD `Medium_Phone_API_37.0`.
   - O executável ADB fica em `%LOCALAPPDATA%\Android\Sdk\platform-tools\adb.exe`. Comandos como `adb devices -l` exigem caminho completo ou `platform-tools` adicionado ao `PATH` do mesmo processo.
   - O primeiro build encontrou `sdk.dir` vazio e rejeitou o application ID com hífen (`com.personal.life-counter`). O build de teste com `com.personal.lifecounter` passou; não gravar outro namespace permanentemente sem confirmar que pertence ao proprietário do app.
   - Definir `NATIVEPHP_ANDROID_SDK_LOCATION` como `%LOCALAPPDATA%\Android\Sdk` antes do build. `ANDROID_HOME` e `ANDROID_SDK_ROOT` mais `platform-tools` no `PATH` ajudaram a resolver o dispositivo. Gradle instalou NDK 27, Build Tools 35 e CMake 3.22.1; o APK debug foi gerado em `nativephp/android/app/build/outputs/apk/debug/app-debug.apk`.
   - Em diferentes execuções, `native:status` confirmou instalado/em execução e uma captura mostrou a arena. A primeira tela pode permanecer em `Loading…` por cerca de 30 segundos. Em outra execução, a tela/captura ficou preta apesar de a WebView registrar navegação; a causa não foi isolada.
   - Avisos encontrados durante builds: leitura do SDK XML v4 por ferramenta que reportava suporte até v3; configuração do SDK ausente na primeira tentativa. O aviso XML não bloqueou a compilação. Há registros conflitantes entre tentativas quanto ao warning PSR-4 da classe `App\Appearance` em `app/Models/Appearance.php`; verificar o estado atual do arquivo/autoload antes de repetir o diagnóstico. Não inferir que outro erro de build foi corrigido sem repetir o comando.

2. **Prova offline anterior bem-sucedida**
   - Uma sessão no emulador confirmou `Active default network: none`, alterou PV de 7.000 para 6.000 sem rede e verificou no SQLite privado do app o valor e os eventos. Depois de forçar o encerramento e relançar, o mesmo estado persistia.
   - Essa evidência demonstra uma operação/persistência offline naquela execução. O relançamento abriu a configuração, não retomou visualmente o duelo. Não há teste automatizado de dispositivo.

3. **Tentativa offline posterior inconclusiva**
   - Em uma nova execução, a tela ficou preta ao tentar operar por coordenadas. Não foi possível confirmar se o toque atingiu o controle HTML correto; portanto essa tentativa não invalida a gravação SQLite da prova anterior, mas impede declarar a repetibilidade/UX offline como validada.
   - Wi‑Fi e dados foram restaurados após o teste; a tela preta permaneceu sob investigação. `native:tail --fatals` não encontrou crashes e não havia `laravel.log`, embora tenha sido observado `browser.log`.
   - O binding da árvore de acessibilidade no emulador expunha somente um WebView, sem elementos HTML, então ADB por coordenadas não era evidência confiável do alvo atingido.
   - O ambiente de uma tarefa posterior mostrou AVD `emulator-5554` e o mesmo AVD `Medium_Phone_API_37.0`. Na validação do lançador da tarefa 012, o build debug compilou, a tela inicial/configuração abriram, mas a interação causou ANR do app/Sistema UI. Essa ocorrência é evidência do estado do emulador/app nessa rodada, não uma causa raiz da falha de captura antiga.

### Comandos e cuidados reproduzíveis

```powershell
$env:ANDROID_HOME = "$env:LOCALAPPDATA\Android\Sdk"
$env:ANDROID_SDK_ROOT = $env:ANDROID_HOME
$env:NATIVEPHP_ANDROID_SDK_LOCATION = $env:ANDROID_HOME
$env:PATH = "$env:ANDROID_HOME\platform-tools;$env:PATH"
& "$env:ANDROID_HOME\platform-tools\adb.exe" devices -l
php artisan native:run android emulator-5554 --no-tty
php artisan native:status android --device=emulator-5554 --json
php artisan native:screenshot android --device=emulator-5554 --out="$env:TEMP\life-counter.png"
```

- O application ID inválido foi corrigido apenas para o processo/build de teste em uma execução anterior; não substituir `.env` nem divulgar seu conteúdo. Para inspecionar configuração, usar `php artisan config:show nativephp.app_id`, sem ler valores secretos do `.env`.
- Sempre confirmar a conectividade antes e depois do teste offline, usar um duelo temporário identificado, guardar valor inicial e final e verificar o banco local. Restaurar Wi‑Fi/dados mesmo quando o teste falhar.
- `native:status` comprova instalação/processo, não renderização correta. Captura visual, navegação e operação de domínio precisam de provas separadas.
