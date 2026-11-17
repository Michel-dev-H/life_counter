<?php

use Illuminate\Support\Facades\File;

/**
 * `<native:list>` renders a LazyColumn, which Compose refuses to measure
 * inside a vertical `scroll-view` (unbounded height) — the device crashes
 * with "Vertically scrollable component was measured with an infinity
 * maximum height constraints". Lists either scroll on their own or the rows
 * are built by hand inside the scroll view.
 */
it('does not nest a lazy list inside a scroll view', function () {
    $offenders = collect(File::files(resource_path('views/native')))
        ->filter(function (SplFileInfo $file): bool {
            preg_match_all(
                '/<native:scroll-view\b.*?<\/native:scroll-view>/s',
                File::get($file->getPathname()),
                $blocks
            );

            foreach ($blocks[0] as $block) {
                if (preg_match('/<native:list(\s|>)/', $block) === 1) {
                    return true;
                }
            }

            return false;
        })
        ->map(fn (SplFileInfo $file): string => $file->getFilename())
        ->values()
        ->all();

    expect($offenders)->toBe([]);
});
