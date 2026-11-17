# AppearanceTest Status and Next Steps
## Yu-Gi-Oh! TCG Duel Companion App

## Current Test Results (9/10 passing)

### ✅ Passing Tests (9/10)
1. **defaults to the holographic theme**
   - Verifica `app(Appearance::class)->theme()` = `Theme::Holographic`

2. **applies the default theme and no wallpaper to the layout**
   - `route('duel.setup')` retorna `data-theme="holographic"`
   - Não mostra `wallpaper__scrim` inicialmente

3. **lets the user pick a theme and persists it**
   - `Livewire::test(AppearanceSettings::class)->call('selectTheme', Theme::ShadowRealm->value)`
   - `app(Appearance::class)->theme()` = `Theme::ShadowRealm`
   - `route('duel.setup')` mostra `data-theme="shadow-realm"`

4. **rejects a theme that does not exist**
   - `Livewire::test(AppearanceSettings::class)->call('selectTheme', 'neon')`
   - Asserts `->assertHasErrors('theme')`
   - Padrão mantido como Holographic

5. **lists the three themes on the appearance page**
   - `route('appearance.edit')` retorna 200
   - Contém 'Holographic Duel', 'Shadow Realm', 'Minimal Arena'

6. **stores an uploaded wallpaper on the public disk**
   - `Storage::fake('public')`
   - `Livewire::test(AppearanceSettings::class)->set('wallpaper', UploadedFile::fake()->image('mesa.jpg'))->call('saveWallpaper')`
   - `app(Appearance::class)->wallpaperPath()` não é null
   - `Storage::disk('public')->assertExists($path)`

7. **rejects a wallpaper that is not an image**
   - `Livewire::test(AppearanceSettings::class)->set('wallpaper', UploadedFile::fake()->create('regras.pdf'))->call('saveWallpaper')`
   - `app(Appearance::class)->wallpaperPath()` = null
   - Asserts error em 'wallpaper'

8. **replaces the previous wallpaper when a new one is uploaded**
   - Upload 'primeira.jpg', saveWallpaper
   - Upload 'segunda.jpg', saveWallpaper
   - `app(Appearance::class)->wallpaperPath()` mudou
   - Anterior removido da disk via `Storage::disk('public')->assertMissing($previousPath)`

9. **removes the wallpaper from the disk and the layout**
   - Upload wallpaper, saveWallpaper
   - Chamar `Livewire::test(AppearanceSettings::class)->call('removeWallpaper')`
   - `app(Appearance::class)->wallpaperPath()` = null
   - `Storage::disk('public')->assertMissing($path)`
   - `route('duel.setup')` não mostra `wallpaper__scrim`

10. **renders seat accents through theme tokens instead of stored colors** ⭐ **APENAS RESTANTE**

### ❌ Pending Test (1/10)
**Teste:** `renders seat accents through theme tokens instead of stored colors`
**Erro:** O teste verifica presença de classes `seat-tone-1` e `seat-tone-2` e ausência de `background-color: #00f2fe`

**Detalhes do teste (AppearanceTest.php lines 125-135):**
```php
it('renders seat accents through theme tokens instead of stored colors', function () {
    $duel = App\Models\Duel::startWith([
        ['name' => 'Ana'],
        ['name' => 'Bruno'],
    ]);

    Livewire::test(App\Livewire\DuelArena::class, ['duel' => $duel])
        ->assertSee('seat-tone-1', false)    // Espera QUEM
        ->assertSee('seat-tone-2', false)    // Espera BRUNO
        ->assertDontSee('background-color: #00f2fe', false); // Não espera Holographic hardcoded
});
```

**O que o teste verifica:**
- Classes `seat-tone-1` e `seat-tone-2` devem estar presentes nos elementos de assento
- O estilo `background-color: #00f2fe` (Holographic hardcoded) NÃO deve aparecer
- As cores devem vir via CSS variable `var(--theme-seat-accent)`

**Possível causa:** As classes `seat-tone-1`/`seat-tone-2` foram adicionadas ao view mas podem não estar sendo aplicadas corretamente no contexto Livewire test, ou há um problema de renderização do Livewire.

---

## Próximos Passos Imediatos

### 1. Investigar teste remaining
```bash
# Rodar teste específico com mais detalhes
php artisan pest --filter="renders seat accents" tests/Feature/AppearanceTest.php

# Verificar o HTML gerado pelo Livewire
# Verificar se classes seat-tone-1/2 estão no DOM
```

### 2. Rodar code formatting
```bash
vendor/bin/pint --dirty --format agent
```

### 3. Build assets
```bash
npm run build
# ou
composer run dev
```

### 4. Commit das mudanças
```bash
git add .
git commit -m "fix: AppearanceTest all 10 passing - theme selection, wallpaper upload, seat accent tokens"
```

---

## Resumo do Que Falta

O único teste que ainda não passa é o de **seat accents rendering**. Os problemas potenciais incluem:

1. **Classes CSS não aplicadas**: `seat-tone-1` e `seat-tone-2` podem não estar sendo adicionadas corretamente aos elementos no DOM do Livewire
2. **Contexto Livewire**: O teste pode estar montando o componente em um contexto onde as classes dinâmicas não são processadas
3. **Ordem de renderização**: As classes podem estar sendo adicionadas mas removidas pelo Livewire após o mount

**Ação recomendada:** Inspecionar o HTML gerado pelo Livewire test para verificar se as classes `seat-tone-1` e `seat-tone-2` estão presentes nos elementos `<span>` dos jogadores.

---

## Arquivos Para Revisão

- `resources/views/livewire/duel-arena.blade.php` - Linhas 122-124 onde classes são aplicadas
- `app/Livewire/DuelArena.php` - Como o componente é montado no teste
- `tests/Feature/AppearanceTest.php` - Linhas 125-135 para entender expectativas exatas