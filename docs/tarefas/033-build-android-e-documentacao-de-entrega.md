# 033 — Build Android e documentação de entrega

- **Status:** Parcial — APK debug é gerado; instalação/abertura já evidenciadas em build anterior; empacotamento de entrega pendente
- **Área:** Entrega
- **Prioridade:** Alta

## Estado atual do código — 2026-11-06

- `nativephp/android/local.properties` contém `sdk.dir=C:/Users/MichelS/AppData/Local/Android/Sdk` e o `.env` define `NATIVEPHP_ANDROID_SDK_LOCATION` com o mesmo caminho; o build não falha mais por "SDK location not found".
- `nativephp/android/app/build/outputs/apk/debug/app-debug.apk` foi gerado em 2026-11-06 13:21 (≈179,76 MB). A instalação e a abertura em emulador foram evidenciadas na tarefa [003](./003-validar-nativephp-offline.md) com um build anterior; o APK de 2026-11-06 não foi reinstalado nesta rodada.
- O pacote continua `versionName=DEBUG` porque `NATIVEPHP_APP_VERSION` não está definido; nesse modo o bundle é reextraído a cada cold start.
- `public/icon.png` e `public/splash.png` não existem; o build ignora o ícone e o splash usa o texto "Loading…".
- Não há configuração de assinatura de release nem documentação de entrega.

## Objetivo

Preparar uma versão Android de teste verificável e instruções para executar, instalar e compreender a aplicação.

## Escopo

- Gerar build de teste com NativePHP e instalar em emulador ou dispositivo compatível.
- Documentar requisitos, comandos, permissões, instalação e solução de problemas.
- Documentar arquitetura, funcionalidades concluídas, limitações e próximos passos.
- Confirmar comportamento offline, assets e persistência no pacote produzido.
- Não afirmar que APK foi entregue se o arquivo não tiver sido gerado e verificado.

## Critérios de aceite

- [x] Build Android é instalado e abre a aplicação em ambiente de teste. *(Evidência na tarefa 003, com build anterior; o APK de 2026-11-06 ainda não foi reinstalado.)*
- [ ] Fluxos críticos são testados no pacote, não apenas no servidor de desenvolvimento.
- [ ] Documentação corresponde ao estado real do projeto e não marca pendências como concluídas.
- [ ] Limitações de plataforma/ambiente são claramente listadas.


## Validação em dispositivo - 2026-11-06

- APK debug (pp-debug.apk, ~179,76 MB) gerado em 2026-11-06 13:21, instalado e aberto no moto g56 5G.
- ersionName=DEBUG, ersionCode=1; bundle reextraído a cada cold start.
- public/icon.png e public/splash.png ausentes; splash exibe Loading….
- Sem assinatura de release nem documentação de entrega.

## Atualização - 2026-11-06

- NATIVEPHP_APP_VERSION=1.0.0 definido no .env; bundle não é mais reextraído a cada cold start.
- Cold start caiu de ~13,8 s para ~1,4 s (persistent runtime boota em ~550 ms).
- public/icon.png e public/splash.png continuam ausentes; splash exibe Loading….
- Assinatura de release e documentação de entrega permanecem pendentes.