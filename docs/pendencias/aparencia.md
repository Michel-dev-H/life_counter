# Aparência

**Rota:** `/appearance/settings` · **Componente:** `App\NativeComponents\AppearanceSettings` · **View:** `resources/views/native/appearance-settings.blade.php`
**Layout:** `App\Layouts\TabsLayout`
**Design Stitch:** `docs/design/personalize.html` ("Personalização & Temas - Duel Nexus")
**Tarefas antigas absorvidas:** 022, 023, 024, 025, 026, 027, 029, 031
**Cobertura atual:** `tests/Feature/AppearanceDomainTest.php`, `tests/Feature/AppearanceScreenTest.php`, `tests/Feature/ThemeTest.php`
**Status geral:** Implementado — não validado no aparelho

## 1. Contexto e requisito

Personalização do app (022–027): escolher tema, escolher/remover papel de parede da galeria do dispositivo e ajustar o escurecimento sobre a imagem. O design `personalize.html` mostra três blocos: identidade dos temas, galeria de superfícies com 3 papéis + "Galeria do Celular", e "Camada de Escurecimento (Dimming)".

## 2. Estado atual

- Tema: `Theme` com 3 casos (Holographic, Shadow Realm, Minimal Arena), rótulo e descrição (`app/Enums/Theme.php:11-27`). Seleção por índice grava em `Setting` e reaplica os tokens nativos (`AppearanceSettings.php:42-55`; `Appearance::selectTheme`/`applyNativeTheme`).
- Paleta: 24 tokens por tema, iguais em `light` e `dark` — o app nunca segue a aparência do sistema (`Theme::nativeTokens`, `ThemeTest.php` cobre).
- Resíduo de WebView removido em 2026-11-19: `Theme::browserColor()` e seu docblock não existem mais (AP-9).
- Papel de parede: `Camera::pickImages('images', false)` (`AppearanceSettings.php:60-65`), resultado em `#[On(MediaSelected::class)]` (`:72-97`), cópia local via `Appearance::storeWallpaperFromPath()` com validação de tipo/tamanho (`Appearance.php:129-146`), URL pública em `public/wallpapers` (`Appearance.php:71-90`).
- Escurecimento: slider 0–0.8 passo 0.05, valor gravado a cada `@change` (`AppearanceSettings.php:99-104`; view `:54-65`).
- Remoção com confirmação em modal (`AppearanceSettings.php:106-122`; view `:70-82`).
- View inteira em `scroll-view`, sem `native:list` (`appearance-settings.blade.php:1-2`); o modal fica **fora** do `scroll-view` (`:70`).
- Falha de leitura/validação mostra `$wallpaperError` (`:46-48`); cancelar o seletor não mostra nada.

## 3. Pendências

| ID | Pendência | Origem | Critério de aceite | Status |
|---|---|---|---|---|
| AP-1 | Galeria interna de papéis de parede | 025 | Lista de fundos incluídos (o design mostra 3 + "Galeria do Celular"), com origem/licença registradas e opção de restaurar o padrão | Pendente |
| AP-2 | Enquadramento e desfoque da imagem escolhida | 026 | Pré-visualização com ajuste de posição/zoom e desfoque opcional antes de salvar; cancelar não altera a aparência gravada | Pendente |
| AP-3 | Identidade visual completa dos temas | 023 | Cada tema distinguível por mais que a cor de destaque (superfícies, tipografia, forma); hoje a diferença é só a paleta de tokens | Pendente |
| AP-4 | Contraste garantido sobre qualquer papel de parede | 027, 029 | Texto e controles legíveis em todos os temas, com o scrim em 0% e com imagem clara/escura | Pendente |
| AP-5 | Cancelar/negar permissão da galeria é silencioso | 026 | `onMediaSelected` com `success=false` ou lista vazia informa o motivo; hoje retorna sem mensagem (`:75-77`) | Pendente |
| AP-6 | Troca de tema não repinta o chrome nativo | 022, 023 | Nav bar e tab bar refletem o tema novo sem reiniciar o app (o `TabBar` lê `theme('primary')` na construção — ver `chrome-e-modais.md` CH-9) | Pendente |
| AP-7 | Slider sem rótulo acessível | 029 | `native:slider` com `a11y-label` e valor anunciado; hoje só o texto "45%" abaixo é lido | Pendente |
| AP-8 | Escurecimento não tem valor "padrão" explícito | 024, 027 | Ação para restaurar o scrim padrão (0,45) junto com o tema/papel de parede | Pendente |
| AP-9 | `browserColor()` obsoleto | observado 2026-11-18 | Removido em 2026-11-19: o método e o docblock de WebView saíram de `Theme`; `grep browserColor` = 0 ocorrências | Feito |
| AP-10 | Validação não limita dimensão em pixels | 026 | `assertValidWallpaper` só checa mime e 2 MB (`Appearance.php:169-179`); definir limite de dimensão para não decodificar imagem gigante na prévia | Pendente |
| AP-11 | Não há "restaurar padrão" global | 025, 031 | Uma ação volta tema, papel de parede e scrim ao padrão, com confirmação | Pendente |

