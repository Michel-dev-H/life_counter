# Duel Companion — Theme & Wallpaper Guidelines

## Overview
This app supports three visual themes that users can switch between at runtime. Components render based on a `data-theme` attribute on the `<body>` element. The browser's `theme-color` metadata follows the selected theme. A theme-colored scrim is intended to improve readability over custom wallpapers; its contrast still requires the validation tracked in [task 027](./tarefas/027-contraste-e-scrim-do-papel-de-parede.md).

## Theme Tokens
Each theme provides a complete set of color tokens used by the Tailwind CSS layer. The tokens are:

| Token | Holographic (#00f2fe palette) | Shadow Realm (#b57bff palette) | Minimal Arena (#2f6f4e palette) |
|-------|------------------------------|--------------------------------|---------------------------------|
| surface | `#0f131d` | `#0b0713` | `#f7f7f4` |
| on-surface | `#dfe2f1` | `#ece4f7` | `#17181c` |
| primary | `#e0fdff` | `#f4ecff` | `#17181c` |
| on-primary | `#00373a` | `#2a0a45` | `#ffffff` |
| primary-container | `#00f2fe` | `#b57bff` | `#2f6f4e` |
| on-primary-container | `#006a70` | `#33005c` | `#ffffff` |
| secondary | `#edc157` | `#e8c06a` | `#b45309` |
| on-secondary | `#3f2e00` | `#3a2a00` | `#ffffff` |
| secondary-container | `#906d00` | `#6d5410` | `#fde8c8` |
| on-secondary-container | `#fff7ee` | `#fff3d6` | `#5c2c00` |
| tertiary | `#fff5f5` | `#ffd9e2` | `#1d4ed8` |
| on-tertiary | `#670020` | `#5c0020` | `#ffffff` |
| error | `#ffb4ab` | `#ffb4ab` | `#b91c1c` |
| on-error | `#690005` | `#690005` | `#ffffff` |
| damage | `#ff3366` | `#ff3366` | `#b91c1c` |
| heal | `#00f5a0` | `#3fe0a2` | `#2f6f4e` |

## Runtime Theme Switching
1. The `<body>` element receives `data-theme="holographic|shadow-realm|minimal-arena"` via a Livewire-rendered attribute.
2. Tailwind's `@layer utilities` generates per-theme utilities using `[data-theme="holographic"] .class { ... }`.
3. The Livewire component `AppearanceSettings` saves the selected theme through `Appearance::selectTheme()` to the `settings` table (`key = 'appearance.theme'`).
4. On page load, `Appearance::theme()` reads the stored value and applies it.
5. On selection, a Livewire browser event updates the body's `data-theme` and the `theme-color` meta tag without reloading the page.

## Wallpaper Upload & Contrast Scrim
1. Users may upload one image from the phone's gallery via `AppearanceSettings`.
2. The image is stored on `storage/app/public/wallpapers/<uuid>.jpg` and its path is saved in the `settings` table (`key = 'appearance.wallpaper'`).
3. A scrim overlay is applied via CSS: `background: theme-color rgba(0,0,0,0.4)`. For example, Holographic uses `rgba(15,19,29,0.4)` (surface), Shadow Realm uses `rgba(11,7,19,0.4)`, Minimal uses `rgba(247,247,244,0.1)` (very light).
4. If the wallpaper is removed, the scrim falls back to the solid theme surface color.
5. Maximum file size: 2 MB. Accepted MIME types: `image/jpeg`, `image/png`, `image/webp`.

## Player Accent Tokens
Player colors are stored as semantic keys (`cyan`, `gold`, `rose`, `mint`, `violet`, `orange`, `blue`, `pink`), never as user-selected hex values. Each theme maps those keys to its own readable palette in `resources/css/app.css`.

The setup and arena use the selected key for the player's accent, icon, and life-point indicator. Players with no selected icon use the standard swords icon. Existing sessions with stored hex colors resolve to their seat's theme token so old duels remain readable.

## Adding a New Theme
1. Define a new `Theme` enum value.
2. Add its palette to the CSS `@layer utilities` block in `resources/css/app.css` using attribute selectors `[data-theme="<value>"]`.
3. Provide a screenshot for the theme picker UI (store under `resources/images/themes/`).
4. If the theme requires a different wallpaper scrim opacity, add a CSS variable: `--theme-scrim-opacity`.
5. Register the theme in the `AppearanceSettings` component if new actions are needed.
6. Add tests in `tests/Feature/AppearanceTest.php`.

## Contrast Checklist
When a wallpaper is active, verify:
- Text on surface has ≥ 4.5:1 contrast against the scrim + wallpaper composite.
- Buttons and interactive elements have ≥ 3:1 contrast (large UI).
- Progress bars and indicators remain readable.
- If the composite fails contrast, fall back to the solid theme surface without wallpaper.