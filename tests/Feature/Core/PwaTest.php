<?php

test('every file the manifest points to exists', function () {
    $manifest = json_decode((string) file_get_contents(public_path('manifest.json')), true);

    expect($manifest['name'])->toBe('Keep Focus')
        ->and($manifest['start_url'])->toBe('/hoje');

    foreach ($manifest['icons'] as $icon) {
        expect(public_path(ltrim($icon['src'], '/')))->toBeFile();
    }
});

test('every file the service worker precaches exists', function () {
    $worker = (string) file_get_contents(public_path('sw.js'));

    preg_match('/const PRECACHE = \[(.*?)\];/s', $worker, $list);
    preg_match_all('/"(\/[^"]+)"/', $list[1], $paths);
    preg_match('/const OFFLINE_URL = "([^"]+)"/', $worker, $offline);

    expect($paths[1])->not->toBeEmpty();
    foreach ([$offline[1], ...$paths[1]] as $path) {
        expect(public_path(ltrim($path, '/')))->toBeFile();
    }
});

test('the layout links the manifest and the icons', function () {
    $this->get('/')
        ->assertSee('<link rel="manifest" href="/manifest.json">', false)
        ->assertSee('/icons/apple-touch-icon.png', false);

    expect(public_path('icons/apple-touch-icon.png'))->toBeFile();
});