## 4. Validação no aparelho

1. Abrir pela aba "Aparência" e pelo botão do Início → mesma tela, título "Aparência".
2. Trocar entre os 3 temas → botão selecionado muda e todas as telas repintam (AP-6).
3. Escolher imagem da galeria → aparece a prévia e o fundo atrás das telas.
4. Negar permissão de fotos → mensagem visível (AP-5), nada é gravado.
5. Escolher arquivo que não é imagem ou grande demais → mensagem de validação (AP-10).
6. Slider de 0% a 80% → escurecimento acompanha em tempo real e persiste.
7. Remover papel de parede → confirmação; cancelar mantém, confirmar apaga o arquivo local.
8. Fechar e reabrir o app → tema, papel de parede e scrim preservados.
9. Android 13+ (fotos por seletor do sistema) e iOS 18.2+ (`mobile-camera`): permissão pedida apenas ao escolher imagem.
10. Fonte do sistema em 200% → blocos continuam legíveis e o slider alcançável.

## 5. Regras aplicáveis

- `.ai/rules/scope.md` — sempre.
- `.ai/rules/app.md` — persistência e validação de imagem em `Appearance`, não no componente.
- `.ai/rules/tests.md` — tema/scrim/papel de parede são comportamento: teste antes.
- `.ai/rules/nativephp-docs.md` — tokens de tema, `native:modal` e boot do tema (D27).

Decisões: D6, D7, D17, D22, D23, D27. Ver `docs/nativephp-refactor/decisions.md`.

## 6. Skills

- `laravel-best-practices` — `Appearance`, `Setting` e storage local.
- `testing-best-practices` — cenários de tema/scrim/arquivo inválido.
- `nativephp-mobile-ui` (vendor) — `image`, `slider`, `modal`, tokens `theme-*`; conferir API de galeria em `mobile-camera`.
- `tailwindcss-development` — classes utilitárias da view.

## 7. Arquivos a inspecionar antes de editar

- `app/NativeComponents/AppearanceSettings.php`
- `resources/views/native/appearance-settings.blade.php`
- `app/Appearance.php`, `app/Enums/Theme.php`, `app/Models/Setting.php`
- `app/Layouts/WallpaperLayout.php`, `resources/views/native/wallpaper-layer.blade.php`
- `tests/Feature/AppearanceDomainTest.php`, `AppearanceScreenTest.php`, `ThemeTest.php`
- `docs/design/personalize.html`
- `docs/tarefas/022-…` a `027-…`, `029-…`

## 8. Registro de execução

| Data | Agente | O que mudou | Comandos | Resultado |
|---|---|---|---|---|
| 2026-11-17 | auditoria | Arquivo criado a partir do código atual e das tarefas 022–027/029 | leitura de código | 11 pendências abertas |
| 2026-11-17 | correção | Boot do tema deixou de quebrar antes do `migrate` (D27); teste de regressão em `AppearanceDomainTest` | `php artisan test --compact tests/Feature/AppearanceDomainTest.php` | verde; validação no device pendente |
| 2026-11-19 | limpeza | AP-9 fechado: `Theme::browserColor()` removido (sem chamadores nem testes) | `grep browserColor app resources routes config tests` | 0 ocorrências |
